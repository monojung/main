<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'item' => null,
    'subItems' => []
];

try {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        throw new Exception('Invalid ID');
    }
    
    // Get database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get item details
    $stmt = $conn->prepare("SELECT * FROM ita_items WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        throw new Exception('Item not found');
    }
    
    // Get sub-items
    $stmt = $conn->prepare("
        SELECT * FROM ita_sub_items 
        WHERE item_id = ? AND is_active = 1 
        ORDER BY sort_order, id
    ");
    $stmt->execute([$id]);
    $subItems = $stmt->fetchAll();
    
    $response['success'] = true;
    $response['item'] = $item;
    $response['subItems'] = $subItems;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>