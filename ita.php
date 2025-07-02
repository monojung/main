<?php
require_once 'config/database.php';

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Fetch ITA categories and items
try {
    // Get categories with their items and sub-items
    $stmt = $conn->prepare("
        SELECT 
            c.*,
            COUNT(i.id) as total_items,
            AVG(CASE WHEN i.status = 'completed' THEN 100 ELSE i.progress END) as avg_progress
        FROM ita_categories c
        LEFT JOIN ita_items i ON c.id = i.category_id
        WHERE c.is_active = 1
        GROUP BY c.id
        ORDER BY c.sort_order, c.id
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    // Get all items with their sub-items
    $stmt = $conn->prepare("
        SELECT 
            i.*,
            c.name as category_name,
            c.color as category_color,
            (SELECT COUNT(*) FROM ita_sub_items si WHERE si.item_id = i.id) as sub_items_count,
            (SELECT COUNT(*) FROM ita_sub_items si WHERE si.item_id = i.id AND si.status = 'completed') as completed_sub_items
        FROM ita_items i
        JOIN ita_categories c ON i.category_id = c.id
        WHERE i.is_active = 1 AND c.is_active = 1
        ORDER BY c.sort_order, i.sort_order, i.id
    ");
    $stmt->execute();
    $items = $stmt->fetchAll();

    // Calculate overall statistics
    $totalItems = count($items);
    $completedItems = 0;
    $totalProgress = 0;

    foreach ($items as $item) {
        if ($item['sub_items_count'] > 0) {
            // Calculate progress based on sub-items
            $item['calculated_progress'] = $item['sub_items_count'] > 0 ? 
                round(($item['completed_sub_items'] / $item['sub_items_count']) * 100) : 0;
        } else {
            // Use manual progress
            $item['calculated_progress'] = $item['progress'];
        }
        
        if ($item['calculated_progress'] >= 70) {
            $completedItems++;
        }
        $totalProgress += $item['calculated_progress'];
    }

    $overallScore = $totalItems > 0 ? round($totalProgress / $totalItems) : 0;

} catch (Exception $e) {
    $categories = [];
    $items = [];
    $totalItems = 0;
    $completedItems = 0;
    $overallScore = 0;
}

// Calculate days left (assuming fiscal year ends September 30)
$fiscalYearEnd = new DateTime('2025-09-30');
$today = new DateTime();
$daysLeft = max(0, $today->diff($fiscalYearEnd)->days);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบประเมิน ITA - มาตรฐานความโปร่งใส MOIT</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .stats-bar {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .assessment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .moit-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .moit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .moit-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .moit-number {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .moit-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.4;
        }

        .progress-container {
            margin: 15px 0;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 10px;
            transition: width 0.8s ease;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .sub-items-list {
            list-style: none;
            margin-top: 15px;
        }

        .sub-items-list li {
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
            font-size: 0.9rem;
            color: #5a6c7d;
            display: flex;
            align-items: center;
        }

        .sub-items-list li:last-child {
            border-bottom: none;
        }

        .check-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #2ecc71;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 10px;
            flex-shrink: 0;
        }

        .incomplete {
            background: #e74c3c;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #ecf0f1;
            color: #7f8c8d;
        }

        .btn-secondary:hover {
            background: #d5dbdb;
        }

        .summary-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .summary-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .category-item {
            text-align: center;
            padding: 20px;
            background: linear-gradient(45deg, #74b9ff, #0984e3);
            color: white;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .category-item:hover {
            transform: scale(1.05);
        }

        .category-score {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .category-name {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .export-section {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #ecf0f1;
        }

        @media (max-width: 768px) {
            .assessment-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-bar {
                flex-direction: column;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .admin-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .admin-link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <a href="admin/ita.php" class="admin-link">🔧 จัดการระบบ</a>
    
    <div class="container">
        <div class="header fade-in">
            <h1>ระบบประเมิน ITA</h1>
            <p>Information Technology Assessment - มาตรฐานความโปร่งใสและการต่อต้านการทุจริต MOIT</p>
            <p><strong>ปีงบประมาณ พ.ศ. 2568</strong> | กระทรวงสาธารณสุข</p>
            
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $totalItems; ?></span>
                    <span class="stat-label">รายการทั้งหมด</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $completedItems; ?></span>
                    <span class="stat-label">ดำเนินการแล้ว</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $overallScore; ?>%</span>
                    <span class="stat-label">คะแนนรวม</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $daysLeft; ?></span>
                    <span class="stat-label">วันที่เหลือ</span>
                </div>
            </div>
        </div>

        <div class="assessment-grid" id="moitGrid">
            <?php foreach ($items as $index => $item): ?>
            <?php
                // Calculate item progress
                if ($item['sub_items_count'] > 0) {
                    $itemProgress = round(($item['completed_sub_items'] / $item['sub_items_count']) * 100);
                } else {
                    $itemProgress = $item['progress'];
                }
                
                // Get sub-items for this item
                $stmt = $conn->prepare("
                    SELECT * FROM ita_sub_items 
                    WHERE item_id = ? AND is_active = 1 
                    ORDER BY sort_order, id
                ");
                $stmt->execute([$item['id']]);
                $subItems = $stmt->fetchAll();
            ?>
            <div class="moit-card fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                <div class="moit-header">
                    <div class="moit-number"><?php echo $item['moit_number']; ?></div>
                    <div class="moit-title"><?php echo htmlspecialchars($item['title']); ?></div>
                </div>
                
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $itemProgress; ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <span>ความคืบหน้า</span>
                        <span><strong><?php echo $itemProgress; ?>%</strong></span>
                    </div>
                </div>
                
                <?php if (!empty($item['description'])): ?>
                <div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: #6c757d;">
                    <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($subItems)): ?>
                <ul class="sub-items-list">
                    <?php foreach ($subItems as $subItem): ?>
                    <li>
                        <span class="check-icon <?php echo $subItem['status'] === 'completed' ? '' : 'incomplete'; ?>">✓</span>
                        <?php echo htmlspecialchars($subItem['title']); ?>
                        <?php if ($subItem['progress'] > 0 && $subItem['status'] !== 'completed'): ?>
                        <span style="margin-left: auto; font-size: 0.8rem; color: #666;">
                            (<?php echo $subItem['progress']; ?>%)
                        </span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="viewDetails(<?php echo $item['id']; ?>)">ดูรายละเอียด</button>
                    <button class="btn btn-secondary" onclick="viewSubItems(<?php echo $item['id']; ?>)">รายการย่อย</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-section fade-in">
            <h2 class="summary-title">สรุปผลการประเมินตามหมวดหมู่</h2>
            
            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                <div class="category-item" style="background: linear-gradient(45deg, <?php echo $category['color']; ?>, <?php echo $category['color']; ?>cc);">
                    <span class="category-score"><?php echo round($category['avg_progress']); ?>%</span>
                    <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="export-section">
                <button class="btn btn-primary" onclick="generateReport()">📊 สร้างรายงานสรุป</button>
                <button class="btn btn-secondary" onclick="exportData()">📁 ส่งออกข้อมูล</button>
            </div>
        </div>
    </div>

    <script>
        function viewDetails(itemId) {
            // You can implement a modal or redirect to detail page
            alert(`กำลังเปิดรายละเอียด item ID: ${itemId}`);
        }

        function viewSubItems(itemId) {
            // You can implement a modal showing sub-items
            alert(`กำลังแสดงรายการย่อยของ item ID: ${itemId}`);
        }

        function generateReport() {
            alert('กำลังสร้างรายงานสรุป...\n\nรายงานจะรวมถึง:\n- ผลการประเมินแต่ละรายการ\n- แผนภูมิเปรียบเทียบ\n- ข้อเสนอแนะการปรับปรุง');
        }

        function exportData() {
            window.location.href = 'export.php?type=json';
        }

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bars
            setTimeout(() => {
                const progressBars = document.querySelectorAll('.progress-fill');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 500);
                });
            }, 1000);
        });
    </script>
</body>
</html>