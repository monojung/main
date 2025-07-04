<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

// Simple sanitize function (in case functions.php is not working)
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Simple auth check
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // For testing, comment out the redirect
    // header("Location: ../login.php");
    // exit();
    echo "<div style='background: yellow; padding: 10px; margin: 10px;'>Warning: Not logged in (bypassed for testing)</div>";
}

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'ita';
$page_title = "จัดการระบบ ITA";

// Get database connection
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<div style='background: green; color: white; padding: 10px; margin: 10px;'>✅ Database connected successfully</div>";
} catch (Exception $e) {
    die("<div style='background: red; color: white; padding: 10px; margin: 10px;'>❌ Database connection failed: " . $e->getMessage() . "</div>");
}

// Create necessary tables
try {
    // Items table (simplified)
    $createItemsTable = "CREATE TABLE IF NOT EXISTS ita_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        moit_number VARCHAR(20) NOT NULL,
        title TEXT NOT NULL,
        description TEXT,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($createItemsTable);
    echo "<div style='background: blue; color: white; padding: 5px; margin: 5px;'>✅ ita_items table created/verified</div>";

    // Sub-items table (simplified)
    $createSubItemsTable = "CREATE TABLE IF NOT EXISTS ita_sub_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        title TEXT NOT NULL,
        attachment_url VARCHAR(255),
        attachment_name VARCHAR(255),
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES ita_items(id) ON DELETE CASCADE
    )";
    
    $conn->exec($createSubItemsTable);
    echo "<div style='background: blue; color: white; padding: 5px; margin: 5px;'>✅ ita_sub_items table created/verified</div>";

    // Insert default items if none exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ita_items");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $defaultItems = [
            ['MOIT 1', 'การเปิดเผยข้อมูลข่าวสารของหน่วยงาน', 'ข้อมูลข่าวสารที่ต้องเปิดเผยต่อสาธารณะ', 1],
            ['MOIT 2', 'การรับฟังความคิดเห็น', 'ช่องทางการรับฟังความคิดเห็นจากประชาชน', 2],
            ['MOIT 3', 'การเปิดโอกาสให้เกิดการมีส่วนร่วม', 'การสร้างการมีส่วนร่วมของผู้มีส่วนได้ส่วนเสีย', 3],
            ['MOIT 4', 'การจัดซื้อจัดจ้างและการจัดหาพัสดุ', 'ความโปร่งใสในการจัดซื้อจัดจ้าง', 4],
            ['MOIT 5', 'การบริหารทรัพยากรบุคคล', 'การบริหารงานบุคคลอย่างโปร่งใส', 5]
        ];

        foreach ($defaultItems as $item) {
            $stmt = $conn->prepare("INSERT INTO ita_items (moit_number, title, description, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute($item);
        }
        echo "<div style='background: green; color: white; padding: 5px; margin: 5px;'>✅ Default items inserted</div>";
    }

} catch (Exception $e) {
    echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>❌ Database setup error: " . $e->getMessage() . "</div>";
}

// Debug POST data
if ($_POST) {
    echo "<div style='background: orange; padding: 10px; margin: 10px;'>";
    echo "<h3>🔍 DEBUG - POST Data:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    echo "</div>";
}

// Handle form submissions
$message = '';
$error = '';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

echo "<div style='background: lightblue; padding: 10px; margin: 10px;'>Current action: " . htmlspecialchars($action) . "</div>";

if ($_POST && $action) {
    try {
        echo "<div style='background: yellow; padding: 10px; margin: 10px;'>Processing action: " . htmlspecialchars($action) . "</div>";
        
        switch ($action) {
            case 'add_item':
                echo "<div style='background: lightgreen; padding: 5px; margin: 5px;'>🔄 Processing add_item...</div>";
                
                $moit_number = sanitizeInput($_POST['moit_number'] ?? '');
                $title = sanitizeInput($_POST['title'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $sort_order = (int)($_POST['sort_order'] ?? 0);
                
                echo "<div style='background: lightgray; padding: 5px; margin: 5px;'>";
                echo "MOIT Number: " . htmlspecialchars($moit_number) . "<br>";
                echo "Title: " . htmlspecialchars($title) . "<br>";
                echo "Description: " . htmlspecialchars($description) . "<br>";
                echo "Sort Order: " . $sort_order . "<br>";
                echo "</div>";
                
                if (empty($moit_number) || empty($title)) {
                    $error = "กรุณากรอกข้อมูลที่จำเป็น (MOIT Number และ Title)";
                    echo "<div style='background: red; color: white; padding: 5px; margin: 5px;'>❌ Validation failed</div>";
                } else {
                    echo "<div style='background: lightgreen; padding: 5px; margin: 5px;'>✅ Validation passed, attempting database insert...</div>";
                    
                    $stmt = $conn->prepare("INSERT INTO ita_items (moit_number, title, description, sort_order) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$moit_number, $title, $description, $sort_order])) {
                        $message = "เพิ่มรายการ ITA เรียบร้อยแล้ว";
                        $action = '';
                        echo "<div style='background: green; color: white; padding: 5px; margin: 5px;'>✅ Database insert successful</div>";
                    } else {
                        $error = "ไม่สามารถเพิ่มรายการ ITA ได้";
                        echo "<div style='background: red; color: white; padding: 5px; margin: 5px;'>❌ Database insert failed</div>";
                        echo "<div style='background: red; color: white; padding: 5px; margin: 5px;'>Error info: " . print_r($stmt->errorInfo(), true) . "</div>";
                    }
                }
                break;

            case 'edit_item':
                echo "<div style='background: lightgreen; padding: 5px; margin: 5px;'>🔄 Processing edit_item...</div>";
                
                $id = (int)($_POST['id'] ?? 0);
                $moit_number = sanitizeInput($_POST['moit_number'] ?? '');
                $title = sanitizeInput($_POST['title'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $sort_order = (int)($_POST['sort_order'] ?? 0);
                
                if (!$id || empty($moit_number) || empty($title)) {
                    $error = "ข้อมูลไม่ถูกต้อง";
                } else {
                    $stmt = $conn->prepare("UPDATE ita_items SET moit_number = ?, title = ?, description = ?, sort_order = ? WHERE id = ?");
                    if ($stmt->execute([$moit_number, $title, $description, $sort_order, $id])) {
                        $message = "แก้ไขรายการ ITA เรียบร้อยแล้ว";
                        $action = '';
                    } else {
                        $error = "ไม่สามารถแก้ไขรายการ ITA ได้";
                    }
                }
                break;

            case 'add_sub_item':
                echo "<div style='background: lightgreen; padding: 5px; margin: 5px;'>🔄 Processing add_sub_item...</div>";
                
                $item_id = (int)($_POST['item_id'] ?? 0);
                $title = sanitizeInput($_POST['title'] ?? '');
                $sort_order = (int)($_POST['sort_order'] ?? 0);
                
                // Handle file upload
                $attachment_url = null;
                $attachment_name = null;
                
                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    echo "<div style='background: lightblue; padding: 5px; margin: 5px;'>📎 Processing file upload...</div>";
                    
                    $uploadDir = '../uploads/ita/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                        echo "<div style='background: yellow; padding: 5px; margin: 5px;'>📁 Created upload directory</div>";
                    }
                    
                    $fileName = $_FILES['attachment']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Allow only PDF files
                    if ($fileExtension === 'pdf') {
                        $newFileName = uniqid() . '_' . time() . '.pdf';
                        $uploadPath = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath)) {
                            $attachment_url = $newFileName;
                            $attachment_name = $fileName;
                            echo "<div style='background: green; color: white; padding: 5px; margin: 5px;'>✅ File uploaded successfully</div>";
                        } else {
                            $error = "ไม่สามารถอัปโหลดไฟล์ได้";
                            echo "<div style='background: red; color: white; padding: 5px; margin: 5px;'>❌ File upload failed</div>";
                            break;
                        }
                    } else {
                        $error = "กรุณาอัปโหลดไฟล์ PDF เท่านั้น";
                        echo "<div style='background: red; color: white; padding: 5px; margin: 5px;'>❌ Invalid file type</div>";
                        break;
                    }
                }
                
                if (!$item_id || empty($title)) {
                    $error = "กรุณากรอกข้อมูลที่จำเป็น";
                } else {
                    // Check if item exists
                    $stmt = $conn->prepare("SELECT id FROM ita_items WHERE id = ? AND is_active = 1");
                    $stmt->execute([$item_id]);
                    if (!$stmt->fetch()) {
                        $error = "ไม่พบรายการ ITA ที่เลือก";
                        break;
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO ita_sub_items (item_id, title, attachment_url, attachment_name, sort_order) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$item_id, $title, $attachment_url, $attachment_name, $sort_order])) {
                        $message = "เพิ่มหัวข้อย่อยเรียบร้อยแล้ว";
                        $action = '';
                    } else {
                        $error = "ไม่สามารถเพิ่มหัวข้อย่อยได้";
                    }
                }
                break;

            case 'edit_sub_item':
                $id = (int)($_POST['id'] ?? 0);
                $title = sanitizeInput($_POST['title'] ?? '');
                $sort_order = (int)($_POST['sort_order'] ?? 0);
                
                // Handle file upload
                $attachment_url = $_POST['current_attachment_url'] ?? null;
                $attachment_name = $_POST['current_attachment_name'] ?? null;
                
                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/ita/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = $_FILES['attachment']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Allow only PDF files
                    if ($fileExtension === 'pdf') {
                        // Delete old file if exists
                        if ($attachment_url && file_exists($uploadDir . $attachment_url)) {
                            unlink($uploadDir . $attachment_url);
                        }
                        
                        $newFileName = uniqid() . '_' . time() . '.pdf';
                        $uploadPath = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath)) {
                            $attachment_url = $newFileName;
                            $attachment_name = $fileName;
                        }
                    } else {
                        $error = "กรุณาอัปโหลดไฟล์ PDF เท่านั้น";
                        break;
                    }
                }
                
                if (!$id || empty($title)) {
                    $error = "ข้อมูลไม่ถูกต้อง";
                } else {
                    $stmt = $conn->prepare("UPDATE ita_sub_items SET title = ?, attachment_url = ?, attachment_name = ?, sort_order = ? WHERE id = ?");
                    if ($stmt->execute([$title, $attachment_url, $attachment_name, $sort_order, $id])) {
                        $message = "แก้ไขหัวข้อย่อยเรียบร้อยแล้ว";
                        $action = '';
                    } else {
                        $error = "ไม่สามารถแก้ไขหัวข้อย่อยได้";
                    }
                }
                break;

            case 'delete_item':
                $id = (int)($_POST['id'] ?? 0);
                if ($id) {
                    $stmt = $conn->prepare("DELETE FROM ita_items WHERE id = ?");
                    if ($stmt->execute([$id])) {
                        $message = "ลบรายการ ITA เรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถลบรายการ ITA ได้";
                    }
                }
                break;

            case 'delete_sub_item':
                $id = (int)($_POST['id'] ?? 0);
                if ($id) {
                    // Get file info before deleting
                    $stmt = $conn->prepare("SELECT attachment_url FROM ita_sub_items WHERE id = ?");
                    $stmt->execute([$id]);
                    $subItem = $stmt->fetch();
                    
                    // Delete the record
                    $stmt = $conn->prepare("DELETE FROM ita_sub_items WHERE id = ?");
                    if ($stmt->execute([$id])) {
                        // Delete file if exists
                        if ($subItem && $subItem['attachment_url']) {
                            $filePath = '../uploads/ita/' . $subItem['attachment_url'];
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }
                        $message = "ลบหัวข้อย่อยเรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถลบหัวข้อย่อยได้";
                    }
                }
                break;
                
            default:
                echo "<div style='background: red; color: white; padding: 5px; margin: 5px;'>❌ Unknown action: " . htmlspecialchars($action) . "</div>";
                break;
        }
    } catch (Exception $e) {
        error_log("Error in ITA management: " . $e->getMessage());
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>❌ Exception: " . $e->getMessage() . "</div>";
    }
}

// Get data for editing
$edit_item = null;
$edit_sub_item = null;

if ($action === 'edit_item' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM ita_items WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $edit_item = $stmt->fetch();
}

if ($action === 'edit_sub_item' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM ita_sub_items WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $edit_sub_item = $stmt->fetch();
}

// Get items for sub-item forms
$items = [];
if ($action === 'add_sub_item' || $action === 'edit_sub_item') {
    try {
        $stmt = $conn->prepare("SELECT * FROM ita_items WHERE is_active = 1 ORDER BY sort_order, moit_number");
        $stmt->execute();
        $items = $stmt->fetchAll();
    } catch (Exception $e) {
        echo "<div style='background: red; color: white; padding: 5px; margin: 5px;'>❌ Error loading items: " . $e->getMessage() . "</div>";
    }
}

// Get statistics
$stats = [
    'items' => 0,
    'sub_items' => 0
];

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ita_items WHERE is_active = 1");
    $stmt->execute();
    $stats['items'] = $stmt->fetchColumn() ?? 0;
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ita_sub_items WHERE is_active = 1");
    $stmt->execute();
    $stats['sub_items'] = $stmt->fetchColumn() ?? 0;
    
    echo "<div style='background: lightgreen; padding: 10px; margin: 10px;'>📊 Stats - Items: {$stats['items']}, Sub-items: {$stats['sub_items']}</div>";
} catch (Exception $e) {
    echo "<div style='background: red; color: white; padding: 5px; margin: 5px;'>❌ Error loading stats: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-cyan-700 text-white shadow-2xl sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                        <span class="text-white font-bold text-xl">🔧</span>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold">จัดการระบบ ITA (Debug Mode)</h1>
                        <p class="text-blue-200 text-sm">ระบบประเมิน Information Technology Assessment</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../ita.php" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-xl transition duration-300 shadow-lg">
                        👁️ ดูระบบประเมิน
                    </a>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl transition duration-300 shadow-lg">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Messages -->
        <?php if ($message): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 px-4 py-3 rounded-lg fade-in">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg fade-in">
            <div class="flex items-center">
                <span class="text-2xl mr-3">❌</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($action)): ?>
        <!-- Dashboard View -->
        <div class="mb-8 fade-in">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">📊 แดชบอร์ดจัดการ ITA</h2>
            <p class="text-gray-200">จัดการรายการและหัวข้อย่อยของระบบประเมิน ITA</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="glass-card rounded-2xl p-6 fade-in">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600"><?php echo $stats['items']; ?></div>
                    <div class="text-sm text-gray-600">รายการ ITA</div>
                </div>
            </div>
            
            <div class="glass-card rounded-2xl p-6 fade-in">
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600"><?php echo $stats['sub_items']; ?></div>
                    <div class="text-sm text-gray-600">หัวข้อย่อย</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass-card rounded-2xl p-8 mb-8 fade-in">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">🚀 การดำเนินการด่วน</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="?action=add_item" class="bg-green-600 text-white p-4 rounded-xl hover:bg-green-700 transition duration-300 text-center">
                    <div class="text-2xl mb-2">📋</div>
                    <div class="font-medium">เพิ่มรายการ ITA</div>
                </a>
                
                <a href="?action=add_sub_item" class="bg-purple-600 text-white p-4 rounded-xl hover:bg-purple-700 transition duration-300 text-center">
                    <div class="text-2xl mb-2">📝</div>
                    <div class="font-medium">เพิ่มหัวข้อย่อย</div>
                </a>
            </div>
        </div>

        <!-- Current Items Display -->
        <div class="glass-card rounded-2xl overflow-hidden fade-in">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-blue-50">
                <h3 class="text-xl font-semibold text-gray-800">📋 รายการ ITA ปัจจุบัน</h3>
            </div>
            <div class="p-6">
                <?php 
                try {
                    $stmt = $conn->prepare("SELECT * FROM ita_items WHERE is_active = 1 ORDER BY sort_order, moit_number");
                    $stmt->execute();
                    $allItems = $stmt->fetchAll();
                } catch (Exception $e) {
                    $allItems = [];
                    echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>Error loading items: " . $e->getMessage() . "</div>";
                }
                ?>
                
                <?php if (empty($allItems)): ?>
                <div class="text-center py-8">
                    <div class="text-4xl mb-4">📋</div>
                    <p class="text-gray-500 mb-4">ยังไม่มีรายการ ITA</p>
                    <a href="?action=add_item" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300">
                        เพิ่มรายการแรก
                    </a>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($allItems as $item): ?>
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($item['moit_number']); ?>
                                    </span>
                                    <span class="text-xs text-gray-500">ID: <?php echo $item['id']; ?></span>
                                </div>
                                <div class="font-medium text-base mb-1"><?php echo htmlspecialchars($item['title']); ?></div>
                                <?php if (!empty($item['description'])): ?>
                                <div class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <a href="?action=edit_item&id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm px-2 py-1 bg-blue-50 rounded">
                                    ✏️ แก้ไข
                                </a>
                                <button onclick="confirmDelete('item', <?php echo $item['id']; ?>)" class="text-red-600 hover:text-red-800 text-sm px-2 py-1 bg-red-50 rounded">
                                    🗑️ ลบ
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- Form Views -->
        <div class="mb-8 fade-in">
            <div class="flex items-center space-x-4 mb-4">
                <a href="?" class="text-white hover:text-gray-200 transition duration-200">
                    <span class="text-2xl">←</span>
                </a>
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white">
                        <?php 
                        echo $action === 'add_item' ? '📋 เพิ่มรายการ ITA ใหม่' : 
                            ($action === 'edit_item' ? '✏️ แก้ไขรายการ ITA' : 
                            ($action === 'add_sub_item' ? '📝 เพิ่มหัวข้อย่อยใหม่' : 
                            ($action === 'edit_sub_item' ? '✏️ แก้ไขหัวข้อย่อย' : 'จัดการ ITA')));
                        ?>
                    </h2>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-8 fade-in">
            <?php if (in_array($action, ['add_item', 'edit_item'])): ?>
            <!-- Item Form -->
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                <?php if ($edit_item): ?>
                <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">หมายเลข MOIT *</label>
                        <input type="text" name="moit_number" required 
                               value="<?php echo htmlspecialchars($edit_item['moit_number'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="เช่น MOIT 1">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับการแสดง</label>
                        <input type="number" name="sort_order" min="0" 
                               value="<?php echo htmlspecialchars($edit_item['sort_order'] ?? '0'); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">หัวข้อ *</label>
                        <textarea name="title" required rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="กรอกหัวข้อของรายการ ITA"><?php echo htmlspecialchars($edit_item['title'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย</label>
                        <textarea name="description" rows="4" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="อธิบายรายละเอียดของรายการ ITA"><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="?" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-6 py-3 rounded-xl transition duration-300 font-medium">
                        ยกเลิก
                    </a>
                    <button type="submit" 
                            class="bg-green-600 text-white hover:bg-green-700 px-6 py-3 rounded-xl transition duration-300 font-medium flex items-center space-x-2">
                        <span><?php echo $action === 'add_item' ? '📋' : '💾'; ?></span>
                        <span><?php echo $action === 'add_item' ? 'เพิ่มรายการ ITA' : 'บันทึกการแก้ไข'; ?></span>
                    </button>
                </div>
            </form>

            <?php elseif (in_array($action, ['add_sub_item', 'edit_sub_item'])): ?>
            <!-- Sub Item Form -->
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                <?php if ($edit_sub_item): ?>
                <input type="hidden" name="id" value="<?php echo $edit_sub_item['id']; ?>">
                <input type="hidden" name="current_attachment_url" value="<?php echo htmlspecialchars($edit_sub_item['attachment_url'] ?? ''); ?>">
                <input type="hidden" name="current_attachment_name" value="<?php echo htmlspecialchars($edit_sub_item['attachment_name'] ?? ''); ?>">
                <?php else: ?>
                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($_GET['item_id'] ?? ''); ?>">
                <?php endif; ?>
                
                <?php if ($action === 'add_sub_item' && empty($_GET['item_id'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">เลือกรายการ ITA *</label>
                    <select name="item_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">เลือกรายการ ITA</option>
                        <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item['id']; ?>">
                            <?php echo htmlspecialchars($item['moit_number'] . ' - ' . substr($item['title'], 0, 50) . (strlen($item['title']) > 50 ? '...' : '')); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">หัวข้อย่อย *</label>
                        <textarea name="title" required rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="กรอกหัวข้อย่อย"><?php echo htmlspecialchars($edit_sub_item['title'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">📎 ไฟล์แนบ (PDF เท่านั้น)</label>
                        <input type="file" name="attachment" accept=".pdf" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php if ($edit_sub_item && $edit_sub_item['attachment_url']): ?>
                        <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="text-yellow-600 mr-2">📄</span>
                                    <span class="text-sm text-gray-700">ไฟล์ปัจจุบัน: <?php echo htmlspecialchars($edit_sub_item['attachment_name'] ?: 'document.pdf'); ?></span>
                                </div>
                                <a href="../uploads/ita/<?php echo htmlspecialchars($edit_sub_item['attachment_url']); ?>" 
                                   target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    👁️ ดูไฟล์
                                </a>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">หากต้องการเปลี่ยนไฟล์ ให้เลือกไฟล์ใหม่</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ลำดับการแสดง</label>
                        <input type="number" name="sort_order" min="0" 
                               value="<?php echo htmlspecialchars($edit_sub_item['sort_order'] ?? '0'); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="?" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-6 py-3 rounded-xl transition duration-300 font-medium">
                        ยกเลิก
                    </a>
                    <button type="submit" 
                            class="bg-purple-600 text-white hover:bg-purple-700 px-6 py-3 rounded-xl transition duration-300 font-medium flex items-center space-x-2">
                        <span><?php echo $action === 'add_sub_item' ? '📝' : '💾'; ?></span>
                        <span><?php echo $action === 'add_sub_item' ? 'เพิ่มหัวข้อย่อย' : 'บันทึกการแก้ไข'; ?></span>
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Delete confirmation
        function confirmDelete(type, id) {
            const typeText = {
                'item': 'รายการ ITA',
                'sub_item': 'หัวข้อย่อย'
            };
            
            if (confirm(`คุณต้องการลบ${typeText[type]}นี้หรือไม่? การดำเนินการนี้ไม่สามารถย้อนกลับได้`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_${type}">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔧 Debug ITA Management system loaded successfully!');
            
            const forms = document.querySelectorAll('form[method="POST"]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    console.log('Form submitted:', this);
                    console.log('Form data:', new FormData(this));
                    
                    const requiredFields = form.querySelectorAll('[required]');
                    let hasError = false;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            hasError = true;
                            field.classList.add('border-red-500');
                            console.log('Required field empty:', field.name);
                        } else {
                            field.classList.remove('border-red-500');
                        }
                    });
                    
                    if (hasError) {
                        e.preventDefault();
                        alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span>กำลังบันทึก...';
                        submitBtn.disabled = true;
                        
                        console.log('Form submission started...');
                    }
                });
            });
        });
    </script>
</body>
</html>