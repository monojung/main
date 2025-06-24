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
        <section class="bg-gradient-to-r from-gray-50 to-gray-100 py-6">
            <div class="container mx-auto px-4">
                <nav class="flex items-center space-x-2 text-sm">
                    <a href="index.php" class="text-blue-600 hover:text-blue-800 transition duration-200">🏠 หน้าหลัก</a>
                    <span class="text-gray-400">→</span>
                    <a href="news.php" class="text-blue-600 hover:text-blue-800 transition duration-200">📰 ข่าวสาร</a>
                    <span class="text-gray-400">→</span>
                    <span class="text-gray-600 font-medium"><?php echo htmlspecialchars($single_news['title']); ?></span>
                </nav>
            </div>
        </section>

        <!-- Single News Article -->
        <article class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <!-- Article Header -->
                    <header class="mb-10">
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            <?php
                            $category_info = array(
                                'general' => ['name' => 'ทั่วไป', 'color' => 'bg-gray-100 text-gray-800', 'icon' => '📄'],
                                'announcement' => ['name' => 'ประชาสัมพันธ์', 'color' => 'bg-blue-100 text-blue-800', 'icon' => '📢'],
                                'jobs' => ['name' => 'สมัครงาน', 'color' => 'bg-green-100 text-green-800', 'icon' => '💼'],
                                'procurement' => ['name' => 'จัดซื้อจัดจ้าง', 'color' => 'bg-orange-100 text-orange-800', 'icon' => '🛒'],
                                'accounting' => ['name' => 'การบัญชี', 'color' => 'bg-purple-100 text-purple-800', 'icon' => '💰'],
                                'health_tips' => ['name' => 'สุขภาพ', 'color' => 'bg-pink-100 text-pink-800', 'icon' => '🏥']
                            );
                            $cat_info = $category_info[$single_news['category']] ?? ['name' => 'ทั่วไป', 'color' => 'bg-gray-100 text-gray-800', 'icon' => '📄'];
                            ?>
                            <span class="px-4 py-2 rounded-full text-sm font-medium <?php echo $cat_info['color']; ?>">
                                <?php echo $cat_info['icon']; ?> <?php echo $cat_info['name']; ?>
                            </span>
                            <?php if ($single_news['is_featured']): ?>
                            <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                ⭐ ข่าวเด่น
                            </span>
                            <?php endif; ?>
                            <?php if ($single_news['is_urgent']): ?>
                            <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm font-medium animate-pulse">
                                🚨 ด่วน
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                            <?php echo htmlspecialchars($single_news['title']); ?>
                        </h1>
                        
                        <?php if ($single_news['excerpt']): ?>
                        <div class="text-xl text-gray-600 mb-6 leading-relaxed">
                            <?php echo htmlspecialchars($single_news['excerpt']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex flex-wrap items-center gap-6 text-gray-600 text-sm bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">📅</span>
                                <span class="font-medium"><?php echo formatThaiDate($single_news['publish_date']); ?></span>
                            </div>
                            <?php if ($single_news['first_name']): ?>
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">👤</span>
                                <span><?php echo htmlspecialchars($single_news['first_name'] . ' ' . $single_news['last_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">👁️</span>
                                <span><?php echo number_format($single_news['views']); ?> ครั้ง</span>
                            </div>
                        </div>
                    </header>

                    <!-- Featured Image -->
                    <?php if (!empty($single_news['featured_image'])): ?>
                    <div class="mb-10">
                        <img src="<?php echo htmlspecialchars($single_news['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($single_news['title']); ?>"
                             class="w-full h-64 md:h-96 object-cover rounded-2xl shadow-xl">
                    </div>
                    <?php endif; ?>

                    <!-- Article Content -->
                    <div class="prose prose-lg max-w-none mb-10 leading-relaxed text-gray-800">
                        <?php echo nl2br(htmlspecialchars($single_news['content'])); ?>
                    </div>

                    <!-- Attachments -->
                    <?php 
                    $attachments = json_decode($single_news['attachments'] ?? '[]', true);
                    if (!empty($attachments)): 
                    ?>
                    <div class="mb-10 bg-blue-50 rounded-2xl p-6">
                        <h3 class="text-xl font-bold mb-4 flex items-center">
                            <span class="text-2xl mr-2">📎</span> ไฟล์แนบ
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <?php foreach ($attachments as $attachment): ?>
                            <a href="<?php echo htmlspecialchars($attachment['path']); ?>" 
                               target="_blank"
                               class="flex items-center p-4 bg-white rounded-lg hover:bg-gray-50 transition duration-200 shadow-sm">
                                <div class="text-3xl mr-4">
                                    <?php
                                    $ext = strtolower(pathinfo($attachment['name'], PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) echo '🖼️';
                                    elseif ($ext === 'pdf') echo '📄';
                                    elseif (in_array($ext, ['doc', 'docx'])) echo '📝';
                                    else echo '📎';
                                    ?>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($attachment['name']); ?></div>
                                    <div class="text-sm text-gray-500">คลิกเพื่อดาวน์โหลด</div>
                                </div>
                                <div class="text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tags -->
                    <?php if (!empty($single_news['tags'])): ?>
                    <div class="mb-10">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span class="text-xl mr-2">🏷️</span> หัวข้อที่เกี่ยวข้อง
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $tags = explode(',', $single_news['tags']);
                            foreach ($tags as $tag):
                                $tag = trim($tag);
                                if (!empty($tag)):
                            ?>
                            <span class="px-4 py-2 bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 rounded-full text-sm font-medium hover:from-blue-200 hover:to-blue-300 transition duration-200">
                                #<?php echo htmlspecialchars($tag); ?>
                            </span>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Share and Back -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                            <a href="news.php" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                ← กลับสู่หน้าข่าวสาร
                            </a>
                            <div class="flex items-center space-x-3">
                                <span class="text-gray-600 font-medium">แชร์:</span>
                                <button onclick="shareFacebook()" class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition duration-200 shadow-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </button>
                                <button onclick="shareTwitter()" class="bg-sky-400 text-white p-3 rounded-full hover:bg-sky-500 transition duration-200 shadow-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </button>
                                <button onclick="shareLine()" class="bg-green-500 text-white p-3 rounded-full hover:bg-green-600 transition duration-200 shadow-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.070 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                                    </svg>
                                </button>
                                <button onclick="copyLink()" class="bg-gray-600 text-white p-3 rounded-full hover:bg-gray-700 transition duration-200 shadow-lg" title="คัดลอกลิงก์">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Related News -->
        <section class="py-16 bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">ข่าวที่เกี่ยวข้อง</h2>
                <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <?php
                    try {
                        if ($conn) {
                            $stmt = $conn->prepare("
                                SELECT title, slug, excerpt, publish_date, category, featured_image, views
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
                            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                                <?php if ($news['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>"
                                     class="w-full h-48 object-cover">
                                <?php endif; ?>
                                <div class="p-6">
                                    <h3 class="font-bold text-lg mb-3 line-clamp-2 text-gray-800">
                                        <a href="news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                           class="hover:text-blue-600 transition duration-200">
                                            <?php echo htmlspecialchars($news['title']); ?>
                                        </a>
                                    </h3>
                                    <?php if ($news['excerpt']): ?>
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                        <?php echo htmlspecialchars($news['excerpt']); ?>
                                    </p>
                                    <?php endif; ?>
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <span>📅 <?php echo formatThaiDate($news['publish_date']); ?></span>
                                        <span>👁️ <?php echo number_format($news['views']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach;
                        }
                    } catch (Exception $e) {
                        echo '<p class="text-center text-gray-500 col-span-3">ไม่สามารถโหลดข่าวที่เกี่ยวข้องได้</p>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <script>
        function shareFacebook() {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank', 'width=600,height=400');
        }
        
        function shareTwitter() {
            window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent('<?php echo addslashes($single_news['title']); ?>'), '_blank', 'width=600,height=400');
        }
        
        function shareLine() {
            window.open('https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(window.location.href), '_blank', 'width=600,height=400');
        }
        
        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                showNotification('คัดลอกลิงก์แล้ว!', 'success');
            });
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    </script>

    <?php
    include 'includes/footer.php';
    exit;
}

// Otherwise, show news listing with tabs
$news_list = array();
$total_news = 0;
$categories_stats = array();

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        // Get category statistics
        $stats_sql = "
            SELECT 
                category,
                COUNT(*) as count
            FROM news 
            WHERE status = 'published'
            AND (publish_date IS NULL OR publish_date <= NOW())
            GROUP BY category
            ORDER BY count DESC
        ";
        $stmt = $conn->prepare($stats_sql);
        $stmt->execute();
        $categories_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Build WHERE clause
        $where_conditions = array("status = 'published'", "(publish_date IS NULL OR publish_date <= NOW())");
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
                   is_featured, is_urgent, views, attachments
            FROM news 
            WHERE $where_clause
            ORDER BY is_urgent DESC, is_featured DESC, publish_date DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $conn->prepare($list_sql);
        $stmt->execute($list_params);
        $news_list = $stmt->fetchAll();
    }
} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
}

// Calculate pagination
$total_pages = ceil($total_news / $per_page);

// Category configurations
$category_configs = array(
    'announcement' => ['name' => 'ประชาสัมพันธ์', 'icon' => '📢', 'color' => 'blue', 'description' => 'ข่าวประชาสัมพันธ์และกิจกรรม'],
    'jobs' => ['name' => 'สมัครงาน', 'icon' => '💼', 'color' => 'green', 'description' => 'ตำแหน่งงานและการรับสมัคร'],
    'procurement' => ['name' => 'จัดซื้อจัดจ้าง', 'icon' => '🛒', 'color' => 'orange', 'description' => 'ประกาศจัดซื้อจัดจ้างและประมูล'],
    'accounting' => ['name' => 'การบัญชี', 'icon' => '💰', 'color' => 'purple', 'description' => 'ข้อมูลทางการเงินและบัญชี'],
    'general' => ['name' => 'ทั่วไป', 'icon' => '📄', 'color' => 'gray', 'description' => 'ข่าวสารทั่วไปอื่นๆ'],
    'health_tips' => ['name' => 'สุขภาพ', 'icon' => '🏥', 'color' => 'pink', 'description' => 'คำแนะนำและข้อมูลสุขภาพ']
);

include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 text-white py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="absolute inset-0">
            <div class="absolute top-10 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-full animate-pulse"></div>
            <div class="absolute top-32 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-full animate-pulse delay-75"></div>
            <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-white bg-opacity-10 rounded-full animate-pulse delay-150"></div>
        </div>
        <div class="container mx-auto px-4 text-center relative z-10">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">📰 ข่าวสารและประกาศ</h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto mb-8 text-blue-100">
                ติดตามข่าวสารและประกาศสำคัญจากโรงพยาบาลทุ่งหัวช้าง
            </p>
            <div class="flex flex-wrap justify-center gap-4 text-lg">
                <div class="bg-white bg-opacity-20 rounded-full px-6 py-2 backdrop-blur-sm">
                    📊 ทั้งหมด <?php echo number_format(array_sum($categories_stats)); ?> ข่าว
                </div>
                <div class="bg-white bg-opacity-20 rounded-full px-6 py-2 backdrop-blur-sm">
                    🔄 อัปเดตทุกวัน
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Tab Navigation -->
    <section class="bg-white shadow-lg sticky top-0 z-40">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <!-- Tabs -->
                <div class="flex space-x-1 bg-gray-100 rounded-xl p-1 overflow-x-auto">
                    <a href="?category=" 
                       class="px-6 py-3 rounded-lg text-sm font-medium transition duration-200 whitespace-nowrap <?php echo empty($category) ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-600 hover:text-blue-600 hover:bg-white'; ?>">
                        📋 ทั้งหมด
                        <span class="ml-2 px-2 py-1 text-xs rounded-full <?php echo empty($category) ? 'bg-blue-500' : 'bg-gray-300'; ?>">
                            <?php echo number_format(array_sum($categories_stats)); ?>
                        </span>
                    </a>
                    
                    <?php foreach ($category_configs as $cat_key => $cat_config): 
                        $count = $categories_stats[$cat_key] ?? 0;
                        if ($count > 0):
                    ?>
                    <a href="?category=<?php echo $cat_key; ?>" 
                       class="px-6 py-3 rounded-lg text-sm font-medium transition duration-200 whitespace-nowrap <?php echo $category === $cat_key ? "bg-{$cat_config['color']}-600 text-white shadow-lg" : 'text-gray-600 hover:text-blue-600 hover:bg-white'; ?>">
                        <?php echo $cat_config['icon']; ?> <?php echo $cat_config['name']; ?>
                        <span class="ml-2 px-2 py-1 text-xs rounded-full <?php echo $category === $cat_key ? "bg-{$cat_config['color']}-500" : 'bg-gray-300'; ?>">
                            <?php echo number_format($count); ?>
                        </span>
                    </a>
                    <?php endif; endforeach; ?>
                </div>
                
                <!-- Search -->
                <form method="GET" class="ml-4">
                    <?php if (!empty($category)): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                    <div class="relative">
                        <input type="text" name="search" placeholder="ค้นหาข่าวสาร..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                        <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                        <button type="submit" class="absolute right-2 top-1.5 bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                            ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Category Description -->
    <?php if (!empty($category) && isset($category_configs[$category])): ?>
    <section class="py-8 bg-gradient-to-r from-gray-50 to-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <div class="text-6xl mb-4"><?php echo $category_configs[$category]['icon']; ?></div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2"><?php echo $category_configs[$category]['name']; ?></h2>
                <p class="text-gray-600 text-lg"><?php echo $category_configs[$category]['description']; ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Search Results Info -->
    <?php if (!empty($search)): ?>
    <section class="py-6 bg-blue-50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between bg-white rounded-lg p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl">🔍</span>
                        <div>
                            <p class="font-medium text-gray-800">
                                ผลการค้นหา: "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            </p>
                            <p class="text-sm text-gray-600">พบ <?php echo number_format($total_news); ?> รายการ</p>
                        </div>
                    </div>
                    <a href="?<?php echo !empty($category) ? 'category=' . urlencode($category) : ''; ?>" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        ✕ ล้างการค้นหา
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- News Grid -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <?php if (empty($news_list)): ?>
            <div class="text-center py-20">
                <div class="text-8xl mb-6">📰</div>
                <h3 class="text-3xl font-bold text-gray-800 mb-4">ไม่พบข่าวสาร</h3>
                <p class="text-gray-600 text-lg mb-6">ลองเปลี่ยนคำค้นหาหรือเลือกหมวดหมู่อื่น</p>
                <a href="news.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                    🔙 กลับสู่หน้าข่าวสาร
                </a>
            </div>
            <?php else: ?>
            
            <!-- Featured News (Only on first page and when no search/category filter) -->
            <?php
            $featured_news = array_filter($news_list, function($news) {
                return $news['is_featured'] || $news['is_urgent'];
            });
            
            if (!empty($featured_news) && ($page == 1) && empty($search) && empty($category)):
            ?>
            <div class="mb-16">
                <div class="flex items-center justify-center mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-1 bg-gradient-to-r from-transparent to-yellow-400 rounded"></div>
                        <h2 class="text-3xl font-bold text-gray-800">⭐ ข่าวเด่น</h2>
                        <div class="w-8 h-1 bg-gradient-to-l from-transparent to-yellow-400 rounded"></div>
                    </div>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach (array_slice($featured_news, 0, 3) as $news): ?>
                    <article class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-300 transform hover:-translate-y-2 border-l-4 border-yellow-400">
                        <?php if (!empty($news['featured_image'])): ?>
                        <div class="relative">
                            <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($news['title']); ?>"
                                 class="w-full h-52 object-cover">
                            <div class="absolute top-4 left-4 flex space-x-2">
                                <?php if ($news['is_urgent']): ?>
                                <span class="px-3 py-1 bg-red-500 text-white rounded-full text-xs font-bold animate-pulse">🚨 ด่วน</span>
                                <?php endif; ?>
                                <?php if ($news['is_featured']): ?>
                                <span class="px-3 py-1 bg-yellow-500 text-white rounded-full text-xs font-bold">⭐ เด่น</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="h-52 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                            <span class="text-6xl"><?php echo $category_configs[$news['category']]['icon'] ?? '📰'; ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="mb-3">
                                <?php $cat_config = $category_configs[$news['category']] ?? $category_configs['general']; ?>
                                <span class="px-3 py-1 bg-<?php echo $cat_config['color']; ?>-100 text-<?php echo $cat_config['color']; ?>-800 rounded-full text-xs font-medium">
                                    <?php echo $cat_config['icon']; ?> <?php echo $cat_config['name']; ?>
                                </span>
                            </div>
                            
                            <h3 class="text-xl font-bold mb-3 text-gray-800 line-clamp-2 leading-tight">
                                <a href="news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                   class="hover:text-blue-600 transition duration-200">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </a>
                            </h3>
                            
                            <?php if (!empty($news['excerpt'])): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                                <?php echo htmlspecialchars($news['excerpt']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center space-x-4">
                                    <span>📅 <?php echo formatThaiDate($news['publish_date']); ?></span>
                                    <span>👁️ <?php echo number_format($news['views']); ?></span>
                                </div>
                                <?php if (!empty($news['attachments']) && $news['attachments'] !== '[]'): ?>
                                <span class="text-blue-600">📎 มีไฟล์แนบ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- All News -->
            <div class="<?php echo (!empty($featured_news) && ($page == 1) && empty($search) && empty($category)) ? 'border-t pt-16' : ''; ?>">
                <?php if (!empty($featured_news) && ($page == 1) && empty($search) && empty($category)): ?>
                <div class="flex items-center justify-center mb-12">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-1 bg-gradient-to-r from-transparent to-blue-400 rounded"></div>
                        <h2 class="text-3xl font-bold text-gray-800">📰 ข่าวสารทั้งหมด</h2>
                        <div class="w-8 h-1 bg-gradient-to-l from-transparent to-blue-400 rounded"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <?php foreach ($news_list as $news): ?>
                    <article class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 transform hover:-translate-y-1 group">
                        <div class="relative">
                            <?php if (!empty($news['featured_image'])): ?>
                            <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($news['title']); ?>"
                                 class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                            <?php else: ?>
                            <div class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <span class="text-5xl opacity-50"><?php echo $category_configs[$news['category']]['icon'] ?? '📰'; ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Status Badges -->
                            <div class="absolute top-3 left-3 flex flex-col space-y-1">
                                <?php if ($news['is_urgent']): ?>
                                <span class="px-2 py-1 bg-red-500 text-white rounded-full text-xs font-bold animate-pulse">🚨 ด่วน</span>
                                <?php endif; ?>
                                <?php if ($news['is_featured']): ?>
                                <span class="px-2 py-1 bg-yellow-500 text-white rounded-full text-xs font-bold">⭐ เด่น</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Category Badge -->
                            <div class="absolute top-3 right-3">
                                <?php $cat_config = $category_configs[$news['category']] ?? $category_configs['general']; ?>
                                <span class="px-2 py-1 bg-white bg-opacity-90 text-gray-800 rounded-full text-xs font-medium shadow-sm">
                                    <?php echo $cat_config['icon']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="mb-3">
                                <?php $cat_config = $category_configs[$news['category']] ?? $category_configs['general']; ?>
                                <span class="px-3 py-1 bg-<?php echo $cat_config['color']; ?>-100 text-<?php echo $cat_config['color']; ?>-800 rounded-full text-xs font-medium">
                                    <?php echo $cat_config['name']; ?>
                                </span>
                            </div>
                            
                            <h3 class="text-lg font-bold mb-3 text-gray-800 line-clamp-2 leading-tight">
                                <a href="news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                   class="hover:text-blue-600 transition duration-200">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </a>
                            </h3>
                            
                            <?php if (!empty($news['excerpt'])): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                                <?php echo htmlspecialchars($news['excerpt']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center space-x-3">
                                    <span>📅 <?php echo formatThaiDate($news['publish_date']); ?></span>
                                    <span>👁️ <?php echo number_format($news['views']); ?></span>
                                </div>
                                <?php if (!empty($news['attachments']) && $news['attachments'] !== '[]'): ?>
                                <span class="text-blue-600 font-medium">📎</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Enhanced Pagination -->
    <?php if ($total_pages > 1): ?>
    <section class="py-12 bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex flex-col items-center space-y-4">
                <!-- Page Numbers -->
                <nav class="flex items-center space-x-2">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        ก่อนหน้า
                    </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <a href="?page=1<?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 shadow-sm">1</a>
                        <?php if ($start > 2): ?>
                        <span class="px-2 text-gray-500">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="px-4 py-2 border rounded-lg transition duration-200 shadow-sm <?php echo $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-300 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                        <?php if ($end < $total_pages - 1): ?>
                        <span class="px-2 text-gray-500">...</span>
                        <?php endif; ?>
                        <a href="?page=<?php echo $total_pages; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 shadow-sm"><?php echo $total_pages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 shadow-sm">
                        ถัดไป
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    <?php endif; ?>
                </nav>
                
                <!-- Page Info -->
                <div class="text-center text-sm text-gray-600 bg-white rounded-lg px-6 py-3 shadow-sm">
                    แสดงหน้า <span class="font-semibold text-blue-600"><?php echo $page; ?></span> จาก <span class="font-semibold"><?php echo $total_pages; ?></span> 
                    (ทั้งหมด <span class="font-semibold text-blue-600"><?php echo number_format($total_news); ?></span> รายการ)
                </div>
                
                <!-- Quick Jump -->
                <?php if ($total_pages > 10): ?>
                <form method="GET" class="flex items-center space-x-2">
                    <?php if (!empty($category)): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                    <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <?php endif; ?>
                    <span class="text-sm text-gray-600">ไปที่หน้า:</span>
                    <input type="number" name="page" min="1" max="<?php echo $total_pages; ?>" 
                           value="<?php echo $page; ?>" 
                           class="w-20 px-3 py-1 border border-gray-300 rounded text-center text-sm">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded text-sm hover:bg-blue-700 transition duration-200">
                        ไป
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Quick Stats -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-center mb-8 text-gray-800">📊 สถิติข่าวสาร</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <?php foreach ($category_configs as $cat_key => $cat_config): 
                        $count = $categories_stats[$cat_key] ?? 0;
                    ?>
                    <div class="text-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition duration-200">
                        <div class="text-3xl mb-2"><?php echo $cat_config['icon']; ?></div>
                        <div class="text-2xl font-bold text-<?php echo $cat_config['color']; ?>-600 mb-1">
                            <?php echo number_format($count); ?>
                        </div>
                        <div class="text-xs text-gray-600"><?php echo $cat_config['name']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
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

/* Smooth scroll for tab navigation */
html {
    scroll-behavior: smooth;
}

/* Loading animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

/* Hover effects */
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Gradient text */
.gradient-text {
    background: linear-gradient(45deg, #3B82F6, #8B5CF6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Tab indicator */
.tab-active {
    position: relative;
}

.tab-active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, currentColor, transparent);
    border-radius: 1.5px;
}

/* Mobile responsive improvements */
@media (max-width: 640px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .text-5xl {
        font-size: 2.5rem;
    }
    
    .text-6xl {
        font-size: 3rem;
    }
}

/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
}
</style>

<script>
// Enhanced JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Search with debounce
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            if (this.value.length >= 3 || this.value.length === 0) {
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500);
            }
        });
    }
    
    // Add fade-in animation to news cards
    const newsCards = document.querySelectorAll('article');
    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, { threshold: 0.1 });
    
    newsCards.forEach(card => {
        cardObserver.observe(card);
    });
    
    // Tab persistence
    const urlParams = new URLSearchParams(window.location.search);
    const activeCategory = urlParams.get('category');
    if (activeCategory) {
        const activeTab = document.querySelector(`a[href*="category=${activeCategory}"]`);
        if (activeTab) {
            activeTab.classList.add('tab-active');
        }
    }
    
    // Reading progress indicator (for single news pages)
    if (document.querySelector('article')) {
        const progressBar = document.createElement('div');
        progressBar.className = 'fixed top-0 left-0 w-full h-1 bg-blue-600 z-50 transform scale-x-0 origin-left transition-transform duration-150';
        document.body.appendChild(progressBar);
        
        window.addEventListener('scroll', () => {
            const article = document.querySelector('article');
            if (article) {
                const articleHeight = article.offsetHeight;
                const articleTop = article.offsetTop;
                const scrollTop = window.pageYOffset;
                const progress = Math.min((scrollTop - articleTop) / articleHeight, 1);
                
                if (progress > 0) {
                    progressBar.style.transform = `scaleX(${progress})`;
                } else {
                    progressBar.style.transform = 'scaleX(0)';
                }
            }
        });
    }
});

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'warning' ? 'bg-yellow-500' : 
        'bg-blue-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Share functions
function shareFacebook() {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank', 'width=600,height=400');
}

function shareTwitter() {
    const title = document.querySelector('h1') ? document.querySelector('h1').textContent : 'ข่าวสารจากโรงพยาบาลทุ่งหัวช้าง';
    window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(title), '_blank', 'width=600,height=400');
}

function shareLine() {
    window.open('https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(window.location.href), '_blank', 'width=600,height=400');
}

function copyLink() {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href).then(function() {
            showNotification('คัดลอกลิงก์แล้ว!', 'success');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = window.location.href;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showNotification('คัดลอกลิงก์แล้ว!', 'success');
        } catch (err) {
            showNotification('ไม่สามารถคัดลอกลิงก์ได้', 'error');
        }
        document.body.removeChild(textArea);
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + F for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Arrow keys for navigation
    if (e.key === 'ArrowLeft' && e.altKey) {
        const prevButton = document.querySelector('a[href*="page=' + (<?php echo $page; ?> - 1) + '"]');
        if (prevButton) {
            window.location.href = prevButton.href;
        }
    }
    
    if (e.key === 'ArrowRight' && e.altKey) {
        const nextButton = document.querySelector('a[href*="page=' + (<?php echo $page; ?> + 1) + '"]');
        if (nextButton) {
            window.location.href = nextButton.href;
        }
    }
});

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(function() {
            const perfData = window.performance.timing;
            const loadTime = perfData.loadEventEnd - perfData.navigationStart;
            console.log('Page load time: ' + loadTime + 'ms');
        }, 100);
    });
}
</script>

<?php include 'includes/footer.php'; ?>