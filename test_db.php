<?php
// ไฟล์ทดสอบการเชื่อมต่อฐานข้อมูล
// บันทึกเป็น test_db.php ในโฟลเดอร์หลัก

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>ทดสอบการเชื่อมต่อฐานข้อมูล</h1>";

try {
    require_once 'config/database.php';
    
    echo "<h2>✅ โหลดไฟล์ config สำเร็จ</h2>";
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<h2>✅ เชื่อมต่อฐานข้อมูลสำเร็จ</h2>";
        
        // ตรวจสอบตาราง
        $tables = ['users', 'departments', 'appointments', 'news', 'settings'];
        
        foreach ($tables as $table) {
            try {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table");
                $stmt->execute();
                $result = $stmt->fetch();
                echo "<p>✅ ตาราง $table: {$result['count']} รายการ</p>";
            } catch (Exception $e) {
                echo "<p>❌ ตาราง $table: " . $e->getMessage() . "</p>";
            }
        }
        
        // ตรวจสอบผู้ใช้
        echo "<h3>ผู้ใช้ในระบบ:</h3>";
        $stmt = $conn->prepare("SELECT username, role FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            echo "<p>- {$user['username']} ({$user['role']})</p>";
        }
        
    } else {
        echo "<h2>❌ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</h2>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ เกิดข้อผิดพลาด:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h3>ข้อมูลระบบ:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// ตรวจสอบโฟลเดอร์
$folders = ['config', 'includes', 'admin', 'staff', 'logs'];
echo "<h3>โฟลเดอร์:</h3>";
foreach ($folders as $folder) {
    $exists = is_dir($folder);
    $writable = is_writable($folder);
    echo "<p>$folder: " . ($exists ? '✅ มีอยู่' : '❌ ไม่มี') . 
         ($exists && $writable ? ' (เขียนได้)' : '') . "</p>";
}
?>

<style>
body { font-family: 'Sarabun', sans-serif; margin: 20px; }
h1 { color: #2563eb; }
h2 { color: #059669; }
p { margin: 5px 0; }
</style>