<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin role
requireAdmin('../login.php');

$page_title = "จัดการข่าวสาร";

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
                
                $publish_date = null;
                if ($status === 'published') {
                    $publish_date = date('Y-m-d H:i:s');
                }
                
                $stmt = $conn->prepare("
                    INSERT INTO news (title, slug, content, excerpt, category, status, 
                                    is_featured, is_urgent, tags, author_id, publish_date, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute(array(
                    $title, $slug, $content, $excerpt, $category, $status,
                    $is_featured, $is_urgent, $tags, $_SESSION['user_id'], $publish_date
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
                    
                    $stmt = $conn->prepare("
                        UPDATE news 
                        SET title = ?, content = ?, excerpt = ?, category = ?, status = ?, 
                            is_featured = ?, is_urgent = ?, tags = ?, publish_date = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    
                    if ($stmt->execute(array(
                        $title, $content, $excerpt, $category, $status,
                        $is_featured, $is_urgent, $tags, $publish_date, $id
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
            $stmt = $conn->prepare("SELECT title FROM news WHERE id = ?");
            $stmt->execute(array($id));
            $news_info = $stmt->fetch();
            
            if ($news_info) {
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
        SELECT n.*, u.first_name, u.last_name
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
    // Convert Thai text and special characters
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
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">THC</span>
                    </div>
                    <h1 class="text-xl font-bold">ระบบจัดการโรงพยาบาลทุ่งหัวช้าง</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span>สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition duration-300">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-6">
                <div class="space-y-2">
                    <a href="dashboard.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📊 แดชบอร์ด
                    </a>
                    <a href="appointments.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📅 จัดการนัดหมาย
                    </a>
                    <a href="patients.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👥 ข้อมูลผู้ป่วย
                    </a>
                    <a href="doctors.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👨‍⚕️ จัดการแพทย์
                    </a>
                    <a href="departments.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        🏥 จัดการแผนก
                    </a>
                    <a href="news.php" class="block py-2 px-4 text-blue-600 bg-blue-50 rounded font-medium">
                        📰 จัดการข่าวสาร
                    </a>
                    <a href="users.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👨‍💼 จัดการผู้ใช้
                    </a>
                    <a href="reports.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📊 รายงาน
                    </a>
                    <a href="settings.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        ⚙️ ตั้งค่าระบบ
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                ✅ <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                ❌ <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800">จัดการข่าวสาร</h2>
                <p class="text-gray-600">จัดการข่าวสารและประกาศของโรงพยาบาล</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?></div>
                    <div class="text-sm text-gray-600">ทั้งหมด</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-green-600"><?php echo number_format($stats['published']); ?></div>
                    <div class="text-sm text-gray-600">เผยแพร่แล้ว</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['draft']); ?></div>
                    <div class="text-sm text-gray-600">ฉบับร่าง</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['featured']); ?></div>
                    <div class="text-sm text-gray-600">ข่าวเด่น</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-red-600"><?php echo number_format($stats['urgent']); ?></div>
                    <div class="text-sm text-gray-600">ข่าวด่วน</div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <button onclick="openAddModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300 mb-4 md:mb-0">
                    ➕ เพิ่มข่าวสารใหม่
                </button>
                
                <!-- Search and Filter -->
                <form method="GET" class="flex flex-col md:flex-row gap-2">
                    <input type="text" name="search" placeholder="ค้นหาข่าวสาร..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">ทุกสถานะ</option>
                        <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>เผยแพร่แล้ว</option>
                        <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>ฉบับร่าง</option>
                        <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>เก็บถาวร</option>
                    </select>
                    
                    <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">ทุกหมวดหมู่</option>
                        <option value="general" <?php echo $category_filter === 'general' ? 'selected' : ''; ?>>ทั่วไป</option>
                        <option value="announcement" <?php echo $category_filter === 'announcement' ? 'selected' : ''; ?>>ประกาศ</option>
                        <option value="procurement" <?php echo $category_filter === 'procurement' ? 'selected' : ''; ?>>จัดซื้อจัดจ้าง</option>
                        <option value="service" <?php echo $category_filter === 'service' ? 'selected' : ''; ?>>บริการ</option>
                        <option value="health_tips" <?php echo $category_filter === 'health_tips' ? 'selected' : ''; ?>>สุขภาพ</option>
                    </select>
                    
                    <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        🔍 ค้นหา
                    </button>
                </form>
            </div>

            <!-- News Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หัวข้อ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมวดหมู่</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้เขียน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($news_list)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-4xl mb-2">📰</div>
                                    ไม่พบข้อมูลข่าวสาร
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($news_list as $news): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1">
                                            <h3 class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($news['title']); ?>
                                                <?php if ($news['is_featured']): ?>
                                                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">เด่น</span>
                                                <?php endif; ?>
                                                <?php if ($news['is_urgent']): ?>
                                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded">ด่วน</span>
                                                <?php endif; ?>
                                            </h3>
                                            <?php if ($news['excerpt']): ?>
                                            <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars(mb_substr($news['excerpt'], 0, 100)); ?>...</p>
                                            <?php endif; ?>
                                            <div class="text-xs text-gray-400 mt-1">
                                                👁️ <?php echo number_format($news['views']); ?> ครั้ง
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $category_names = array(
                                        'general' => 'ทั่วไป',
                                        'announcement' => 'ประกาศ',
                                        'procurement' => 'จัดซื้อจัดจ้าง',
                                        'service' => 'บริการ',
                                        'health_tips' => 'สุขภาพ'
                                    );
                                    $category_colors = array(
                                        'general' => 'bg-gray-100 text-gray-800',
                                        'announcement' => 'bg-blue-100 text-blue-800',
                                        'procurement' => 'bg-green-100 text-green-800',
                                        'service' => 'bg-purple-100 text-purple-800',
                                        'health_tips' => 'bg-pink-100 text-pink-800'
                                    );
                                    $category_name = $category_names[$news['category']] ?? $news['category'];
                                    $category_color = $category_colors[$news['category']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $category_color; ?>">
                                        <?php echo $category_name; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_colors = array(
                                        'published' => 'bg-green-100 text-green-800',
                                        'draft' => 'bg-yellow-100 text-yellow-800',
                                        'archived' => 'bg-gray-100 text-gray-800'
                                    );
                                    $status_names = array(
                                        'published' => 'เผยแพร่',
                                        'draft' => 'ฉบับร่าง',
                                        'archived' => 'เก็บถาวร'
                                    );
                                    $status_color = $status_colors[$news['status']] ?? 'bg-gray-100 text-gray-800';
                                    $status_name = $status_names[$news['status']] ?? $news['status'];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_color; ?>">
                                        <?php echo $status_name; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($news['first_name'] . ' ' . $news['last_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div><?php echo formatThaiDate($news['publish_date'] ?: $news['created_at']); ?></div>
                                    <div class="text-xs"><?php echo formatThaiDateTime($news['created_at']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editNews(<?php echo htmlspecialchars(json_encode($news)); ?>)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            แก้ไข
                                        </button>
                                        <a href="../news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                           target="_blank" class="text-green-600 hover:text-green-900">
                                            ดู
                                        </a>
                                        <button onclick="deleteNews(<?php echo $news['id']; ?>, '<?php echo htmlspecialchars($news['title'], ENT_QUOTES); ?>')" 
                                                class="text-red-600 hover:text-red-900">
                                            ลบ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ก่อนหน้า
                        </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ถัดไป
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                แสดง <span class="font-medium"><?php echo $offset + 1; ?></span> ถึง 
                                <span class="font-medium"><?php echo min($offset + $per_page, $total_news); ?></span> จาก 
                                <span class="font-medium"><?php echo number_format($total_news); ?></span> รายการ
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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

    <!-- Add/Edit News Modal -->
    <div id="newsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-screen overflow-y-auto">
                <form method="POST" id="newsForm">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900" id="modalTitle">เพิ่มข่าวสารใหม่</h3>
                        <button type="button" onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                            <span class="sr-only">ปิด</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <input type="hidden" name="action" id="modalAction" value="add">
                        <input type="hidden" name="id" id="modalId">
                        
                        <!-- Title -->
                        <div>
                            <label for="modalNewsTitle" class="block text-sm font-medium text-gray-700 mb-2">หัวข้อข่าว *</label>
                            <input type="text" name="title" id="modalNewsTitle" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <!-- Excerpt -->
                        <div>
                            <label for="modalExcerpt" class="block text-sm font-medium text-gray-700 mb-2">สรุปย่อ</label>
                            <textarea name="excerpt" id="modalExcerpt" rows="2" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="สรุปข่าวสารสั้นๆ สำหรับแสดงในรายการ"></textarea>
                        </div>
                        
                        <!-- Content -->
                        <div>
                            <label for="modalContent" class="block text-sm font-medium text-gray-700 mb-2">เนื้อหา *</label>
                            <textarea name="content" id="modalContent" rows="8" required 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="เนื้อหาข่าวสารฉบับเต็ม"></textarea>
                        </div>
                        
                        <!-- Category and Status -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="modalCategory" class="block text-sm font-medium text-gray-700 mb-2">หมวดหมู่</label>
                                <select name="category" id="modalCategory" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="general">ทั่วไป</option>
                                    <option value="announcement">ประกาศ</option>
                                    <option value="procurement">จัดซื้อจัดจ้าง</option>
                                    <option value="service">บริการ</option>
                                    <option value="health_tips">สุขภาพ</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="modalStatus" class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                                <select name="status" id="modalStatus" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="draft">ฉบับร่าง</option>
                                    <option value="published">เผยแพร่</option>
                                    <option value="archived">เก็บถาวร</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Tags -->
                        <div>
                            <label for="modalTags" class="block text-sm font-medium text-gray-700 mb-2">แท็ก</label>
                            <input type="text" name="tags" id="modalTags" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="แยกด้วยเครื่องหมายจุลภาค เช่น สุขภาพ, การรักษา, ข้อมูล">
                        </div>
                        
                        <!-- Special Options -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_featured" id="modalIsFeatured" value="1" 
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="modalIsFeatured" class="ml-2 text-sm text-gray-700">
                                    ข่าวเด่น (แสดงในหน้าแรก)
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" name="is_urgent" id="modalIsUrgent" value="1" 
                                       class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="modalIsUrgent" class="ml-2 text-sm text-gray-700">
                                    ข่าวด่วน (แสดงเป็นพิเศษ)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 text-right space-x-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            ยกเลิก
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'เพิ่มข่าวสารใหม่';
            document.getElementById('modalAction').value = 'add';
            document.getElementById('modalId').value = '';
            document.getElementById('newsForm').reset();
            document.getElementById('newsModal').classList.remove('hidden');
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
            document.getElementById('newsModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('newsModal').classList.add('hidden');
        }

        function deleteNews(id, title) {
            if (confirm(`ต้องการลบข่าวสาร "${title}" หรือไม่?\n\nการดำเนินการนี้ไม่สามารถยกเลิกได้`)) {
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

        // Close modal when clicking outside
        document.getElementById('newsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Auto-save draft functionality (optional)
        let autoSaveTimer;
        function autoSaveDraft() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                // Implement auto-save logic here if needed
                console.log('Auto-saving draft...');
            }, 30000); // Auto-save every 30 seconds
        }

        // Character counter for title and excerpt
        document.getElementById('modalNewsTitle').addEventListener('input', function() {
            const maxLength = 200;
            const currentLength = this.value.length;
            if (currentLength > maxLength * 0.8) {
                this.style.borderColor = currentLength > maxLength ? '#ef4444' : '#f59e0b';
            } else {
                this.style.borderColor = '#d1d5db';
            }
        });
    </script>
</body>
</html>