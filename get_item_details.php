<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'config/database.php';

// Get database connection
$db = new Database();
$conn = $db->getConnection();

$itemId = (int)($_GET['id'] ?? 0);

if (!$itemId) {
    echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
    exit;
}

try {
    // Get item details
    $stmt = $conn->prepare("SELECT * FROM ita_items WHERE id = ? AND is_active = 1");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['success' => false, 'error' => 'Item not found']);
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
    
    echo json_encode([
        'success' => true,
        'item' => $item,
        'subItems' => $subItems
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>