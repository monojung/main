<?php
session_start();
require_once 'config/database.php';

// Log logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at)
            VALUES (?, 'logout', 'users', ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        // Silent fail for logging
    }
}

// Clear all session data
session_unset();
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Start new session for logout message
session_start();
$_SESSION['logout_message'] = 'คุณได้ออกจากระบบเรียบร้อยแล้ว';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ออกจากระบบ - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
    <meta http-equiv="refresh" content="3;url=login.php">
</head>
<body class="bg-gradient-to-br from-blue-50 to-green-50 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <!-- Success Animation -->
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
            <span class="text-4xl">✅</span>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-800 mb-4">ออกจากระบบสำเร็จ</h1>
        <p class="text-gray-600 mb-6">ขอบคุณที่ใช้บริการ</p>
        
        <!-- Loading Animation -->
        <div class="flex items-center justify-center space-x-2 text-gray-500 mb-6">
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        </div>
        
        <p class="text-sm text-gray-500 mb-4">กำลังนำคุณกลับสู่หน้าเข้าสู่ระบบ...</p>
        
        <!-- Manual Navigation -->
        <div class="space-y-3">
            <a href="login.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                เข้าสู่ระบบอีกครั้ง
            </a>
            <br>
            <a href="index.php" class="inline-block text-blue-600 hover:text-blue-800 transition duration-300">
                กลับสู่เว็บไซต์หลัก
            </a>
        </div>
    </div>
</body>
</html>