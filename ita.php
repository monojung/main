<?php
require_once 'config/database.php';

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Fetch ITA items and sub-items
try {
    // Get all items with their sub-items
    $stmt = $conn->prepare("
        SELECT 
            i.*,
            (SELECT COUNT(*) FROM ita_sub_items si WHERE si.item_id = i.id AND si.is_active = 1) as sub_items_count
        FROM ita_items i
        WHERE i.is_active = 1
        ORDER BY i.sort_order, i.id
    ");
    $stmt->execute();
    $items = $stmt->fetchAll();

    // Calculate overall statistics
    $totalItems = count($items);
    $totalSubItems = 0;

    foreach ($items as $item) {
        $totalSubItems += $item['sub_items_count'];
    }

} catch (Exception $e) {
    $items = [];
    $totalItems = 0;
    $totalSubItems = 0;
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

        .sub-items-list {
            list-style: none;
            margin-top: 15px;
        }

        .sub-items-list li {
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
            font-size: 0.9rem;
            color: #5a6c7d;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sub-items-list li:last-child {
            border-bottom: none;
        }

        .sub-item-content {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .sub-item-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 10px;
            flex-shrink: 0;
        }

        .sub-item-text {
            flex: 1;
        }

        .attachment-icon {
            color: #27ae60;
            font-size: 14px;
            margin-left: 8px;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            margin: 5% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #000;
        }

        .download-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .sub-item-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            transition: all 0.3s ease;
        }

        .sub-item-card:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .file-attachment {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .description-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-size: 0.9rem;
            color: #6c757d;
            line-height: 1.5;
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
                    <span class="stat-label">รายการ ITA</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $totalSubItems; ?></span>
                    <span class="stat-label">หัวข้อย่อย</span>
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
                    <div class="moit-number"><?php echo htmlspecialchars($item['moit_number']); ?></div>
                    <div class="moit-title"><?php echo htmlspecialchars($item['title']); ?></div>
                </div>
                
                <?php if (!empty($item['description'])): ?>
                <div class="description-box">
                    <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($subItems)): ?>
                <ul class="sub-items-list">
                    <?php foreach ($subItems as $subItem): ?>
                    <li>
                        <div class="sub-item-content">
                            <span class="sub-item-icon">📝</span>
                            <span class="sub-item-text"><?php echo htmlspecialchars($subItem['title']); ?></span>
                        </div>
                        <?php if ($subItem['attachment_url']): ?>
                        <a href="uploads/ita/<?php echo htmlspecialchars($subItem['attachment_url']); ?>" 
                           target="_blank" 
                           class="attachment-icon" 
                           title="ดาวน์โหลด: <?php echo htmlspecialchars($subItem['attachment_name'] ?: 'เอกสาร'); ?>">
                            📎
                        </a>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <div class="text-3xl mb-2">📝</div>
                    <p>ยังไม่มีหัวข้อย่อย</p>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="viewDetails(<?php echo $item['id']; ?>)">ดูรายละเอียด</button>
                    <button class="btn btn-secondary" onclick="viewSubItems(<?php echo $item['id']; ?>)">หัวข้อย่อย (<?php echo count($subItems); ?>)</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-section fade-in">
            <h2 class="summary-title">สรุปข้อมูลระบบประเมิน ITA</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="text-center p-6 bg-blue-50 rounded-lg">
                    <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $totalItems; ?></div>
                    <div class="text-gray-600">รายการ ITA ทั้งหมด</div>
                </div>
                <div class="text-center p-6 bg-green-50 rounded-lg">
                    <div class="text-3xl font-bold text-green-600 mb-2"><?php echo $totalSubItems; ?></div>
                    <div class="text-gray-600">หัวข้อย่อยทั้งหมด</div>
                </div>
                <div class="text-center p-6 bg-purple-50 rounded-lg">
                    <div class="text-3xl font-bold text-purple-600 mb-2"><?php echo $daysLeft; ?></div>
                    <div class="text-gray-600">วันที่เหลือในปีงบ</div>
                </div>
            </div>

            <div class="export-section">
                <button class="btn btn-primary" onclick="generateReport()">📊 สร้างรายงานสรุป</button>
                <button class="btn btn-secondary" onclick="exportData()">📁 ส่งออกข้อมูล</button>
            </div>
        </div>
    </div>

    <!-- Modal for Item Details -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function viewDetails(itemId) {
            // Show loading
            document.getElementById('modalContent').innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 3rem; margin-bottom: 20px;">⏳</div><p>กำลังโหลดข้อมูล...</p></div>';
            document.getElementById('detailModal').style.display = 'block';
            
            // Fetch item details
            fetch(`get_item_details.php?id=${itemId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayItemDetails(data.item, data.subItems);
                    } else {
                        document.getElementById('modalContent').innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 3rem; margin-bottom: 20px;">❌</div><p>ไม่สามารถโหลดข้อมูลได้</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback: show basic item details from page data
                    showBasicItemDetails(itemId);
                });
        }

        function showBasicItemDetails(itemId) {
            // Get item data from the current page
            const items = <?php echo json_encode($items); ?>;
            const item = items.find(i => i.id == itemId);
            
            if (!item) {
                document.getElementById('modalContent').innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 3rem; margin-bottom: 20px;">❌</div><p>ไม่พบข้อมูลรายการ</p></div>';
                return;
            }

            // Get sub-items for this item from DOM
            const cardElement = document.querySelector(`.moit-card:nth-child(${items.indexOf(item) + 1})`);
            const subItemElements = cardElement ? cardElement.querySelectorAll('.sub-items-list li') : [];
            
            let subItemsHtml = '';
            if (subItemElements.length > 0) {
                subItemsHtml = `
                    <div style="margin-top: 30px;">
                        <h4 style="font-size: 1.2rem; font-weight: 600; color: #2c3e50; margin-bottom: 20px;">📝 หัวข้อย่อย (${subItemElements.length} รายการ)</h4>
                        <div style="space-y: 15px;">
                `;
                
                subItemElements.forEach((element, index) => {
                    const text = element.querySelector('.sub-item-text')?.textContent || '';
                    const hasAttachment = element.querySelector('.attachment-icon') !== null;
                    
                    subItemsHtml += `
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 15px; margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="flex: 1;">
                                    <h5 style="font-weight: 500; color: #2c3e50; margin-bottom: 5px;">${text}</h5>
                                </div>
                                ${hasAttachment ? `
                                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 8px 12px; display: inline-flex; align-items: center; gap: 8px;">
                                        <span>📄</span>
                                        <span style="font-size: 0.9rem;">มีเอกสารแนบ</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
                
                subItemsHtml += `
                        </div>
                    </div>
                `;
            } else {
                subItemsHtml = `
                    <div style="margin-top: 30px; text-align: center; padding: 40px; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📝</div>
                        <p style="color: #6c757d;">ยังไม่มีหัวข้อย่อย</p>
                    </div>
                `;
            }

            const modalContent = `
                <div style="margin-bottom: 30px;">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <span style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 8px 16px; border-radius: 25px; font-size: 0.9rem; font-weight: 600; margin-right: 15px;">
                            ${item.moit_number}
                        </span>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 600; color: #2c3e50; margin-bottom: 20px; line-height: 1.4;">${item.title}</h3>
                    ${item.description ? `
                        <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 20px; border-radius: 0 10px 10px 0; margin-bottom: 20px;">
                            <h4 style="font-weight: 600; color: #1976d2; margin-bottom: 10px;">📋 รายละเอียด</h4>
                            <p style="color: #424242; line-height: 1.6;">${item.description.replace(/\n/g, '<br>')}</p>
                        </div>
                    ` : ''}
                </div>
                ${subItemsHtml}
                <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef; text-align: center;">
                    <button onclick="exportItemReport(${item.id})" style="background: linear-gradient(45deg, #28a745, #20c997); color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 500; margin-right: 10px; cursor: pointer; transition: all 0.3s ease;">
                        📄 ส่งออกรายงาน
                    </button>
                    <button onclick="closeModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 500; cursor: pointer; transition: all 0.3s ease;">
                        ปิด
                    </button>
                </div>
            `;

            document.getElementById('modalContent').innerHTML = modalContent;
        }

        function displayItemDetails(item, subItems) {
            let subItemsHtml = '';
            if (subItems && subItems.length > 0) {
                subItemsHtml = `
                    <div style="margin-top: 30px;">
                        <h4 style="font-size: 1.2rem; font-weight: 600; color: #2c3e50; margin-bottom: 20px;">📝 หัวข้อย่อย (${subItems.length} รายการ)</h4>
                        <div style="space-y: 15px;">
                            ${subItems.map(subItem => `
                                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 15px; margin-bottom: 10px;">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <h5 style="font-weight: 500; color: #2c3e50; margin-bottom: 5px;">${subItem.title}</h5>
                                        </div>
                                        ${subItem.attachment_url ? `
                                            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 8px 12px; display: inline-flex; align-items: center; gap: 8px;">
                                                <span>📄</span>
                                                <span style="font-size: 0.9rem;">${subItem.attachment_name || 'เอกสารแนบ'}</span>
                                                <a href="uploads/ita/${subItem.attachment_url}" 
                                                   style="background: linear-gradient(45deg, #28a745, #20c997); color: white; border: none; padding: 4px 8px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; margin-left: 8px;" 
                                                   download="${subItem.attachment_name || 'document.pdf'}"
                                                   target="_blank">
                                                    📥 ดาวน์โหลด
                                                </a>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                subItemsHtml = `
                    <div style="margin-top: 30px; text-align: center; padding: 40px; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📝</div>
                        <p style="color: #6c757d;">ยังไม่มีหัวข้อย่อย</p>
                    </div>
                `;
            }

            const modalContent = `
                <div style="margin-bottom: 30px;">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <span style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 8px 16px; border-radius: 25px; font-size: 0.9rem; font-weight: 600; margin-right: 15px;">
                            ${item.moit_number}
                        </span>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 600; color: #2c3e50; margin-bottom: 20px; line-height: 1.4;">${item.title}</h3>
                    ${item.description ? `
                        <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 20px; border-radius: 0 10px 10px 0; margin-bottom: 20px;">
                            <h4 style="font-weight: 600; color: #1976d2; margin-bottom: 10px;">📋 รายละเอียด</h4>
                            <p style="color: #424242; line-height: 1.6;">${item.description.replace(/\n/g, '<br>')}</p>
                        </div>
                    ` : ''}
                </div>
                ${subItemsHtml}
                <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef; text-align: center;">
                    <button onclick="exportItemReport(${item.id})" style="background: linear-gradient(45deg, #28a745, #20c997); color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 500; margin-right: 10px; cursor: pointer; transition: all 0.3s ease;">
                        📄 ส่งออกรายงาน
                    </button>
                    <button onclick="closeModal()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 500; cursor: pointer; transition: all 0.3s ease;">
                        ปิด
                    </button>
                </div>
            `;

            document.getElementById('modalContent').innerHTML = modalContent;
        }

        function viewSubItems(itemId) {
            viewDetails(itemId); // Same as view details
        }

        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        function exportItemReport(itemId) {
            window.open(`export_item.php?id=${itemId}&format=pdf`, '_blank');
        }

        function generateReport() {
            window.open('export_report.php?format=pdf', '_blank');
        }

        function exportData() {
            window.open('export_report.php?format=json', '_blank');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔧 ITA Assessment system loaded successfully!');
            
            // Add hover effects to attachment icons
            const attachmentIcons = document.querySelectorAll('.attachment-icon');
            attachmentIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.2)';
                });
                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Add click handlers for buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('btn-secondary')) {
                        this.style.transform = 'translateY(-2px)';
                    }
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>

    <style>
        .grid {
            display: grid;
        }
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        .gap-6 {
            gap: 1.5rem;
        }
        .mb-8 {
            margin-bottom: 2rem;
        }
        @media (min-width: 768px) {
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .lg\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
</body>
</html>