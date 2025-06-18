<?php 
$page_title = "ข่าวสารและประกาศ";
require_once 'config/database.php';

// Get parameters
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get single news if slug is provided
$single_news = null;
if (isset($_GET['slug'])) {
    $slug = sanitizeInput($_GET['slug']);
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($conn) {
            // Get single news article
            $stmt = $conn->prepare("
                SELECT n.*, u.first_name, u.last_name 
                FROM news n
                LEFT JOIN users u ON n.author_id = u.id
                WHERE n.slug = ? AND n.status = 'published'
                AND (n.publish_date IS NULL OR n.publish_date <= NOW())
            ");
            $stmt->execute(array($slug));
            $single_news = $stmt->fetch();
            
            if ($single_news) {
                // Update view count
                $stmt = $conn->prepare("UPDATE news SET views = views + 1 WHERE id = ?");
                $stmt->execute(array($single_news['id']));
                
                $page_title = $single_news['title'] . " - ข่าวสาร";
            }
        }
    } catch (Exception $e) {
        logError($e->getMessage(), __FILE__, __LINE__);
    }
}

// If showing single news, display it
if ($single_news) {
    include 'includes/header.php';
    ?>
    
    <main>
        <!-- Breadcrumb -->
        <section class="bg-gray-100 py-4">
            <div class="container mx-auto px-4">
                <nav class="text-sm">
                    <a href="index.php" class="text-blue-600 hover:text-blue-800">หน้าหลัก</a>
                    <span class="mx-2">></span>
                    <a href="news.php" class="text-blue-600 hover:text-blue-800">ข่าวสาร</a>
                    <span class="mx-2">></span>
                    <span class="text-gray-600"><?php echo htmlspecialchars($single_news['title']); ?></span>
                </nav>
            </div>
        </section>

        <!-- Single News Article -->
        <article class="py-16">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <!-- Article Header -->
                    <header class="mb-8">
                        <div class="flex items-center space-x-4 mb-4">
                            <?php
                            $category_colors = array(
                                'general' => 'bg-gray-100 text-gray-800',
                                'announcement' => 'bg-blue-100 text-blue-800',
                                'procurement' => 'bg-green-100 text-green-800',
                                'service' => 'bg-purple-100 text-purple-800',
                                'health_tips' => 'bg-pink-100 text-pink-800'
                            );
                            $category_names = array(
                                'general' => 'ทั่วไป',
                                'announcement' => 'ประกาศ',
                                'procurement' => 'จัดซื้อจัดจ้าง',
                                'service' => 'บริการ',
                                'health_tips' => 'สุขภาพ'
                            );
                            $category_color = isset($category_colors[$single_news['category']]) ? $category_colors[$single_news['category']] : 'bg-gray-100 text-gray-800';
                            $category_name = isset($category_names[$single_news['category']]) ? $category_names[$single_news['category']] : 'ทั่วไป';
                            ?>
                            <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $category_color; ?>">
                                <?php echo $category_name; ?>
                            </span>
                            <?php if ($single_news['is_featured']): ?>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                ข่าวเด่น
                            </span>
                            <?php endif; ?>
                            <?php if ($single_news['is_urgent']): ?>
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                ด่วน
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            <?php echo htmlspecialchars($single_news['title']); ?>
                        </h1>
                        
                        <div class="flex flex-wrap items-center text-gray-600 text-sm space-x-4">
                            <div class="flex items-center space-x-1">
                                <span>📅</span>
                                <span><?php echo formatThaiDate($single_news['publish_date']); ?></span>
                            </div>
                            <?php if ($single_news['first_name']): ?>
                            <div class="flex items-center space-x-1">
                                <span>👤</span>
                                <span><?php echo htmlspecialchars($single_news['first_name'] . ' ' . $single_news['last_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="flex items-center space-x-1">
                                <span>👁️</span>
                                <span><?php echo number_format($single_news['views']); ?> ครั้ง</span>
                            </div>
                        </div>
                    </header>

                    <!-- Featured Image -->
                    <?php if (!empty($single_news['featured_image'])): ?>
                    <div class="mb-8">
                        <img src="<?php echo htmlspecialchars($single_news['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($single_news['title']); ?>"
                             class="w-full h-64 md:h-96 object-cover rounded-lg shadow-lg">
                    </div>
                    <?php endif; ?>

                    <!-- Article Content -->
                    <div class="prose prose-lg max-w-none mb-8">
                        <?php echo nl2br(htmlspecialchars($single_news['content'])); ?>
                    </div>

                    <!-- Tags -->
                    <?php if (!empty($single_news['tags'])): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-3">หัวข้อที่เกี่ยวข้อง</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $tags = explode(',', $single_news['tags']);
                            foreach ($tags as $tag):
                                $tag = trim($tag);
                                if (!empty($tag)):
                            ?>
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                                #<?php echo htmlspecialchars($tag); ?>
                            </span>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Share and Back -->
                    <div class="border-t pt-8">
                        <div class="flex flex-col sm:flex-row justify-between items-center">
                            <a href="news.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300 mb-4 sm:mb-0">
                                ← กลับสู่หน้าข่าวสาร
                            </a>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-600">แชร์:</span>
                                <button onclick="shareFacebook()" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
                                    FB
                                </button>
                                <button onclick="shareTwitter()" class="bg-blue-400 text-white p-2 rounded hover:bg-blue-500">
                                    TW
                                </button>
                                <button onclick="shareLine()" class="bg-green-500 text-white p-2 rounded hover:bg-green-600">
                                    LINE
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Related News -->
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold text-center mb-8">ข่าวที่เกี่ยวข้อง</h2>
                <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    <?php
                    try {
                        if ($conn) {
                            $stmt = $conn->prepare("
                                SELECT title, slug, excerpt, publish_date, category
                                FROM news 
                                WHERE status = 'published' 
                                AND id != ?
                                AND category = ?
                                ORDER BY publish_date DESC 
                                LIMIT 3
                            ");
                            $stmt->execute(array($single_news['id'], $single_news['category']));
                            $related_news = $stmt->fetchAll();
                            
                            foreach ($related_news as $news):
                            ?>
                            <div class="bg-white rounded-lg shadow-lg p-6">
                                <h3 class="font-semibold mb-2 line-clamp-2">
                                    <a href="news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                       class="text-gray-800 hover:text-blue-600">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </a>
                                </h3>
                                <?php if ($news['excerpt']): ?>
                                <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                                    <?php echo htmlspecialchars($news['excerpt']); ?>
                                </p>
                                <?php endif; ?>
                                <p class="text-gray-500 text-xs">
                                    <?php echo formatThaiDate($news['publish_date']); ?>
                                </p>
                            </div>
                            <?php endforeach;
                        }
                    } catch (Exception $e) {
                        echo '<p class="text-center text-gray-500">ไม่สามารถโหลดข่าวที่เกี่ยวข้องได้</p>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <script>
        function shareFacebook() {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href));
        }
        
        function shareTwitter() {
            window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent('<?php echo addslashes($single_news['title']); ?>'));
        }
        
        function shareLine() {
            window.open('https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(window.location.href));
        }
    </script>

    <?php
    include 'includes/footer.php';
    exit;
}

// Otherwise, show news listing
$news_list = array();
$total_news = 0;
$categories = array();

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        // Build WHERE clause
        $where_conditions = array("status = 'published'");
        $params = array();
        
        if (!empty($category)) {
            $where_conditions[] = "category = ?";
            $params[] = $category;
        }
        
        if (!empty($search)) {
            $where_conditions[] = "(title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
            $search_term = '%' . $search . '%';
            $params[] = $search_term;
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
        
        $list_sql = "
            SELECT id, title, slug, excerpt, publish_date, category, featured_image, 
                   is_featured, is_urgent, views
            FROM news 
            WHERE $where_clause
            AND (publish_date IS NULL OR publish_date <= NOW())
            ORDER BY is_urgent DESC, is_featured DESC, publish_date DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $conn->prepare($list_sql);
        $stmt->execute($list_params);
        $news_list = $stmt->fetchAll();
        
        // Get categories for filter
        $stmt = $conn->prepare("
            SELECT DISTINCT category, COUNT(*) as count
            FROM news 
            WHERE status = 'published'
            GROUP BY category
            ORDER BY category
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll();
    }
} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
}

// Calculate pagination
$total_pages = ceil($total_news / $per_page);

include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">ข่าวสารและประกาศ</h1>
            <p class="text-xl max-w-2xl mx-auto">ติดตามข่าวสารและประกาศสำคัญจากโรงพยาบาล</p>
        </div>
    </section>

    <!-- Search and Filter -->
    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <form method="GET" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <input type="text" name="search" placeholder="ค้นหาข่าวสาร..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="md:w-48">
                            <select name="category" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">ทุกหมวดหมู่</option>
                                <?php
                                $category_names = array(
                                    'general' => 'ทั่วไป',
                                    'announcement' => 'ประกาศ',
                                    'procurement' => 'จัดซื้อจัดจ้าง',
                                    'service' => 'บริการ',
                                    'health_tips' => 'สุขภาพ'
                                );
                                foreach ($categories as $cat):
                                    $cat_name = isset($category_names[$cat['category']]) ? $category_names[$cat['category']] : $cat['category'];
                                ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                        <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo $cat_name; ?> (<?php echo $cat['count']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <button type="submit" 
                                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition duration-300">
                            🔍 ค้นหา
                        </button>
                    </div>
                </form>

                <!-- Search Results Info -->
                <?php if (!empty($search) || !empty($category)): ?>
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-blue-800">
                        <?php if (!empty($search)): ?>
                        ผลการค้นหา: "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        <?php endif; ?>
                        <?php if (!empty($category)): ?>
                        หมวดหมู่: <strong><?php echo isset($category_names[$category]) ? $category_names[$category] : $category; ?></strong>
                        <?php endif; ?>
                        - พบ <?php echo number_format($total_news); ?> รายการ
                        <?php if (!empty($search) || !empty($category)): ?>
                        <a href="news.php" class="ml-4 text-blue-600 hover:text-blue-800">✕ ล้างการค้นหา</a>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- News Grid -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <?php if (empty($news_list)): ?>
            <div class="text-center py-16">
                <div class="text-6xl mb-4">📰</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">ไม่พบข่าวสาร</h3>
                <p class="text-gray-600">ลองเปลี่ยนคำค้นหาหรือหมวดหมู่</p>
            </div>
            <?php else: ?>
            
            <!-- Featured News -->
            <?php
            $featured_news = array_filter($news_list, function($news) {
                return $news['is_featured'] || $news['is_urgent'];
            });
            
            if (!empty($featured_news) && ($page == 1) && empty($search) && empty($category)):
            ?>
            <div class="mb-16">
                <h2 class="text-2xl font-bold mb-8 text-gray-800">ข่าวเด่น</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach (array_slice($featured_news, 0, 3) as $news): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300 border-l-4 border-indigo-500">
                        <?php if (!empty($news['featured_image'])): ?>
                        <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($news['title']); ?>"
                             class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-6">
                            <div class="flex items-center space-x-2 mb-3">
                                <?php if ($news['is_urgent']): ?>
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-medium">ด่วน</span>
                                <?php endif; ?>
                                <?php if ($news['is_featured']): ?>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium">ข่าวเด่น</span>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="text-lg font-semibold mb-3 text-gray-800 line-clamp-2">
                                <a href="news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                   class="hover:text-indigo-600">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </a>
                            </h3>
                            
                            <?php if (!empty($news['excerpt'])): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($news['excerpt']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span><?php echo formatThaiDate($news['publish_date']); ?></span>
                                <span>👁️ <?php echo number_format($news['views']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- All News -->
            <div class="<?php echo (!empty($featured_news) && ($page == 1) && empty($search) && empty($category)) ? 'border-t pt-16' : ''; ?>">
                <?php if (!empty($featured_news) && ($page == 1) && empty($search) && empty($category)): ?>
                <h2 class="text-2xl font-bold mb-8 text-gray-800">ข่าวสารทั้งหมด</h2>
                <?php endif; ?>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($news_list as $news): ?>
                    <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                        <?php if (!empty($news['featured_image'])): ?>
                        <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($news['title']); ?>"
                             class="w-full h-48 object-cover">
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="flex items-center space-x-2 mb-3">
                                <?php
                                $category_colors = array(
                                    'general' => 'bg-gray-100 text-gray-800',
                                    'announcement' => 'bg-blue-100 text-blue-800',
                                    'procurement' => 'bg-green-100 text-green-800',
                                    'service' => 'bg-purple-100 text-purple-800',
                                    'health_tips' => 'bg-pink-100 text-pink-800'
                                );
                                $category_color = isset($category_colors[$news['category']]) ? $category_colors[$news['category']] : 'bg-gray-100 text-gray-800';
                                $category_name = isset($category_names[$news['category']]) ? $category_names[$news['category']] : 'ทั่วไป';
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-medium <?php echo $category_color; ?>">
                                    <?php echo $category_name; ?>
                                </span>
                                <?php if ($news['is_urgent']): ?>
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-medium">ด่วน</span>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="text-lg font-semibold mb-3 text-gray-800 line-clamp-2">
                                <a href="news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                   class="hover:text-indigo-600">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </a>
                            </h3>
                            
                            <?php if (!empty($news['excerpt'])): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($news['excerpt']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span><?php echo formatThaiDate($news['publish_date']); ?></span>
                                <span>👁️ <?php echo number_format($news['views']); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-center">
                <nav class="flex items-center space-x-2">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        ← ก่อนหน้า
                    </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="px-3 py-2 border border-gray-300 rounded-lg <?php echo $i == $page ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        ถัดไป →
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <div class="text-center mt-4 text-sm text-gray-600">
                แสดงหน้า <?php echo $page; ?> จาก <?php echo $total_pages; ?> 
                (ทั้งหมด <?php echo number_format($total_news); ?> รายการ)
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<style>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.line-clamp-3 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}

.prose {
    line-height: 1.8;
}

.prose p {
    margin-bottom: 1em;
}
</style>

<?php include 'includes/footer.php'; ?>