<?php
require_once 'config/database.php';
header('Content-Type: application/json');

// Get item ID
$itemId = (int)($_GET['id'] ?? 0);

if (!$itemId) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

try {
    // Get database connection
    $db = new Database();
    $conn = $db->getConnection();

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
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }

    // Get sub-items
    $stmt = $conn->prepare("
        SELECT * FROM ita_sub_items 
        WHERE item_id = ? AND is_active = 1 
        ORDER BY sort_order, id
    ");
    $stmt->execute([$itemId]);
    $subItems = $stmt->fetchAll();

    // Calculate progress
    if (count($subItems) > 0) {
        $completedSubItems = 0;
        foreach ($subItems as $subItem) {
            if ($subItem['status'] === 'completed') {
                $completedSubItems++;
            }
        }
        $item['calculated_progress'] = round(($completedSubItems / count($subItems)) * 100);
    } else {
        $item['calculated_progress'] = $item['progress'];
    }

    echo json_encode([
        'success' => true,
        'item' => $item,
        'subItems' => $subItems
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>