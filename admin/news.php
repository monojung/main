<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin role
requireAdmin('../login.php');

$page_title = "จัดการข่าวสาร";

// Helper function to safely format numbers
function safeNumberFormat($number, $decimals = 0) {
    return number_format($number ?? 0, $decimals);
}

// Check if required columns exist and add them if they don't
function checkAndCreateColumns($conn) {
    try {
        // Check if featured_image column exists
        $stmt = $conn->prepare("SHOW COLUMNS FROM news LIKE 'featured_image'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $conn->exec("ALTER TABLE news ADD COLUMN featured_image VARCHAR(255) DEFAULT NULL AFTER tags");
        }
        
        // Check if attachments column exists
        $stmt = $conn->prepare("SHOW COLUMNS FROM news LIKE 'attachments'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $conn->exec("ALTER TABLE news ADD COLUMN attachments JSON DEFAULT NULL AFTER featured_image");
        }
        
        // Check if views column exists and has default value
        $stmt = $conn->prepare("SHOW COLUMNS FROM news LIKE 'views'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $conn->exec("ALTER TABLE news ADD COLUMN views INT DEFAULT 0 AFTER attachments");
        } else {
            // Update existing null views to 0
            $conn->exec("UPDATE news SET views = 0 WHERE views IS NULL");
        }
        
    } catch (Exception $e) {
        logError("Error checking/creating columns: " . $e->getMessage(), __FILE__, __LINE__);
    }
}

// Handle file upload
function uploadFile($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('ประเภทไฟล์ไม่รองรับ');
    }
    
    $max_size = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_size) {
        throw new Exception('ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 10MB)');
    }
    
    $upload_dir = '../uploads/news/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/news/' . $filename;
    }
    
    throw new Exception('ไม่สามารถอัพโหลดไฟล์ได้');
}

// Handle actions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($action === 'add') {
            $title = sanitizeInput($_POST['title'] ?? '');
            $content = sanitizeInput($_POST['content'] ?? '');
            $excerpt = sanitizeInput($_POST['excerpt'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? 'general');
            $status = sanitizeInput($_POST['status'] ?? 'draft');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
            $tags = sanitizeInput($_POST['tags'] ?? '');
            
            if (empty($title) || empty($content)) {
                $error = 'กรุณากรอกหัวข้อและเนื้อหา';
            } else {
                // Generate slug
                $slug = generateSlug($title);
                
                // Check if slug already exists
                $stmt = $conn->prepare("SELECT id FROM news WHERE slug = ?");
                $stmt->execute(array($slug));
                if ($stmt->fetch()) {
                    $slug .= '-' . time();
                }
                
                // Handle file uploads
                $featured_image = null;
                $attachments = [];
                
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    $featured_image = uploadFile($_FILES['featured_image'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                }
                
                if (isset($_FILES['attachments'])) {
                    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                            $file_data = [
                                'name' => $_FILES['attachments']['name'][$key],
                                'type' => $_FILES['attachments']['type'][$key],
                                'tmp_name' => $tmp_name,
                                'error' => $_FILES['attachments']['error'][$key],
                                'size' => $_FILES['attachments']['size'][$key]
                            ];
                            $attachment_path = uploadFile($file_data);
                            if ($attachment_path) {
                                $attachments[] = [
                                    'name' => $_FILES['attachments']['name'][$key],
                                    'path' => $attachment_path
                                ];
                            }
                        }
                    }
                }
                
                $publish_date = null;
                if ($status === 'published') {
                    $publish_date = date('Y-m-d H:i:s');
                }
                
                $stmt = $conn->prepare("
                    INSERT INTO news (title, slug, content, excerpt, category, status, 
                                    is_featured, is_urgent, tags, author_id, publish_date, 
                                    featured_image, attachments, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute(array(
                    $title, $slug, $content, $excerpt, $category, $status,
                    $is_featured, $is_urgent, $tags, $_SESSION['user_id'], $publish_date,
                    $featured_image, json_encode($attachments)
                ))) {
                    $news_id = $conn->lastInsertId();
                    logActivity($conn, $_SESSION['user_id'], 'news_created', 'news', $news_id, null, array(
                        'title' => $title,
                        'category' => $category,
                        'status' => $status
                    ));
                    $message = 'เพิ่มข่าวสารเรียบร้อยแล้ว';
                } else {
                    $error = 'เกิดข้อผิดพลาดในการบันทึก';
                }
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $title = sanitizeInput($_POST['title'] ?? '');
            $content = sanitizeInput($_POST['content'] ?? '');
            $excerpt = sanitizeInput($_POST['excerpt'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? 'general');
            $status = sanitizeInput($_POST['status'] ?? 'draft');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
            $tags = sanitizeInput($_POST['tags'] ?? '');
            
            if (empty($title) || empty($content)) {
                $error = 'กรุณากรอกหัวข้อและเนื้อหา';
            } else {
                // Get old data
                $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
                $stmt->execute(array($id));
                $old_data = $stmt->fetch();
                
                if ($old_data) {
                    $publish_date = $old_data['publish_date'];
                    if ($status === 'published' && $old_data['status'] !== 'published') {
                        $publish_date = date('Y-m-d H:i:s');
                    } elseif ($status !== 'published') {
                        $publish_date = null;
                    }
                    
                    // Handle file uploads
                    $featured_image = $old_data['featured_image'];
                    $attachments = json_decode($old_data['attachments'] ?? '[]', true);
                    
                    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                        // Delete old featured image
                        if ($featured_image && file_exists('../' . $featured_image)) {
                            unlink('../' . $featured_image);
                        }
                        $featured_image = uploadFile($_FILES['featured_image'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                    }
                    
                    if (isset($_FILES['attachments'])) {
                        foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                            if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                                $file_data = [
                                    'name' => $_FILES['attachments']['name'][$key],
                                    'type' => $_FILES['attachments']['type'][$key],
                                    'tmp_name' => $tmp_name,
                                    'error' => $_FILES['attachments']['error'][$key],
                                    'size' => $_FILES['attachments']['size'][$key]
                                ];
                                $attachment_path = uploadFile($file_data);
                                if ($attachment_path) {
                                    $attachments[] = [
                                        'name' => $_FILES['attachments']['name'][$key],
                                        'path' => $attachment_path
                                    ];
                                }
                            }
                        }
                    }
                    
                    $stmt = $conn->prepare("
                        UPDATE news 
                        SET title = ?, content = ?, excerpt = ?, category = ?, status = ?, 
                            is_featured = ?, is_urgent = ?, tags = ?, publish_date = ?, 
                            featured_image = ?, attachments = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    
                    if ($stmt->execute(array(
                        $title, $content, $excerpt, $category, $status,
                        $is_featured, $is_urgent, $tags, $publish_date,
                        $featured_image, json_encode($attachments), $id
                    ))) {
                        logActivity($conn, $_SESSION['user_id'], 'news_updated', 'news', $id, array(
                            'title' => $old_data['title'],
                            'status' => $old_data['status']
                        ), array(
                            'title' => $title,
                            'status' => $status
                        ));
                        $message = 'แก้ไขข่าวสารเรียบร้อยแล้ว';
                    } else {
                        $error = 'เกิดข้อผิดพลาดในการแก้ไข';
                    }
                } else {
                    $error = 'ไม่พบข่าวสารนี้';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            
            // Get news info before deletion
            $stmt = $conn->prepare("SELECT title, featured_image, attachments FROM news WHERE id = ?");
            $stmt->execute(array($id));
            $news_info = $stmt->fetch();
            
            if ($news_info) {
                // Delete associated files
                if ($news_info['featured_image'] && file_exists('../' . $news_info['featured_image'])) {
                    unlink('../' . $news_info['featured_image']);
                }
                
                $attachments = json_decode($news_info['attachments'] ?? '[]', true);
                foreach ($attachments as $attachment) {
                    if (file_exists('../' . $attachment['path'])) {
                        unlink('../' . $attachment['path']);
                    }
                }
                
                $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
                if ($stmt->execute(array($id))) {
                    logActivity($conn, $_SESSION['user_id'], 'news_deleted', 'news', $id, array(
                        'title' => $news_info['title']
                    ), null);
                    $message = 'ลบข่าวสารเรียบร้อยแล้ว';
                } else {
                    $error = 'เกิดข้อผิดพลาดในการลบ';
                }
            } else {
                $error = 'ไม่พบข่าวสารนี้';
            }
        } elseif ($action === 'delete_attachment') {
            $news_id = (int)($_POST['news_id'] ?? 0);
            $attachment_index = (int)($_POST['attachment_index'] ?? 0);
            
            $stmt = $conn->prepare("SELECT attachments FROM news WHERE id = ?");
            $stmt->execute(array($news_id));
            $news_data = $stmt->fetch();
            
            if ($news_data) {
                $attachments = json_decode($news_data['attachments'] ?? '[]', true);
                if (isset($attachments[$attachment_index])) {
                    // Delete file
                    if (file_exists('../' . $attachments[$attachment_index]['path'])) {
                        unlink('../' . $attachments[$attachment_index]['path']);
                    }
                    
                    // Remove from array
                    array_splice($attachments, $attachment_index, 1);
                    
                    // Update database
                    $stmt = $conn->prepare("UPDATE news SET attachments = ? WHERE id = ?");
                    if ($stmt->execute(array(json_encode($attachments), $news_id))) {
                        $message = 'ลบไฟล์แนบเรียบร้อยแล้ว';
                    } else {
                        $error = 'เกิดข้อผิดพลาดในการลบไฟล์แนบ';
                    }
                }
            }
        }
    } catch (Exception $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        logError($e->getMessage(), __FILE__, __LINE__);
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check and create missing columns
    if ($conn) {
        checkAndCreateColumns($conn);
    }
    
    // Build WHERE clause
    $where_conditions = array("1=1");
    $params = array();
    
    if (!empty($status_filter)) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($category_filter)) {
        $where_conditions[] = "category = ?";
        $params[] = $category_filter;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
        $search_term = '%' . $search . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM news WHERE $where_clause";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_news = $stmt->fetch()['total'];
    
    // Get news list
    $list_params = $params;
    $list_params[] = $per_page;
    $list_params[] = $offset;
    
    $news_sql = "
        SELECT n.*, u.first_name, u.last_name,
               COALESCE(n.views, 0) as views
        FROM news n 
        LEFT JOIN users u ON n.author_id = u.id
        WHERE $where_clause
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($news_sql);
    $stmt->execute($list_params);
    $news_list = $stmt->fetchAll();
    
    // Get statistics
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
            SUM(CASE WHEN is_urgent = 1 THEN 1 ELSE 0 END) as urgent
        FROM news
    ";
    $stmt = $conn->prepare($stats_sql);
    $stmt->execute();
    $stats = $stmt->fetch();
    
    // Ensure all stats are integers
    $stats = array_map(function($value) {
        return (int)($value ?? 0);
    }, $stats);
    
} catch (Exception $e) {
    $error = "เกิดข้อผิดพลาดในการโหลดข้อมูล";
    logError($e->getMessage(), __FILE__, __LINE__);
    $news_list = array();
    $stats = array('total' => 0, 'published' => 0, 'draft' => 0, 'featured' => 0, 'urgent' => 0);
    $total_news = 0;
}

$total_pages = ceil($total_news / $per_page);

// Function to generate URL-friendly slug
function generateSlug($text) {
    $slug = mb_strtolower($text, 'UTF-8');
    $slug = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug ?: 'news-' . time();
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
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .hover-lift { transition: transform 0.2s ease; }
        .hover-lift:hover { transform: translateY(-2px); }
        .status-indicator { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .status-published { background-color: #10b981; }
        .status-draft { background-color: #f59e0b; }
        .status-archived { background-color: #6b7280; }
        .attachment-item { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 8px; }
        .file-preview { max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Enhanced Navigation -->
    <nav class="bg-gradient-to-r from-blue-800 to-blue-900 text-white shadow-xl">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <span class="text-white font-bold text-lg">THC</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">ระบบจัดการโรงพยาบาลทุ่งหัวช้าง</h1>
                        <p class="text-blue-200 text-sm">ระบบจัดการข่าวสารและประกาศ</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p class="text-xs text-blue-200"><?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition duration-300 hover-lift">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex min-h-screen">
        <!-- Enhanced Sidebar -->
        <aside class="w-64 bg-white shadow-xl border-r border-gray-200">
            <div class="p-6">
                <div class="space-y-2">
                    <a href="dashboard.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">📊</span> แดชบอร์ด
                    </a>
                    <a href="news.php" class="flex items-center py-3 px-4 text-blue-600 bg-blue-50 rounded-lg font-medium border-l-4 border-blue-600">
                        <span class="text-xl mr-3">📰</span> จัดการข่าวสาร
                    </a>
                    <a href="reports.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">📊</span> รายงาน
                    </a>

                    <a href="users.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">👨‍💼</span> จัดการผู้ใช้
                    </a>

                    <a href="settings.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">⚙️</span> ตั้งค่าระบบ
                    </a>
                    <hr class="my-3">
                    <a href="../index.php" target="_blank" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">🌐</span> เว็บไซต์หลัก
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Enhanced Messages -->
            <?php if ($message): ?>
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 fade-in shadow-sm">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span><?php echo $message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 fade-in shadow-sm">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-800 mb-2">จัดการข่าวสาร</h2>
                        <p class="text-gray-600">จัดการข่าวสารและประกาศของโรงพยาบาล พร้อมระบบอัพโหลดไฟล์</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">อัปเดตล่าสุด</p>
                        <p class="text-lg font-semibold text-gray-700"><?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total']); ?></div>
                            <div class="text-blue-100">ทั้งหมด</div>
                        </div>
                        <div class="text-4xl opacity-80">📰</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['published']); ?></div>
                            <div class="text-green-100">เผยแพร่แล้ว</div>
                        </div>
                        <div class="text-4xl opacity-80">✅</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['draft']); ?></div>
                            <div class="text-yellow-100">ฉบับร่าง</div>
                        </div>
                        <div class="text-4xl opacity-80">📝</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['featured']); ?></div>
                            <div class="text-purple-100">ข่าวเด่น</div>
                        </div>
                        <div class="text-4xl opacity-80">⭐</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['urgent']); ?></div>
                            <div class="text-red-100">ข่าวด่วน</div>
                        </div>
                        <div class="text-4xl opacity-80">🚨</div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Action Bar -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                    <button onclick="openAddModal()" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-300 hover-lift shadow-lg">
                        <span class="text-xl mr-2">➕</span> เพิ่มข่าวสารใหม่
                    </button>
                    
                    <!-- Enhanced Search and Filter -->
                    <form method="GET" class="flex flex-col md:flex-row gap-3">
                        <div class="relative">
                            <input type="text" name="search" placeholder="ค้นหาข่าวสาร..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                            <span class="absolute left-3 top-3 text-gray-400">🔍</span>
                        </div>
                        
                        <select name="status" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">ทุกสถานะ</option>
                            <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>✅ เผยแพร่แล้ว</option>
                            <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>📝 ฉบับร่าง</option>
                            <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>📦 เก็บถาวร</option>
                        </select>
                        
                        <select name="category" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">ทุกหมวดหมู่</option>
                            <option value="announcement" <?php echo $category_filter === 'announcement' ? 'selected' : ''; ?>>📢 ประชาสัมพันธ์</option>
                            <option value="jobs" <?php echo $category_filter === 'jobs' ? 'selected' : ''; ?>>💼 สมัครงาน</option>
                            <option value="procurement" <?php echo $category_filter === 'procurement' ? 'selected' : ''; ?>>🛒 จัดซื้อจัดจ้าง</option>
                            <option value="accounting" <?php echo $category_filter === 'accounting' ? 'selected' : ''; ?>>💰 การบัญชี</option>
                            <option value="general" <?php echo $category_filter === 'general' ? 'selected' : ''; ?>>📄 ทั่วไป</option>
                            <option value="health_tips" <?php echo $category_filter === 'health_tips' ? 'selected' : ''; ?>>🏥 สุขภาพ</option>
                        </select>
                        
                        <button type="submit" 
                                class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition duration-300 hover-lift">
                            🔍 ค้นหา
                        </button>
                        
                        <?php if (!empty($search) || !empty($status_filter) || !empty($category_filter)): ?>
                        <a href="news.php" class="bg-gray-400 text-white px-6 py-3 rounded-lg hover:bg-gray-500 transition duration-300 hover-lift">
                            ✕ ล้าง
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Enhanced News Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>📰</span>
                                        <span>หัวข้อ</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>📁</span>
                                        <span>หมวดหมู่</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>📊</span>
                                        <span>สถานะ</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>👤</span>
                                        <span>ผู้เขียน</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>📅</span>
                                        <span>วันที่</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>🔧</span>
                                        <span>จัดการ</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($news_list)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                    <div class="text-6xl mb-4">📰</div>
                                    <div class="text-xl font-semibold mb-2">ไม่พบข้อมูลข่าวสาร</div>
                                    <div class="text-gray-400">ลองปรับเปลี่ยนคำค้นหาหรือเพิ่มข่าวสารใหม่</div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($news_list as $news): ?>
                            <tr class="hover:bg-gray-50 transition duration-200">
                                <td class="px-6 py-4">
                                    <div class="flex items-start space-x-4">
                                        <?php if ($news['featured_image']): ?>
                                        <img src="../<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($news['title']); ?>"
                                             class="w-16 h-16 object-cover rounded-lg shadow-sm">
                                        <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <span class="text-2xl">📰</span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <h3 class="text-sm font-semibold text-gray-900 mb-1">
                                                <?php echo htmlspecialchars($news['title']); ?>
                                                <?php if ($news['is_featured']): ?>
                                                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">⭐ เด่น</span>
                                                <?php endif; ?>
                                                <?php if ($news['is_urgent']): ?>
                                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">🚨 ด่วน</span>
                                                <?php endif; ?>
                                            </h3>
                                            <?php if ($news['excerpt']): ?>
                                            <p class="text-xs text-gray-500 mb-2 line-clamp-2"><?php echo htmlspecialchars(mb_substr($news['excerpt'], 0, 100)); ?>...</p>
                                            <?php endif; ?>
                                            <div class="flex items-center space-x-4 text-xs text-gray-400">
                                                <span>👁️ <?php echo number_format($news['views']); ?> ครั้ง</span>
                                                <?php 
                                                $attachments = json_decode($news['attachments'] ?? '[]', true);
                                                if (!empty($attachments)): 
                                                ?>
                                                <span>📎 <?php echo count($attachments); ?> ไฟล์</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $category_names = array(
                                        'general' => ['name' => 'ทั่วไป', 'color' => 'bg-gray-100 text-gray-800', 'icon' => '📄'],
                                        'announcement' => ['name' => 'ประชาสัมพันธ์', 'color' => 'bg-blue-100 text-blue-800', 'icon' => '📢'],
                                        'jobs' => ['name' => 'สมัครงาน', 'color' => 'bg-green-100 text-green-800', 'icon' => '💼'],
                                        'procurement' => ['name' => 'จัดซื้อจัดจ้าง', 'color' => 'bg-orange-100 text-orange-800', 'icon' => '🛒'],
                                        'accounting' => ['name' => 'การบัญชี', 'color' => 'bg-purple-100 text-purple-800', 'icon' => '💰'],
                                        'health_tips' => ['name' => 'สุขภาพ', 'color' => 'bg-pink-100 text-pink-800', 'icon' => '🏥']
                                    );
                                    $cat_info = $category_names[$news['category']] ?? ['name' => $news['category'], 'color' => 'bg-gray-100 text-gray-800', 'icon' => '📄'];
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $cat_info['color']; ?>">
                                        <?php echo $cat_info['icon']; ?> <?php echo $cat_info['name']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_info = array(
                                        'published' => ['name' => 'เผยแพร่', 'color' => 'bg-green-100 text-green-800', 'indicator' => 'status-published'],
                                        'draft' => ['name' => 'ฉบับร่าง', 'color' => 'bg-yellow-100 text-yellow-800', 'indicator' => 'status-draft'],
                                        'archived' => ['name' => 'เก็บถาวร', 'color' => 'bg-gray-100 text-gray-800', 'indicator' => 'status-archived']
                                    );
                                    $stat_info = $status_info[$news['status']] ?? ['name' => $news['status'], 'color' => 'bg-gray-100 text-gray-800', 'indicator' => 'status-draft'];
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $stat_info['color']; ?>">
                                        <span class="status-indicator <?php echo $stat_info['indicator']; ?>"></span>
                                        <?php echo $stat_info['name']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-xs font-semibold text-gray-600">
                                                <?php echo mb_substr($news['first_name'], 0, 1) . mb_substr($news['last_name'], 0, 1); ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($news['first_name'] . ' ' . $news['last_name']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="space-y-1">
                                        <div class="font-medium"><?php echo formatThaiDate($news['publish_date'] ?: $news['created_at']); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo formatThaiDateTime($news['created_at']); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex space-x-2">
                                        <button onclick="editNews(<?php echo htmlspecialchars(json_encode($news)); ?>)" 
                                                class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-3 py-1 rounded-lg transition duration-200 text-xs font-medium">
                                            ✏️ แก้ไข
                                        </button>
                                        <a href="../news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                           target="_blank" 
                                           class="bg-green-100 text-green-600 hover:bg-green-200 px-3 py-1 rounded-lg transition duration-200 text-xs font-medium">
                                            👁️ ดู
                                        </a>
                                        <button onclick="deleteNews(<?php echo $news['id']; ?>, '<?php echo htmlspecialchars($news['title'], ENT_QUOTES); ?>')" 
                                                class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg transition duration-200 text-xs font-medium">
                                            🗑️ ลบ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ← ก่อนหน้า
                        </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ถัดไป →
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                แสดง <span class="font-medium"><?php echo number_format($offset + 1); ?></span> ถึง 
                                <span class="font-medium"><?php echo number_format(min($offset + $per_page, $total_news)); ?></span> จาก 
                                <span class="font-medium"><?php echo number_format($total_news); ?></span> รายการ
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                                   class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    ←
                                </a>
                                <?php endif; ?>
                                
                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                                   class="relative inline-flex items-center px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    →
                                </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Enhanced Add/Edit News Modal -->
    <div id="newsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-screen overflow-y-auto fade-in">
                <form method="POST" id="newsForm" enctype="multipart/form-data">
                    <!-- Modal Header -->
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900" id="modalTitle">เพิ่มข่าวสารใหม่</h3>
                                <p class="text-gray-600 mt-1">กรอกรายละเอียดข่าวสารและอัพโหลดไฟล์ที่เกี่ยวข้อง</p>
                            </div>
                            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition duration-200">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <input type="hidden" name="action" id="modalAction" value="add">
                        <input type="hidden" name="id" id="modalId">
                        
                        <!-- Title and Category Row -->
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <label for="modalNewsTitle" class="block text-sm font-medium text-gray-700 mb-2">
                                    📰 หัวข้อข่าว <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" id="modalNewsTitle" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="กรอกหัวข้อข่าวที่น่าสนใจ">
                                <div class="mt-1 text-xs text-gray-500">หัวข้อควรมีความยาวไม่เกิน 200 ตัวอักษร</div>
                            </div>
                            
                            <div>
                                <label for="modalCategory" class="block text-sm font-medium text-gray-700 mb-2">
                                    📁 หมวดหมู่
                                </label>
                                <select name="category" id="modalCategory" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="announcement">📢 ประชาสัมพันธ์</option>
                                    <option value="jobs">💼 สมัครงาน</option>
                                    <option value="procurement">🛒 จัดซื้อจัดจ้าง</option>
                                    <option value="accounting">💰 การบัญชี</option>
                                    <option value="general">📄 ทั่วไป</option>
                                    <option value="health_tips">🏥 สุขภาพ</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Excerpt -->
                        <div>
                            <label for="modalExcerpt" class="block text-sm font-medium text-gray-700 mb-2">
                                📝 สรุปย่อ
                            </label>
                            <textarea name="excerpt" id="modalExcerpt" rows="3" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="สรุปข่าวสารสั้นๆ สำหรับแสดงในรายการ (แนะนำ 150-250 ตัวอักษร)"></textarea>
                        </div>
                        
                        <!-- Content -->
                        <div>
                            <label for="modalContent" class="block text-sm font-medium text-gray-700 mb-2">
                                📄 เนื้อหา <span class="text-red-500">*</span>
                            </label>
                            <textarea name="content" id="modalContent" rows="12" required 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="เนื้อหาข่าวสารฉบับเต็ม สามารถใช้การขึ้นบรรทัดใหม่ได้"></textarea>
                        </div>
                        
                        <!-- Featured Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                🖼️ รูปภาพหลัก
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition duration-200" id="imageDropZone">
                                <input type="file" name="featured_image" id="featuredImageInput" accept="image/*" class="hidden">
                                <div id="imagePreview" class="hidden">
                                    <img id="previewImg" class="max-w-full h-48 object-cover rounded-lg mx-auto mb-4">
                                    <button type="button" onclick="removeImage()" class="text-red-600 hover:text-red-800 text-sm">🗑️ ลบรูปภาพ</button>
                                </div>
                                <div id="imageUploadText">
                                    <div class="text-4xl mb-2">📷</div>
                                    <p class="text-gray-600">คลิกเพื่อเลือกรูปภาพหรือลากไฟล์มาวาง</p>
                                    <p class="text-xs text-gray-500 mt-1">รองรับ JPG, PNG, GIF, WebP ขนาดไม่เกิน 10MB</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- File Attachments -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                📎 ไฟล์แนบ
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition duration-200" id="fileDropZone">
                                <input type="file" name="attachments[]" id="attachmentsInput" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                                <div class="text-4xl mb-2">📁</div>
                                <p class="text-gray-600">คลิกเพื่อเลือกไฟล์หรือลากไฟล์มาวาง</p>
                                <p class="text-xs text-gray-500 mt-1">รองรับ PDF, Word, รูปภาพ ขนาดไม่เกิน 10MB ต่อไฟล์</p>
                            </div>
                            <div id="attachmentsList" class="mt-4 space-y-2"></div>
                            <div id="existingAttachments" class="mt-4 space-y-2"></div>
                        </div>
                        
                        <!-- Status and Options -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="modalStatus" class="block text-sm font-medium text-gray-700 mb-2">
                                    📊 สถานะ
                                </label>
                                <select name="status" id="modalStatus" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="draft">📝 ฉบับร่าง</option>
                                    <option value="published">✅ เผยแพร่</option>
                                    <option value="archived">📦 เก็บถาวร</option>
                                </select>
                            </div>
                            
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    ⚙️ ตัวเลือกพิเศษ
                                </label>
                                <div class="space-y-3">
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                        <input type="checkbox" name="is_featured" id="modalIsFeatured" value="1" 
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="modalIsFeatured" class="ml-3 text-sm text-gray-700">
                                            <span class="font-medium">⭐ ข่าวเด่น</span>
                                            <div class="text-xs text-gray-500">แสดงในหน้าแรกและไฮไลท์พิเศษ</div>
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                        <input type="checkbox" name="is_urgent" id="modalIsUrgent" value="1" 
                                               class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                        <label for="modalIsUrgent" class="ml-3 text-sm text-gray-700">
                                            <span class="font-medium">🚨 ข่าวด่วน</span>
                                            <div class="text-xs text-gray-500">แสดงเป็นพิเศษและแจ้งเตือน</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tags -->
                        <div>
                            <label for="modalTags" class="block text-sm font-medium text-gray-700 mb-2">
                                🏷️ แท็ก
                            </label>
                            <input type="text" name="tags" id="modalTags" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="แยกด้วยเครื่องหมายจุลภาค เช่น สุขภาพ, การรักษา, ข้อมูล">
                            <div class="mt-1 text-xs text-gray-500">แท็กช่วยในการค้นหาและจัดหมวดหมู่ข่าว</div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between rounded-b-2xl">
                        <div class="text-sm text-gray-500">
                            <span class="font-medium">💡 เคล็ดลับ:</span> ใช้ Ctrl+S เพื่อบันทึกแบบร่าง
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeModal()" 
                                    class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                                ❌ ยกเลิก
                            </button>
                            <button type="button" onclick="saveDraft()" 
                                    class="px-6 py-2 text-sm font-medium text-gray-700 bg-yellow-100 border border-yellow-300 rounded-lg hover:bg-yellow-200 transition duration-200">
                                📝 บันทึกร่าง
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-200 shadow-lg">
                                💾 บันทึก
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script>
        // Modal Functions
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'เพิ่มข่าวสารใหม่';
            document.getElementById('modalAction').value = 'add';
            document.getElementById('modalId').value = '';
            document.getElementById('newsForm').reset();
            clearFileInputs();
            document.getElementById('newsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function editNews(news) {
            document.getElementById('modalTitle').textContent = 'แก้ไขข่าวสาร';
            document.getElementById('modalAction').value = 'edit';
            document.getElementById('modalId').value = news.id;
            document.getElementById('modalNewsTitle').value = news.title;
            document.getElementById('modalExcerpt').value = news.excerpt || '';
            document.getElementById('modalContent').value = news.content;
            document.getElementById('modalCategory').value = news.category;
            document.getElementById('modalStatus').value = news.status;
            document.getElementById('modalTags').value = news.tags || '';
            document.getElementById('modalIsFeatured').checked = news.is_featured == 1;
            document.getElementById('modalIsUrgent').checked = news.is_urgent == 1;
            
            // Show featured image if exists
            if (news.featured_image) {
                showImagePreview('../' + news.featured_image);
            }
            
            // Show existing attachments
            showExistingAttachments(news.attachments);
            
            document.getElementById('newsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('newsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            clearFileInputs();
        }

        function deleteNews(id, title) {
            if (confirm(`🗑️ ต้องการลบข่าวสาร\n\n"${title}"\n\nการดำเนินการนี้ไม่สามารถยกเลิกได้ และจะลบไฟล์ที่แนบมาด้วยทั้งหมด`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function saveDraft() {
            document.getElementById('modalStatus').value = 'draft';
            document.getElementById('newsForm').submit();
        }

        // File Upload Functions
        function clearFileInputs() {
            document.getElementById('featuredImageInput').value = '';
            document.getElementById('attachmentsInput').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('imageUploadText').classList.remove('hidden');
            document.getElementById('attachmentsList').innerHTML = '';
            document.getElementById('existingAttachments').innerHTML = '';
        }

        function showImagePreview(src) {
            document.getElementById('previewImg').src = src;
            document.getElementById('imagePreview').classList.remove('hidden');
            document.getElementById('imageUploadText').classList.add('hidden');
        }

        function removeImage() {
            document.getElementById('featuredImageInput').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('imageUploadText').classList.remove('hidden');
        }

        function showExistingAttachments(attachmentsJson) {
            const container = document.getElementById('existingAttachments');
            container.innerHTML = '';
            
            if (attachmentsJson) {
                try {
                    const attachments = JSON.parse(attachmentsJson);
                    attachments.forEach((attachment, index) => {
                        const div = document.createElement('div');
                        div.className = 'attachment-item flex items-center justify-between bg-blue-50';
                        div.innerHTML = `
                            <div class="flex items-center space-x-3">
                                <div class="text-2xl">📎</div>
                                <div>
                                    <div class="font-medium text-sm">${attachment.name}</div>
                                    <div class="text-xs text-gray-500">ไฟล์ที่มีอยู่</div>
                                </div>
                            </div>
                            <button type="button" onclick="deleteAttachment(${document.getElementById('modalId').value}, ${index})" 
                                    class="text-red-600 hover:text-red-800 text-sm px-2 py-1 rounded">
                                🗑️ ลบ
                            </button>
                        `;
                        container.appendChild(div);
                    });
                } catch (e) {
                    console.error('Error parsing attachments:', e);
                }
            }
        }

        function deleteAttachment(newsId, attachmentIndex) {
            if (confirm('ต้องการลบไฟล์แนบนี้หรือไม่?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_attachment">
                    <input type="hidden" name="news_id" value="${newsId}">
                    <input type="hidden" name="attachment_index" value="${attachmentIndex}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // File Input Event Listeners
        document.getElementById('featuredImageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    showImagePreview(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('attachmentsInput').addEventListener('change', function(e) {
            const container = document.getElementById('attachmentsList');
            container.innerHTML = '';
            
            Array.from(e.target.files).forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'attachment-item flex items-center justify-between bg-green-50';
                
                const fileIcon = getFileIcon(file.type);
                div.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="text-2xl">${fileIcon}</div>
                        <div>
                            <div class="font-medium text-sm">${file.name}</div>
                            <div class="text-xs text-gray-500">${formatFileSize(file.size)}</div>
                        </div>
                    </div>
                    <button type="button" onclick="removeAttachment(${index})" 
                            class="text-red-600 hover:text-red-800 text-sm px-2 py-1 rounded">
                        ❌ ลบ
                    </button>
                `;
                container.appendChild(div);
            });
        });

        function getFileIcon(mimeType) {
            if (mimeType.startsWith('image/')) return '🖼️';
            if (mimeType.includes('pdf')) return '📄';
            if (mimeType.includes('word') || mimeType.includes('document')) return '📝';
            return '📎';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function removeAttachment(index) {
            const input = document.getElementById('attachmentsInput');
            const dt = new DataTransfer();
            
            Array.from(input.files).forEach((file, i) => {
                if (i !== index) dt.items.add(file);
            });
            
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        }

        // Drag & Drop for Image
        const imageDropZone = document.getElementById('imageDropZone');
        
        imageDropZone.addEventListener('click', () => {
            document.getElementById('featuredImageInput').click();
        });

        imageDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageDropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        imageDropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            imageDropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        imageDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            imageDropZone.classList.remove('border-blue-500', 'bg-blue-50');
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                document.getElementById('featuredImageInput').files = files;
                document.getElementById('featuredImageInput').dispatchEvent(new Event('change'));
            }
        });

        // Drag & Drop for Files
        const fileDropZone = document.getElementById('fileDropZone');
        
        fileDropZone.addEventListener('click', () => {
            document.getElementById('attachmentsInput').click();
        });

        fileDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileDropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        fileDropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            fileDropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        fileDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            fileDropZone.classList.remove('border-blue-500', 'bg-blue-50');
            
            document.getElementById('attachmentsInput').files = e.dataTransfer.files;
            document.getElementById('attachmentsInput').dispatchEvent(new Event('change'));
        });

        // Close modal when clicking outside
        document.getElementById('newsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's' && !document.getElementById('newsModal').classList.contains('hidden')) {
                e.preventDefault();
                saveDraft();
            }
            if (e.key === 'Escape' && !document.getElementById('newsModal').classList.contains('hidden')) {
                closeModal();
            }
        });

        // Form validation
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            const title = document.getElementById('modalNewsTitle').value.trim();
            const content = document.getElementById('modalContent').value.trim();
            
            if (!title || !content) {
                e.preventDefault();
                alert('กรุณากรอกหัวข้อและเนื้อหา');
                return;
            }
            
            if (title.length > 200) {
                e.preventDefault();
                alert('หัวข้อยาวเกินไป (สูงสุด 200 ตัวอักษร)');
                return;
            }
        });

        // Character counter for title
        document.getElementById('modalNewsTitle').addEventListener('input', function() {
            const maxLength = 200;
            const currentLength = this.value.length;
            const percentage = (currentLength / maxLength) * 100;
            
            if (percentage > 80) {
                this.style.borderColor = percentage > 100 ? '#ef4444' : '#f59e0b';
            } else {
                this.style.borderColor = '#d1d5db';
            }
        });
    </script>
    
</body>
</html>