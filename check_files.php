<?php
echo "<h2>🔍 ตรวจสอบไฟล์ที่จำเป็น</h2>";

$required_files = [
    'config/database.php',
    'includes/auth.php',
    'admin/functions.php',
    'uploads/ita/.htaccess'
];

$required_dirs = [
    'config',
    'includes', 
    'admin',
    'uploads',
    'uploads/ita'
];

echo "<h3>📁 ตรวจสอบโฟลเดอร์:</h3>";
foreach ($required_dirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ $dir - มีอยู่<br>";
        if (is_writable($dir)) {
            echo "&nbsp;&nbsp;&nbsp;📝 สามารถเขียนได้<br>";
        } else {
            echo "&nbsp;&nbsp;&nbsp;❌ ไม่สามารถเขียนได้<br>";
        }
    } else {
        echo "❌ $dir - ไม่มี<br>";
        echo "&nbsp;&nbsp;&nbsp;🔧 กำลังสร้าง...<br>";
        if (mkdir($dir, 0755, true)) {
            echo "&nbsp;&nbsp;&nbsp;✅ สร้างเสร็จแล้ว<br>";
        } else {
            echo "&nbsp;&nbsp;&nbsp;❌ ไม่สามารถสร้างได้<br>";
        }
    }
}

echo "<h3>📄 ตรวจสอบไฟล์:</h3>";
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file - มีอยู่<br>";
    } else {
        echo "❌ $file - ไม่มี<br>";
    }
}

echo "<h3>🔧 ตรวจสอบการตั้งค่า PHP:</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";

echo "<h3>📊 ตรวจสอบฐานข้อมูล:</h3>";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        echo "✅ เชื่อมต่อฐานข้อมูลสำเร็จ<br>";
        
        // ตรวจสอบตาราง
        $tables = ['ita_categories', 'ita_items', 'ita_sub_items'];
        foreach ($tables as $table) {
            $stmt = $conn->prepare("SHOW TABLES LIKE '$table'");
            $stmt->execute();
            if ($stmt->fetch()) {
                echo "✅ ตาราง $table - มีอยู่<br>";
            } else {
                echo "❌ ตาราง $table - ไม่มี<br>";
            }
        }
    } else {
        echo "❌ ไม่พบไฟล์ config/database.php<br>";
    }
} catch (Exception $e) {
    echo "❌ เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $e->getMessage() . "<br>";
}

echo "<h3>📋 สรุปการตรวจสอบ:</h3>";
echo "ตรวจสอบเสร็จสิ้น - ดูผลลัพธ์ด้านบนเพื่อแก้ไขปัญหา<br>";
echo "<br><strong>ขั้นตอนต่อไป:</strong><br>";
echo "1. แก้ไขปัญหาที่มีสัญลักษณ์ ❌<br>";
echo "2. ลบไฟล์นี้ออกหลังจากแก้ไขเสร็จ<br>";
echo "3. ลองเข้าใช้งานระบบอีกครั้ง<br>";
?>