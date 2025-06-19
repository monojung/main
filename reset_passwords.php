<?php
// ไฟล์รีเซ็ตรหัสผ่าน - บันทึกเป็น reset_passwords.php
// รันครั้งเดียวแล้วลบทิ้ง เพื่อความปลอดภัย

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>รีเซ็ตรหัสผ่านผู้ใช้</h1>";

try {
    require_once 'config/database.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
    }
    
    // รหัสผ่านใหม่
    $passwords = [
        'admin' => 'admin123',
        'staff' => 'staff123', 
        'doctor' => 'doctor123',
        'nurse1' => 'nurse123'
    ];
    
    echo "<h2>กำลังอัพเดทรหัสผ่าน...</h2>";
    
    foreach ($passwords as $username => $password) {
        // สร้าง hash ใหม่
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // อัพเดทในฐานข้อมูล
        $stmt = $conn->prepare("
            UPDATE users 
            SET password_hash = ?, login_attempts = 0, locked_until = NULL, updated_at = NOW()
            WHERE username = ?
        ");
        
        if ($stmt->execute([$password_hash, $username])) {
            echo "<p>✅ อัพเดท $username สำเร็จ (รหัสผ่าน: $password)</p>";
        } else {
            echo "<p>❌ ไม่สามารถอัพเดท $username ได้</p>";
        }
    }
    
    echo "<h2>ตรวจสอบผู้ใช้ในระบบ:</h2>";
    $stmt = $conn->prepare("SELECT username, role, is_active, login_attempts, locked_until FROM users ORDER BY role, username");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ชื่อผู้ใช้</th><th>บทบาท</th><th>สถานะ</th><th>ครั้งที่ผิด</th><th>ล็อคถึง</th><th>รหัสผ่าน</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        $password = isset($passwords[$user['username']]) ? $passwords[$user['username']] : 'ไม่ทราบ';
        echo "<tr>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>" . ($user['is_active'] ? 'ใช้งานได้' : 'ถูกปิด') . "</td>";
        echo "<td>{$user['login_attempts']}</td>";
        echo "<td>" . ($user['locked_until'] ? $user['locked_until'] : '-') . "</td>";
        echo "<td><strong>$password</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>🔐 ข้อมูลการเข้าสู่ระบบ:</h3>";
    echo "<ul>";
    echo "<li><strong>ระบบเจ้าหน้าที่:</strong> <a href='login.php'>login.php</a></li>";
    echo "<li><strong>ระบบผู้ดูแล:</strong> <a href='admin/login.php'>admin/login.php</a></li>";
    echo "</ul>";
    
    echo "<h3>📋 บัญชีทดสอบ:</h3>";
    echo "<ul>";
    foreach ($passwords as $username => $password) {
        echo "<li><strong>$username</strong> / $password</li>";
    }
    echo "</ul>";
    
    echo "<div style='background: #fee; border: 1px solid #fcc; padding: 10px; margin: 20px 0;'>";
    echo "<strong>⚠️ คำเตือน:</strong> ลบไฟล์นี้ทิ้งหลังจากใช้งานเสร็จ เพื่อความปลอดภัย";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ เกิดข้อผิดพลาด:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}
?>

<style>
body { 
    font-family: 'Sarabun', sans-serif; 
    margin: 20px; 
    background: #f5f5f5;
}
table { 
    background: white; 
    margin: 10px 0;
}
th, td { 
    padding: 8px 12px; 
    text-align: left;
}
th { 
    background: #e0e0e0; 
}
h1 { color: #2563eb; }
h2 { color: #059669; }
</style>