<?php
require_once 'config/database.php';

// Get format
$format = $_GET['format'] ?? 'json';
$itemId = (int)($_GET['id'] ?? 0);

try {
    // Get database connection
    $db = new Database();
    $conn = $db->getConnection();

    if ($itemId) {
        // Export single item
        exportSingleItem($conn, $itemId, $format);
    } else {
        // Export all data
        exportAllData($conn, $format);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

function exportSingleItem($conn, $itemId, $format) {
    // Get item details
    $stmt = $conn->prepare("
        SELECT i.*, c.name as category_name, c.color as category_color
        FROM ita_items i 
        JOIN ita_categories c ON i.category_id = c.id 
        WHERE i.id = ? AND i.is_active = 1
    ");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();

    if (!$item) {
        http_response_code(404);
        echo "ไม่พบรายการ";
        return;
    }

    // Get sub-items
    $stmt = $conn->prepare("
        SELECT * FROM ita_sub_items 
        WHERE item_id = ? AND is_active = 1 
        ORDER BY sort_order, id
    ");
    $stmt->execute([$itemId]);
    $subItems = $stmt->fetchAll();

    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="ita_item_' . $itemId . '_' . date('Y-m-d') . '.json"');
        
        echo json_encode([
            'item' => $item,
            'sub_items' => $subItems,
            'exported_at' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } else if ($format === 'pdf') {
        // For PDF, we'll create an HTML version that can be printed as PDF
        header('Content-Type: text/html; charset=utf-8');
        generateItemPDF($item, $subItems);
    }
}

function exportAllData($conn, $format) {
    // Get all categories with items and sub-items
    $stmt = $conn->prepare("
        SELECT 
            c.*,
            COUNT(i.id) as total_items,
            AVG(CASE WHEN i.status = 'completed' THEN 100 ELSE i.progress END) as avg_progress
        FROM ita_categories c
        LEFT JOIN ita_items i ON c.id = i.category_id AND i.is_active = 1
        WHERE c.is_active = 1
        GROUP BY c.id
        ORDER BY c.sort_order, c.id
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    // Get all items with sub-items
    $stmt = $conn->prepare("
        SELECT 
            i.*,
            c.name as category_name,
            c.color as category_color,
            (SELECT COUNT(*) FROM ita_sub_items si WHERE si.item_id = i.id AND si.is_active = 1) as sub_items_count,
            (SELECT COUNT(*) FROM ita_sub_items si WHERE si.item_id = i.id AND si.is_active = 1 AND si.status = 'completed') as completed_sub_items
        FROM ita_items i
        JOIN ita_categories c ON i.category_id = c.id
        WHERE i.is_active = 1 AND c.is_active = 1
        ORDER BY c.sort_order, i.sort_order, i.id
    ");
    $stmt->execute();
    $items = $stmt->fetchAll();

    // Get all sub-items
    $stmt = $conn->prepare("
        SELECT si.*, i.moit_number, i.title as item_title
        FROM ita_sub_items si
        JOIN ita_items i ON si.item_id = i.id
        WHERE si.is_active = 1 AND i.is_active = 1
        ORDER BY i.sort_order, si.sort_order, si.id
    ");
    $stmt->execute();
    $subItems = $stmt->fetchAll();

    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="ita_full_report_' . date('Y-m-d') . '.json"');
        
        echo json_encode([
            'categories' => $categories,
            'items' => $items,
            'sub_items' => $subItems,
            'statistics' => calculateStatistics($items),
            'exported_at' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } else if ($format === 'pdf') {
        header('Content-Type: text/html; charset=utf-8');
        generateFullPDF($categories, $items, $subItems);
    }
}

function calculateStatistics($items) {
    $totalItems = count($items);
    $completedItems = 0;
    $totalProgress = 0;

    foreach ($items as $item) {
        if ($item['sub_items_count'] > 0) {
            $progress = round(($item['completed_sub_items'] / $item['sub_items_count']) * 100);
        } else {
            $progress = $item['progress'];
        }
        
        if ($progress >= 70) {
            $completedItems++;
        }
        $totalProgress += $progress;
    }

    return [
        'total_items' => $totalItems,
        'completed_items' => $completedItems,
        'overall_score' => $totalItems > 0 ? round($totalProgress / $totalItems) : 0,
        'completion_rate' => $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0
    ];
}

function generateItemPDF($item, $subItems) {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงาน ITA - <?php echo htmlspecialchars($item['moit_number']); ?></title>
        <style>
            body { font-family: 'Sarabun', Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .item-info { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
            .sub-item { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .progress-bar { width: 100%; height: 20px; background: #e9ecef; border-radius: 10px; overflow: hidden; }
            .progress-fill { height: 100%; background: linear-gradient(90deg, #007bff, #6610f2); }
            .status { padding: 5px 10px; border-radius: 15px; font-size: 12px; }
            .status-completed { background: #d4edda; color: #155724; }
            .status-progress { background: #cce5ff; color: #004085; }
            .status-pending { background: #f8f9fa; color: #6c757d; }
            @media print { body { margin: 0; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>รายงาน ITA - <?php echo htmlspecialchars($item['moit_number']); ?></h1>
            <p>สร้างเมื่อ: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="item-info">
            <h2><?php echo htmlspecialchars($item['title']); ?></h2>
            <p><strong>หมวดหมู่:</strong> <?php echo htmlspecialchars($item['category_name']); ?></p>
            <p><strong>สถานะ:</strong> 
                <span class="status status-<?php echo $item['status'] === 'completed' ? 'completed' : ($item['status'] === 'in_progress' ? 'progress' : 'pending'); ?>">
                    <?php echo $item['status'] === 'completed' ? 'เสร็จสิ้น' : ($item['status'] === 'in_progress' ? 'กำลังดำเนินการ' : 'รอดำเนินการ'); ?>
                </span>
            </p>
            <?php if ($item['description']): ?>
            <p><strong>รายละเอียด:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($subItems)): ?>
        <h3>รายการย่อย (<?php echo count($subItems); ?> รายการ)</h3>
        <?php foreach ($subItems as $subItem): ?>
        <div class="sub-item">
            <h4><?php echo htmlspecialchars($subItem['title']); ?></h4>
            <?php if ($subItem['description']): ?>
            <p><?php echo nl2br(htmlspecialchars($subItem['description'])); ?></p>
            <?php endif; ?>
            <p><strong>ความคืบหน้า:</strong></p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $subItem['progress']; ?>%"></div>
            </div>
            <p><?php echo $subItem['progress']; ?>%</p>
            <p><strong>สถานะ:</strong> 
                <span class="status status-<?php echo $subItem['status'] === 'completed' ? 'completed' : ($subItem['status'] === 'in_progress' ? 'progress' : 'pending'); ?>">
                    <?php echo $subItem['status'] === 'completed' ? 'เสร็จสิ้น' : ($subItem['status'] === 'in_progress' ? 'กำลังดำเนินการ' : 'รอดำเนินการ'); ?>
                </span>
            </p>
            <?php if ($subItem['attachment_url']): ?>
            <p><strong>ไฟล์แนบ:</strong> <?php echo htmlspecialchars($subItem['attachment_name'] ?: 'document.pdf'); ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>
    <?php
}

function generateFullPDF($categories, $items, $subItems) {
    $stats = calculateStatistics($items);
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานสรุป ITA Assessment</title>
        <style>
            body { font-family: 'Sarabun', Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
            .stats { display: flex; justify-content: space-around; margin: 20px 0; }
            .stat-item { text-align: center; }
            .stat-number { font-size: 2em; font-weight: bold; color: #007bff; }
            .category { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; }
            .item { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; }
            .progress-bar { width: 100%; height: 15px; background: #e9ecef; border-radius: 7px; overflow: hidden; margin: 5px 0; }
            .progress-fill { height: 100%; background: linear-gradient(90deg, #007bff, #6610f2); }
            @media print { body { margin: 0; } .stats { display: block; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>รายงานสรุป ITA Assessment</h1>
            <h2>มาตรฐานความโปร่งใสและการต่อต้านการทุจริต MOIT</h2>
            <p>ปีงบประมาณ พ.ศ. 2568 | สร้างเมื่อ: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="summary">
            <h3>สรุปผลการประเมิน</h3>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['total_items']; ?></div>
                    <div>รายการทั้งหมด</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['completed_items']; ?></div>
                    <div>ดำเนินการแล้ว</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['overall_score']; ?>%</div>
                    <div>คะแนนรวม</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['completion_rate']; ?>%</div>
                    <div>อัตราความสำเร็จ</div>
                </div>
            </div>
        </div>

        <?php foreach ($categories as $category): ?>
        <div class="category" style="border-color: <?php echo $category['color']; ?>">
            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
            <?php if ($category['description']): ?>
            <p><?php echo htmlspecialchars($category['description']); ?></p>
            <?php endif; ?>
            <p><strong>ความคืบหน้าเฉลี่ย:</strong> <?php echo round($category['avg_progress']); ?>%</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo round($category['avg_progress']); ?>%"></div>
            </div>

            <?php
            // Get items for this category
            $categoryItems = array_filter($items, function($item) use ($category) {
                return $item['category_id'] == $category['id'];
            });
            ?>

            <?php foreach ($categoryItems as $item): ?>
            <div class="item">
                <h4><?php echo htmlspecialchars($item['moit_number'] . ': ' . $item['title']); ?></h4>
                <?php
                $itemProgress = $item['sub_items_count'] > 0 ? 
                    round(($item['completed_sub_items'] / $item['sub_items_count']) * 100) : 
                    $item['progress'];
                ?>
                <p><strong>ความคืบหน้า:</strong> <?php echo $itemProgress; ?>%</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $itemProgress; ?>%"></div>
                </div>
                <p><strong>หัวข้อย่อย:</strong> <?php echo $item['sub_items_count']; ?> รายการ 
                   (เสร็จสิ้น <?php echo $item['completed_sub_items']; ?> รายการ)</p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <div style="margin-top: 40px; text-align: center; font-size: 0.9em; color: #666;">
            <p>รายงานนี้สร้างโดยระบบจัดการ ITA Assessment</p>
            <p>โรงพยาบาลทุ่งหัวช้าง - กระทรวงสาธารณสุข</p>
        </div>

        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>
    <?php
}
?>