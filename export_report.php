<?php
require_once 'config/database.php';

$format = $_GET['format'] ?? 'json';
$item_id = $_GET['id'] ?? null;

try {
    // Get database connection
    $db = new Database();
    $conn = $db->getConnection();

    if ($item_id) {
        // Export specific item
        $stmt = $conn->prepare("SELECT * FROM ita_items WHERE id = ? AND is_active = 1");
        $stmt->execute([(int)$item_id]);
        $items = $stmt->fetchAll();
    } else {
        // Export all items
        $stmt = $conn->prepare("SELECT * FROM ita_items WHERE is_active = 1 ORDER BY sort_order, moit_number");
        $stmt->execute();
        $items = $stmt->fetchAll();
    }

    // Get sub-items for each item
    foreach ($items as &$item) {
        $stmt = $conn->prepare("
            SELECT * FROM ita_sub_items 
            WHERE item_id = ? AND is_active = 1 
            ORDER BY sort_order, id
        ");
        $stmt->execute([$item['id']]);
        $item['sub_items'] = $stmt->fetchAll();
    }

    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="ita_export_' . date('Y-m-d_H-i-s') . '.json"');
        
        echo json_encode([
            'export_date' => date('Y-m-d H:i:s'),
            'total_items' => count($items),
            'data' => $items
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    } elseif ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ita_export_' . date('Y-m-d_H-i-s') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($output, [
            'หมายเลข MOIT',
            'หัวข้อหลัก',
            'คำอธิบาย',
            'ลำดับ',
            'หัวข้อย่อย',
            'ลำดับย่อย',
            'ไฟล์แนบ',
            'วันที่สร้าง',
            'วันที่อัปเดต'
        ]);
        
        // Data
        foreach ($items as $item) {
            if (empty($item['sub_items'])) {
                fputcsv($output, [
                    $item['moit_number'],
                    $item['title'],
                    $item['description'],
                    $item['sort_order'],
                    '',
                    '',
                    '',
                    $item['created_at'],
                    $item['updated_at']
                ]);
            } else {
                foreach ($item['sub_items'] as $subItem) {
                    fputcsv($output, [
                        $item['moit_number'],
                        $item['title'],
                        $item['description'],
                        $item['sort_order'],
                        $subItem['title'],
                        $subItem['sort_order'],
                        $subItem['attachment_name'] ?: '',
                        $item['created_at'],
                        $item['updated_at']
                    ]);
                }
            }
        }
        
        fclose($output);
        
    } elseif ($format === 'pdf') {
        // Simple HTML to PDF conversion (you might want to use a proper PDF library)
        header('Content-Type: text/html; charset=utf-8');
        
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>รายงาน ITA</title>
    <style>
        body { font-family: "Sarabun", Arial, sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; }
        .item { margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; }
        .moit-number { background: #3498db; color: white; padding: 5px 10px; border-radius: 5px; }
        .sub-item { margin: 10px 0; padding: 8px; background: #f8f9fa; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>รายงานระบบประเมิน ITA</h1>
        <p>Information Technology Assessment - มาตรฐานความโปร่งใสและการต่อต้านการทุจริต MOIT</p>
        <p>วันที่ส่งออก: ' . date('d/m/Y H:i:s') . '</p>
    </div>';

        foreach ($items as $item) {
            echo '<div class="item">
                <h3><span class="moit-number">' . htmlspecialchars($item['moit_number']) . '</span> ' . htmlspecialchars($item['title']) . '</h3>';
            
            if ($item['description']) {
                echo '<p><strong>รายละเอียด:</strong> ' . nl2br(htmlspecialchars($item['description'])) . '</p>';
            }
            
            if (!empty($item['sub_items'])) {
                echo '<h4>หัวข้อย่อย:</h4>';
                foreach ($item['sub_items'] as $index => $subItem) {
                    echo '<div class="sub-item">
                        <strong>' . ($index + 1) . '.</strong> ' . htmlspecialchars($subItem['title']);
                    if ($subItem['attachment_name']) {
                        echo ' <span style="color: #27ae60;">📎 ' . htmlspecialchars($subItem['attachment_name']) . '</span>';
                    }
                    echo '</div>';
                }
            }
            
            echo '</div>';
        }

        echo '<script>window.print();</script>
</body>
</html>';
    } else {
        throw new Exception('รูปแบบการส่งออกไม่ถูกต้อง');
    }

} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>เกิดข้อผิดพลาด</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<a href="javascript:history.back()">กลับ</a>';
}
?>