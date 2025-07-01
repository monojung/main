<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Require admin role
requireAdmin('../login.php');

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'news';
$page_title = "จัดการข่าวสาร";

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$error = '';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_POST && $action) {
    try {
        switch ($action) {
            case 'add':
                $title = sanitizeInput($_POST['title'] ?? '');
                $content = $_POST['content'] ?? '';
                $summary = sanitizeInput($_POST['summary'] ?? '');
                $status = sanitizeInput($_POST['status'] ?? 'draft');
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
                $publish_date = $_POST['publish_date'] ?? null;
                
                if (empty($title) || empty($content)) {
                    $error = "กรุณากรอกหัวข้อและเนื้อหา";
                } else {
                    $slug = generateSlug($title);
                    
                    // Handle file upload
                    $featured_image = null;
                    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                        try {
                            $featured_image = uploadFile($_FILES['featured_image'], '../uploads/news/', ['jpg', 'jpeg', 'png', 'gif']);
                        } catch (Exception $e) {
                            $error = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ: " . $e->getMessage();
                        }
                    }
                    
                    if (!$error) {
                        $stmt = $conn->prepare("
                            INSERT INTO news (title, slug, content, summary, featured_image, status, is_featured, is_urgent, publish_date, author_id, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        if ($stmt->execute([$title, $slug, $content, $summary, $featured_image, $status, $is_featured, $is_urgent, $publish_date, $_SESSION['user_id']])) {
                            $news_id = $conn->lastInsertId();
                            logActivity($conn, $_SESSION['user_id'], 'news_created', 'news', $news_id);
                            $message = "เพิ่มข่าวสารเรียบร้อยแล้ว";
                            $action = ''; // Reset action to show list
                        } else {
                            $error = "ไม่สามารถเพิ่มข่าวสารได้";
                        }
                    }
                }
                break;
                
            case 'edit':
                $news_id = (int)($_POST['news_id'] ?? 0);
                $title = sanitizeInput($_POST['title'] ?? '');
                $content = $_POST['content'] ?? '';
                $summary = sanitizeInput($_POST['summary'] ?? '');
                $status = sanitizeInput($_POST['status'] ?? 'draft');
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
                $publish_date = $_POST['publish_date'] ?? null;
                
                if (!$news_id || empty($title) || empty($content)) {
                    $error = "ข้อมูลไม่ถูกต้อง";
                } else {
                    // Get old data for logging
                    $old_news = getRecord($conn, 'news', $news_id);
                    
                    // Handle file upload
                    $featured_image = $old_news['featured_image'] ?? null;
                    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                        try {
                            // Delete old image
                            if ($featured_image) {
                                deleteFile($featured_image, '../uploads/news/');
                            }
                            $featured_image = uploadFile($_FILES['featured_image'], '../uploads/news/', ['jpg', 'jpeg', 'png', 'gif']);
                        } catch (Exception $e) {
                            $error = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ: " . $e->getMessage();
                        }
                    }
                    
                    if (!$error) {
                        $stmt = $conn->prepare("
                            UPDATE news 
                            SET title = ?, content = ?, summary = ?, featured_image = ?, status = ?, is_featured = ?, is_urgent = ?, publish_date = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        
                        if ($stmt->execute([$title, $content, $summary, $featured_image, $status, $is_featured, $is_urgent, $publish_date, $news_id])) {
                            logActivity($conn, $_SESSION['user_id'], 'news_updated', 'news', $news_id, $old_news);
                            $message = "แก้ไขข่าวสารเรียบร้อยแล้ว";
                            $action = ''; // Reset action to show list
                        } else {
                            $error = "ไม่สามารถแก้ไขข่าวสารได้";
                        }
                    }
                }
                break;
                
            case 'delete':
                $news_id = (int)($_POST['news_id'] ?? 0);
                if ($news_id) {
                    $old_news = getRecord($conn, 'news', $news_id);
                    
                    // Delete featured image
                    if ($old_news && $old_news['featured_image']) {
                        deleteFile($old_news['featured_image'], '../uploads/news/');
                    }
                    
                    if (deleteRecord($conn, 'news', $news_id)) {
                        logActivity($conn, $_SESSION['user_id'], 'news_deleted', 'news', $news_id, $old_news);
                        $message = "ลบข่าวสารเรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถลบข่าวสารได้";
                    }
                }
                break;
                
            case 'toggle_status':
                $news_id = (int)($_POST['news_id'] ?? 0);
                $new_status = sanitizeInput($_POST['new_status'] ?? '');
                
                if ($news_id && in_array($new_status, ['published', 'draft', 'archived'])) {
                    $stmt = $conn->prepare("UPDATE news SET status = ?, updated_at = NOW() WHERE id = ?");
                    if ($stmt->execute([$new_status, $news_id])) {
                        logActivity($conn, $_SESSION['user_id'], 'news_status_changed', 'news', $news_id);
                        $message = "เปลี่ยนสถานะข่าวสารเรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถเปลี่ยนสถานะได้";
                    }
                }
                break;
        }
    } catch (Exception $e) {
        logError($e->getMessage(), __FILE__, __LINE__);
        $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
    }
}

// Get news for editing
$edit_news = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_news = getRecord($conn, 'news', (int)$_GET['id']);
    if (!$edit_news) {
        $error = "ไม่พบข่าวสารที่ต้องการแก้ไข";
        $action = '';
    }
}

// Pagination and filtering for list view
if (empty($action)) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    
    // Filter options
    $status_filter = $_GET['status'] ?? '';
    $search = sanitizeInput($_GET['search'] ?? '');
    $author_filter = $_GET['author'] ?? '';
    
    // Build query
    $where_conditions = ["1=1"];
    $params = [];
    
    if ($status_filter) {
        $where_conditions[] = "n.status = ?";
        $params[] = $status_filter;
    }
    
    if ($search) {
        $where_conditions[] = "(n.title LIKE ? OR n.content LIKE ? OR n.summary LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if ($author_filter) {
        $where_conditions[] = "n.author_id = ?";
        $params[] = $author_filter;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_query = "
        SELECT COUNT(*) 
        FROM news n 
        LEFT JOIN users u ON n.author_id = u.id 
        WHERE $where_clause
    ";
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetchColumn();
    
    // Get records
    $query = "
        SELECT n.*, u.first_name, u.last_name 
        FROM news n 
        LEFT JOIN users u ON n.author_id = u.id 
        WHERE $where_clause
        ORDER BY n.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $news_list = $stmt->fetchAll();
    
    $pagination = getPagination($total_records, $per_page, $page);
    
    // Get authors for filter
    $authors_stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.first_name, u.last_name 
        FROM users u 
        INNER JOIN news n ON u.id = n.author_id 
        ORDER BY u.first_name, u.last_name
    ");
    $authors_stmt->execute();
    $authors = $authors_stmt->fetchAll();
}

// Get statistics
$stats = [
    'total' => getTotalNews($conn),
    'published' => 0,
    'draft' => 0,
    'featured' => 0
];

try {
    foreach (['published', 'draft'] as $status) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM news WHERE status = ?");
        $stmt->execute([$status]);
        $stats[$status] = $stmt->fetchColumn() ?? 0;
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM news WHERE is_featured = 1 AND status = 'published'");
    $stmt->execute();
    $stats['featured'] = $stmt->fetchColumn() ?? 0;
} catch (Exception $e) {
    // Keep default values
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
        
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
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
    <nav class="bg-gradient-to-r from-green-600 to-teal-700 text-white shadow-2xl sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                        <span class="text-white font-bold text-xl">📰</span>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold">จัดการข่าวสาร</h1>
                        <p class="text-green-200 text-sm">ระบบจัดการข่าวสารและประกาศ</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-green-200"><?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl transition duration-300 shadow-lg">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex min-h-screen">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 lg:p-8 overflow-x-hidden">
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 px-4 py-3 rounded-lg fade-in">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span><?php echo $message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg fade-in">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($action)): ?>
            <!-- List View -->
            <!-- Page Header -->
            <div class="mb-8 fade-in">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">📰 จัดการข่าวสาร</h2>
                        <p class="text-gray-200">เพิ่ม แก้ไข และจัดการข่าวสารของโรงพยาบาล</p>
                    </div>
                    <a href="?action=add" class="bg-white text-green-600 hover:bg-green-50 px-6 py-3 rounded-xl transition duration-300 shadow-lg font-medium flex items-center space-x-2">
                        <span class="text-lg">➕</span>
                        <span>เพิ่มข่าวสารใหม่</span>
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?></div>
                        <div class="text-sm text-gray-600">ทั้งหมด</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-green-600"><?php echo number_format($stats['published']); ?></div>
                        <div class="text-sm text-gray-600">เผยแพร่</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-yellow-600"><?php echo number_format($stats['draft']); ?></div>
                        <div class="text-sm text-gray-600">ร่าง</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-purple-600"><?php echo number_format($stats['featured']); ?></div>
                        <div class="text-sm text-gray-600">ข่าวเด่น</div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="glass-card rounded-2xl p-6 mb-8 fade-in">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🔍 ค้นหา</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="หัวข้อ, เนื้อหา..." 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📊 สถานะ</label>
                        <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">ทั้งหมด</option>
                            <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>เผยแพร่</option>
                            <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>ร่าง</option>
                            <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>เก็บถาวร</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">✍️ ผู้เขียน</label>
                        <select name="author" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">ทั้งหมด</option>
                            <?php foreach ($authors as $author): ?>
                            <option value="<?php echo $author['id']; ?>" <?php echo $author_filter == $author['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                        <button type="submit" class="w-full bg-green-600 text-white py-3 px-6 rounded-xl hover:bg-green-700 transition duration-300 font-medium">
                            🔍 ค้นหา
                        </button>
                    </div>
                </form>
            </div>

            <!-- News Table -->
            <div class="glass-card rounded-2xl overflow-hidden shadow-xl fade-in">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-teal-50">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">📋 รายการข่าวสาร</h3>
                        <div class="text-sm text-gray-600">
                            แสดง <?php echo number_format(count($news_list ?? [])); ?> จาก <?php echo number_format($total_records ?? 0); ?> รายการ
                        </div>
                    </div>
                </div>

                <?php if (empty($news_list)): ?>
                <div class="p-12 text-center">
                    <div class="text-6xl mb-4">📰</div>
                    <p class="text-gray-500 text-lg font-medium">ไม่มีข่าวสาร</p>
                    <p class="text-gray-400 text-sm mb-6">เริ่มต้นโดยการเพิ่มข่าวสารใหม่</p>
                    <a href="?action=add" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition duration-300 inline-flex items-center space-x-2">
                        <span>➕</span>
                        <span>เพิ่มข่าวสารใหม่</span>
                    </a>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ข่าวสาร</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้เขียน</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($news_list as $news): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-start space-x-4">
                                        <?php if ($news['featured_image']): ?>
                                        <img src="../uploads/news/<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($news['title']); ?>"
                                             class="w-16 h-16 object-cover rounded-lg">
                                        <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-2xl">📰</span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900 mb-1">
                                                <?php echo htmlspecialchars($news['title']); ?>
                                                <?php if ($news['is_featured']): ?>
                                                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">⭐ เด่น</span>
                                                <?php endif; ?>
                                                <?php if ($news['is_urgent']): ?>
                                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">🚨 ด่วน</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo truncateText($news['summary'] ?: strip_tags($news['content']), 100); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars(($news['first_name'] ?? '') . ' ' . ($news['last_name'] ?? '')); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $status_colors = [
                                        'published' => 'bg-green-100 text-green-800',
                                        'draft' => 'bg-yellow-100 text-yellow-800',
                                        'archived' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $status_labels = [
                                        'published' => 'เผยแพร่',
                                        'draft' => 'ร่าง',
                                        'archived' => 'เก็บถาวร'
                                    ];
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $status_colors[$news['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $status_labels[$news['status']] ?? $news['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo safeFormatThaiDateTime($news['created_at']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $news['id']; ?>" 
                                           class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            ✏️ แก้ไข
                                        </a>
                                        <?php if ($news['status'] !== 'published'): ?>
                                        <button onclick="toggleStatus(<?php echo $news['id']; ?>, 'published')"
                                                class="bg-green-100 text-green-600 hover:bg-green-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            📢 เผยแพร่
                                        </button>
                                        <?php else: ?>
                                        <button onclick="toggleStatus(<?php echo $news['id']; ?>, 'draft')"
                                                class="bg-yellow-100 text-yellow-600 hover:bg-yellow-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            📝 ร่าง
                                        </button>
                                        <?php endif; ?>
                                        <button onclick="confirmDelete(<?php echo $news['id']; ?>)"
                                                class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            🗑️ ลบ
                                        </button>
                                        <?php if (!empty($news['slug'])): ?>
                                        <a href="../news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                           target="_blank"
                                           class="bg-gray-100 text-gray-600 hover:bg-gray-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            👁️ ดู
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            แสดงรายการ <?php echo number_format(($page - 1) * $per_page + 1); ?> - <?php echo number_format(min($page * $per_page, $total_records)); ?> 
                            จากทั้งหมด <?php echo number_format($total_records); ?> รายการ
                        </div>
                        <div class="flex space-x-2">
                            <?php if ($pagination['has_prev']): ?>
                            <a href="?page=<?php echo $pagination['prev_page']; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="bg-white border border-gray-300 text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                                ← ก่อนหน้า
                            </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="<?php echo $i === $page ? 'bg-green-600 text-white' : 'bg-white border border-gray-300 text-gray-500 hover:text-gray-700'; ?> px-4 py-2 rounded-lg transition duration-200">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?php echo $pagination['next_page']; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="bg-white border border-gray-300 text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                                ถัดไป →
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <!-- Add/Edit Form -->
            <div class="mb-8 fade-in">
                <div class="flex items-center space-x-4 mb-4">
                    <a href="news.php" class="text-white hover:text-gray-200 transition duration-200">
                        <span class="text-2xl">←</span>
                    </a>
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white">
                            <?php echo $action === 'add' ? '➕ เพิ่มข่าวสารใหม่' : '✏️ แก้ไขข่าวสาร'; ?>
                        </h2>
                        <p class="text-gray-200">
                            <?php echo $action === 'add' ? 'สร้างข่าวสารและประกาศใหม่' : 'แก้ไขข้อมูลข่าวสาร'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8 fade-in">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($edit_news): ?>
                    <input type="hidden" name="news_id" value="<?php echo $edit_news['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Basic Information -->
                    <div class="bg-blue-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">📝 ข้อมูลพื้นฐาน</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หัวข้อข่าว *</label>
                                <input type="text" name="title" required 
                                       value="<?php echo htmlspecialchars($edit_news['title'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="กรอกหัวข้อข่าว">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">สรุปข่าว</label>
                                <textarea name="summary" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                          placeholder="สรุปสั้นๆ ของข่าว"><?php echo htmlspecialchars($edit_news['summary'] ?? ''); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">เนื้อหาข่าว *</label>
                                <textarea name="content" required rows="10" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                          placeholder="เนื้อหาข่าวแบบละเอียด"><?php echo htmlspecialchars($edit_news['content'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Media -->
                    <div class="bg-purple-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">🖼️ รูปภาพประกอบ</h4>
                        <div class="space-y-4">
                            <?php if ($edit_news && $edit_news['featured_image']): ?>
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">รูปภาพปัจจุบัน:</p>
                                <img src="../uploads/news/<?php echo htmlspecialchars($edit_news['featured_image']); ?>" 
                                     alt="Current image" class="w-32 h-32 object-cover rounded-lg">
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php echo $edit_news ? 'อัพโหลดรูปภาพใหม่ (เว้นว่างหากไม่เปลี่ยน)' : 'รูปภาพประกอบ'; ?>
                                </label>
                                <input type="file" name="featured_image" accept="image/*"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <p class="text-sm text-gray-500 mt-2">รองรับไฟล์: JPG, PNG, GIF (ขนาดไม่เกิน 10MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Publishing Options -->
                    <div class="bg-green-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">📊 ตัวเลือกการเผยแพร่</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="draft" <?php echo ($edit_news['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>ร่าง</option>
                                    <option value="published" <?php echo ($edit_news['status'] ?? '') === 'published' ? 'selected' : ''; ?>>เผยแพร่</option>
                                    <option value="archived" <?php echo ($edit_news['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>เก็บถาวร</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">วันที่เผยแพร่</label>
                                <input type="datetime-local" name="publish_date" 
                                       value="<?php echo $edit_news && $edit_news['publish_date'] ? date('Y-m-d\TH:i', strtotime($edit_news['publish_date'])) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_featured" value="1" 
                                       <?php echo ($edit_news['is_featured'] ?? 0) ? 'checked' : ''; ?>
                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">⭐ ข่าวเด่น (แสดงในหน้าแรก)</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" name="is_urgent" value="1" 
                                       <?php echo ($edit_news['is_urgent'] ?? 0) ? 'checked' : ''; ?>
                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">🚨 ข่าวด่วน (แสดงการแจ้งเตือนพิเศษ)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="news.php" 
                           class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-6 py-3 rounded-xl transition duration-300 font-medium">
                            ยกเลิก
                        </a>
                        <button type="submit" 
                                class="bg-green-600 text-white hover:bg-green-700 px-6 py-3 rounded-xl transition duration-300 font-medium flex items-center space-x-2">
                            <span><?php echo $action === 'add' ? '➕' : '💾'; ?></span>
                            <span><?php echo $action === 'add' ? 'เพิ่มข่าวสาร' : 'บันทึกการแก้ไข'; ?></span>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Status toggle function
        function toggleStatus(newsId, newStatus) {
            const statusText = newStatus === 'published' ? 'เผยแพร่' : 'เปลี่ยนเป็นร่าง';
            if (confirm(`คุณต้องการ${statusText}ข่าวนี้หรือไม่?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="news_id" value="${newsId}">
                    <input type="hidden" name="new_status" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Delete confirmation
        function confirmDelete(newsId) {
            if (confirm('คุณต้องการลบข่าวนี้หรือไม่? การดำเนินการนี้ไม่สามารถย้อนกลับได้')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="news_id" value="${newsId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[enctype="multipart/form-data"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const title = document.querySelector('input[name="title"]');
                    const content = document.querySelector('textarea[name="content"]');
                    
                    if (title && content) {
                        if (!title.value.trim() || !content.value.trim()) {
                            e.preventDefault();
                            alert('กรุณากรอกหัวข้อและเนื้อหาข่าว');
                            return false;
                        }
                    }
                    
                    // File size validation
                    const fileInput = document.querySelector('input[name="featured_image"]');
                    if (fileInput && fileInput.files.length > 0) {
                        const file = fileInput.files[0];
                        const maxSize = 10 * 1024 * 1024; // 10MB
                        
                        if (file.size > maxSize) {
                            e.preventDefault();
                            alert('ขนาดไฟล์ใหญ่เกินไป กรุณาเลือกไฟล์ที่มีขนาดไม่เกิน 10MB');
                            return false;
                        }
                        
                        // File type validation
                        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!allowedTypes.includes(file.type)) {
                            e.preventDefault();
                            alert('รูปแบบไฟล์ไม่ถูกต้อง กรุณาเลือกไฟล์ JPG, PNG หรือ GIF');
                            return false;
                        }
                    }
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span>กำลังบันทึก...';
                        submitBtn.disabled = true;
                        
                        // Re-enable after 30 seconds as fallback
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 30000);
                    }
                });
            }
        });

        // Auto-save draft functionality
        let autoSaveTimeout;
        function autoSaveDraft() {
            const title = document.querySelector('input[name="title"]');
            const content = document.querySelector('textarea[name="content"]');
            
            if (title && content && title.value.trim() && content.value.trim()) {
                // Here you could implement auto-save to server
                console.log('Auto-saving draft...');
            }
        }

        // Set up auto-save on input
        document.querySelectorAll('input[name="title"], textarea[name="content"]').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(autoSaveDraft, 5000); // Auto-save after 5 seconds of inactivity
            });
        });

        // Character counter for title
        const titleInput = document.querySelector('input[name="title"]');
        if (titleInput) {
            const maxLength = 200;
            const counter = document.createElement('div');
            counter.className = 'text-sm text-gray-500 mt-1';
            titleInput.parentNode.appendChild(counter);
            
            function updateCounter() {
                const remaining = maxLength - titleInput.value.length;
                counter.textContent = `เหลือ ${remaining} ตัวอักษร`;
                
                if (remaining < 20) {
                    counter.className = 'text-sm text-red-500 mt-1';
                } else if (remaining < 50) {
                    counter.className = 'text-sm text-yellow-500 mt-1';
                } else {
                    counter.className = 'text-sm text-gray-500 mt-1';
                }
            }
            
            titleInput.addEventListener('input', updateCounter);
            titleInput.setAttribute('maxlength', maxLength);
            updateCounter();
        }

        // Image preview
        const imageInput = document.querySelector('input[name="featured_image"]');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Remove existing preview
                        const existingPreview = document.querySelector('.image-preview');
                        if (existingPreview) {
                            existingPreview.remove();
                        }
                        
                        // Create new preview
                        const preview = document.createElement('div');
                        preview.className = 'image-preview mt-4';
                        preview.innerHTML = `
                            <p class="text-sm text-gray-600 mb-2">ตัวอย่างรูปภาพใหม่:</p>
                            <img src="${e.target.result}" alt="Preview" class="w-32 h-32 object-cover rounded-lg">
                        `;
                        imageInput.parentNode.appendChild(preview);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        console.log('📰 News management system loaded successfully!');
    </script>
</body>
</html>