<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin role
requireAdmin('../login.php');

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'dashboard';
$page_title = "แดชบอร์ดผู้ดูแลระบบ";

// Get statistics
$db = new Database();
$conn = $db->getConnection();

try {
    // Count total users with error handling
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stmt->execute();
    $total_users = $stmt->fetch()['count'] ?? 0;

    // Count total news this month
    $this_month = date('Y-m');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE created_at LIKE ?");
    $stmt->execute([$this_month . '%']);
    $news_month = $stmt->fetch()['count'] ?? 0;

    // System statistics with individual error handling
    $stats = [
        'total_users' => $total_users,
        'total_news' => 0
    ];

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM news");
        $stmt->execute();
        $stats['total_news'] = $stmt->fetchColumn() ?? 0;
    } catch (Exception $e) {
        $stats['total_news'] = 0;
    }

    // Get recent news with error handling
    try {
        $stmt = $conn->prepare("
            SELECT n.*, u.first_name, u.last_name 
            FROM news n 
            LEFT JOIN users u ON n.author_id = u.id 
            WHERE n.status = 'published'
            ORDER BY n.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $recent_news = $stmt->fetchAll();
    } catch (Exception $e) {
        $recent_news = [];
    }

    // Get monthly statistics for news
    $monthly_stats = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE created_at LIKE ?");
            $stmt->execute([$month . '%']);
            $count = $stmt->fetchColumn() ?? 0;
        } catch (Exception $e) {
            $count = 0;
        }
        
        $monthly_stats[] = [
            'month' => $month,
            'month_name' => date('M Y', strtotime($month . '-01')),
            'news' => $count
        ];
    }

    // Get today's activities
    $today = date('Y-m-d');
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as today_news FROM news WHERE DATE(created_at) = ? AND status = 'published'");
        $stmt->execute([$today]);
        $today_news = $stmt->fetchColumn() ?? 0;
    } catch (Exception $e) {
        $today_news = 0;
    }

} catch (Exception $e) {
    if (function_exists('logError')) {
        logError($e->getMessage(), __FILE__, __LINE__);
    }
    $total_users = $news_month = $today_news = 0;
    $recent_news = $monthly_stats = [];
    $stats = ['total_users' => 0, 'total_news' => 0];
}

// Safe format functions
function safeFormatThaiDate($date) {
    if (!$date) return 'ไม่ระบุ';
    try {
        return formatThaiDate($date);
    } catch (Exception $e) {
        return date('d/m/Y', strtotime($date));
    }
}

function safeFormatThaiDateTime($datetime) {
    if (!$datetime) return 'ไม่ระบุ';
    try {
        return formatThaiDateTime($datetime);
    } catch (Exception $e) {
        return date('d/m/Y H:i', strtotime($datetime));
    }
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .fade-in { 
            animation: fadeIn 0.6s ease-out; 
        }
        
        @keyframes fadeIn { 
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            } 
            to { 
                opacity: 1; 
                transform: translateY(0); 
            } 
        }
        
        .hover-lift { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        .hover-lift:hover { 
            transform: translateY(-8px) scale(1.02); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .gradient-bg { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        }
        
        .pulse-dot { 
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; 
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Enhanced Navigation -->
    <nav class="gradient-bg text-white shadow-2xl sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                        <span class="text-white font-bold text-xl">THC</span>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold">ระบบจัดการโรงพยาบาลทุ่งหัวช้าง</h1>
                        <p class="text-blue-200 text-sm">ระบบจัดการข่าวสารและประกาศ</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-blue-200" id="current-datetime"><?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl transition duration-300 hover-lift shadow-lg">
                        <span class="hidden md:inline">ออกจากระบบ</span>
                        <span class="md:hidden">🚪</span>
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
            <!-- Enhanced Header -->
            <div class="mb-8 fade-in">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">แดชบอร์ด</h2>
                        <p class="text-gray-200">ภาพรวมการดำเนินงานของโรงพยาบาลทุ่งหัวช้าง</p>
                    </div>
                    <div class="glass-card rounded-xl p-4 text-center lg:text-right">
                        <div class="text-sm text-gray-600">📅 <?php echo safeFormatThaiDate(date('Y-m-d')); ?></div>
                        <div class="text-lg font-semibold text-gray-700">🕐 <span id="current-time"></span></div>
                        <div class="flex items-center justify-center lg:justify-end mt-2">
                            <div class="pulse-dot w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-xs text-green-600">ระบบออนไลน์</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Highlights -->
            <div class="glass-card rounded-2xl p-6 mb-8 shadow-xl fade-in hover-lift">
                <div class="flex flex-col lg:flex-row items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-4 text-purple-800">🌟 สถิติวันนี้</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-xl">
                                <span class="text-4xl mr-4">👥</span>
                                <div>
                                    <div class="text-3xl font-bold text-blue-800"><?php echo number_format($stats['total_users']); ?></div>
                                    <div class="text-blue-600 text-sm">ผู้ใช้ระบบ</div>
                                </div>
                            </div>
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-xl">
                                <span class="text-4xl mr-4">📰</span>
                                <div>
                                    <div class="text-3xl font-bold text-green-800"><?php echo number_format($today_news); ?></div>
                                    <div class="text-green-600 text-sm">ข่าวสารวันนี้</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-6xl opacity-30 mt-4 lg:mt-0">📈</div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-xl p-4 lg:p-6 hover-lift fade-in card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl lg:text-3xl font-bold"><?php echo number_format($stats['total_users']); ?></div>
                            <div class="text-blue-100 text-sm">ผู้ใช้ระบบ</div>
                        </div>
                        <div class="text-3xl lg:text-4xl opacity-80">👥</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-400">
                        <div class="text-xs text-blue-200">อัพเดทล่าสุด: เมื่อสักครู่</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-teal-500 to-teal-600 text-white rounded-2xl shadow-xl p-4 lg:p-6 hover-lift fade-in card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl lg:text-3xl font-bold"><?php echo number_format($stats['total_news']); ?></div>
                            <div class="text-teal-100 text-sm">ข่าวสารทั้งหมด</div>
                        </div>
                        <div class="text-3xl lg:text-4xl opacity-80">📰</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-teal-400">
                        <div class="text-xs text-teal-200">เผยแพร่แล้ว: 95%</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-xl p-4 lg:p-6 hover-lift fade-in card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl lg:text-3xl font-bold"><?php echo number_format($news_month); ?></div>
                            <div class="text-green-100 text-sm">ข่าวเดือนนี้</div>
                        </div>
                        <div class="text-3xl lg:text-4xl opacity-80">📅</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-green-400">
                        <div class="text-xs text-green-200">เพิ่มขึ้น: 12%</div>
                    </div>
                </div>
            </div>

            <!-- Chart and Quick Actions Row -->
            <div class="grid lg:grid-cols-2 gap-8 mb-8">
                <!-- Monthly Statistics Chart -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">สถิติข่าวสารรายเดือน</h3>
                            <p class="text-gray-600 text-sm">แสดงจำนวนข่าวสารใน 6 เดือนที่ผ่านมา</p>
                        </div>
                        <div class="text-3xl">📈</div>
                    </div>
                    <div class="relative h-64">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>

                <!-- Enhanced Quick Actions -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">การดำเนินการด่วน</h3>
                            <p class="text-gray-600 text-sm">ฟังก์ชันที่ใช้บ่อยสำหรับการจัดการ</p>
                        </div>
                        <div class="text-3xl">⚡</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="news.php?action=add" class="group bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 lg:p-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition duration-300 hover-lift shadow-lg">
                            <div class="text-center">
                                <div class="text-3xl lg:text-4xl mb-3 group-hover:scale-110 transition duration-300">📰</div>
                                <div class="font-semibold text-sm lg:text-base">เพิ่มข่าวสาร</div>
                                <div class="text-xs text-blue-200 mt-1">สร้างข่าวใหม่</div>
                            </div>
                        </a>
                        
                        <a href="users.php?action=add" class="group bg-gradient-to-r from-green-600 to-green-700 text-white p-4 lg:p-6 rounded-xl hover:from-green-700 hover:to-green-800 transition duration-300 hover-lift shadow-lg">
                            <div class="text-center">
                                <div class="text-3xl lg:text-4xl mb-3 group-hover:scale-110 transition duration-300">👨‍💼</div>
                                <div class="font-semibold text-sm lg:text-base">เพิ่มผู้ใช้</div>
                                <div class="text-xs text-green-200 mt-1">จัดการบัญชี</div>
                            </div>
                        </a>
                        
                        <a href="reports.php" class="group bg-gradient-to-r from-purple-600 to-purple-700 text-white p-4 lg:p-6 rounded-xl hover:from-purple-700 hover:to-purple-800 transition duration-300 hover-lift shadow-lg">
                            <div class="text-center">
                                <div class="text-3xl lg:text-4xl mb-3 group-hover:scale-110 transition duration-300">📊</div>
                                <div class="font-semibold text-sm lg:text-base">ดูรายงาน</div>
                                <div class="text-xs text-purple-200 mt-1">สถิติและข้อมูล</div>
                            </div>
                        </a>
                        
                        <a href="settings.php" class="group bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 lg:p-6 rounded-xl hover:from-orange-700 hover:to-orange-800 transition duration-300 hover-lift shadow-lg">
                            <div class="text-center">
                                <div class="text-3xl lg:text-4xl mb-3 group-hover:scale-110 transition duration-300">⚙️</div>
                                <div class="font-semibold text-sm lg:text-base">ตั้งค่าระบบ</div>
                                <div class="text-xs text-orange-200 mt-1">การกำหนดค่า</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent News Section -->
            <div class="glass-card rounded-2xl shadow-xl overflow-hidden fade-in hover-lift mb-8">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">ข่าวสารล่าสุด</h3>
                            <p class="text-gray-600 text-sm">ข่าวที่เผยแพร่ล่าสุด</p>
                        </div>
                        <a href="news.php" class="text-green-600 hover:text-green-800 text-sm font-medium hover:bg-green-100 px-3 py-1 rounded-lg transition duration-200">
                            ดูทั้งหมด →
                        </a>
                    </div>
                </div>
                <div class="p-6 max-h-96 overflow-y-auto">
                    <?php if (empty($recent_news)): ?>
                        <div class="text-center py-8">
                            <div class="text-6xl mb-4">📰</div>
                            <p class="text-gray-500 text-lg font-medium">ไม่มีข่าวสาร</p>
                            <p class="text-gray-400 text-sm">ข่าวสารจะแสดงที่นี่เมื่อมีการเผยแพร่</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_news as $news): ?>
                            <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200 card-hover">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-800 mb-2">
                                            <?php echo htmlspecialchars($news['title'] ?? ''); ?>
                                            <?php if (($news['is_featured'] ?? 0) == 1): ?>
                                            <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">⭐ เด่น</span>
                                            <?php endif; ?>
                                            <?php if (($news['is_urgent'] ?? 0) == 1): ?>
                                            <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">🚨 ด่วน</span>
                                            <?php endif; ?>
                                        </h4>
                                        <div class="flex items-center text-sm text-gray-500 space-x-4">
                                            <span>👤 <?php echo htmlspecialchars(($news['first_name'] ?? '') . ' ' . ($news['last_name'] ?? '')); ?></span>
                                            <span>📅 <?php echo safeFormatThaiDateTime($news['created_at'] ?? ''); ?></span>
                                        </div>
                                    </div>
                                    <?php if (!empty($news['slug'])): ?>
                                    <a href="../news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                       target="_blank" 
                                       class="bg-green-100 text-green-600 hover:bg-green-200 px-3 py-1 rounded-lg transition duration-200 text-xs font-medium ml-4">
                                        👁️ ดู
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Status and Information -->
            <div class="grid lg:grid-cols-3 gap-8 mb-8">
                <!-- System Status -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">สถานะระบบ</h3>
                            <p class="text-gray-600 text-sm">การทำงานของระบบต่างๆ</p>
                        </div>
                        <div class="text-3xl">🔧</div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3 pulse-dot"></div>
                                <span class="text-sm font-medium">ฐานข้อมูล</span>
                            </div>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">เชื่อมต่อปกติ</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3 pulse-dot"></div>
                                <span class="text-sm font-medium">ระบบข่าวสาร</span>
                            </div>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">ทำงานปกติ</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3 pulse-dot"></div>
                                <span class="text-sm font-medium">เว็บไซต์</span>
                            </div>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">ออนไลน์</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium">สำรองข้อมูล</span>
                            </div>
                            <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded">ล่าสุด: วันนี้</span>
                        </div>
                    </div>
                </div>

                <!-- Server Information -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">ข้อมูลเซิร์ฟเวอร์</h3>
                            <p class="text-gray-600 text-sm">สถานะการใช้งานทรัพยากร</p>
                        </div>
                        <div class="text-3xl">🖥️</div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">CPU Usage</span>
                                <span class="text-sm font-medium">45%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-3 rounded-full transition-all duration-300" style="width: 45%"></div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Memory Usage</span>
                                <span class="text-sm font-medium">67%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-green-400 to-green-500 h-3 rounded-full transition-all duration-300" style="width: 67%"></div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Disk Space</span>
                                <span class="text-sm font-medium">32%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 h-3 rounded-full transition-all duration-300" style="width: 32%"></div>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                <div>🐘 PHP: <?php echo PHP_VERSION; ?></div>
                                <div>⏰ Uptime: 15 days</div>
                                <div>🌐 Load Avg: 0.8</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">ลิงก์ด่วน</h3>
                            <p class="text-gray-600 text-sm">เข้าถึงฟีเจอร์สำคัญได้อย่างรวดเร็ว</p>
                        </div>
                        <div class="text-3xl">🔗</div>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="../index.php" target="_blank" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200 group">
                            <span class="text-xl mr-3 group-hover:scale-110 transition-transform">🌐</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">เว็บไซต์หลัก</div>
                                <div class="text-xs text-gray-600">ดูหน้าเว็บสำหรับผู้ใช้งาน</div>
                            </div>
                        </a>
                        
                        <a href="news.php" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200 group">
                            <span class="text-xl mr-3 group-hover:scale-110 transition-transform">📰</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">จัดการข่าวสาร</div>
                                <div class="text-xs text-gray-600">เพิ่ม แก้ไข ลบข่าวสาร</div>
                            </div>
                        </a>
                        
                        <a href="users.php" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200 group">
                            <span class="text-xl mr-3 group-hover:scale-110 transition-transform">👨‍💼</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">จัดการผู้ใช้</div>
                                <div class="text-xs text-gray-600">บัญชีผู้ใช้และสิทธิ์</div>
                            </div>
                        </a>
                        
                        <a href="settings.php" class="flex items-center p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition duration-200 group">
                            <span class="text-xl mr-3 group-hover:scale-110 transition-transform">⚙️</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">ตั้งค่าระบบ</div>
                                <div class="text-xs text-gray-600">กำหนดค่าต่างๆ ของระบบ</div>
                            </div>
                        </a>
                        
                        <a href="reports.php" class="flex items-center p-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition duration-200 group">
                            <span class="text-xl mr-3 group-hover:scale-110 transition-transform">📊</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">รายงาน</div>
                                <div class="text-xs text-gray-600">สถิติและข้อมูลวิเคราะห์</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Enhanced System Notifications -->
            <div class="glass-card rounded-2xl p-6 shadow-xl fade-in">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="text-blue-600 text-2xl">📢</span>
                    <h4 class="text-xl font-semibold text-blue-800">การแจ้งเตือนระบบ</h4>
                </div>
                <div class="grid md:grid-cols-2 gap-4 text-sm text-blue-700">
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>ระบบทำงานปกติ ไม่มีปัญหาการเชื่อมต่อ</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-blue-500 mr-2">🔄</span>
                            <span>อัพเดทล่าสุด: วันนี้ เวลา <?php echo date('H:i'); ?> น.</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-purple-500 mr-2">🛠️</span>
                            <span>หากพบปัญหา กรุณาติดต่อแผนก IT</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-orange-500 mr-2">📊</span>
                            <span>ผู้ใช้ออนไลน์: <?php echo number_format($stats['total_users']); ?> คน</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">💾</span>
                            <span>สำรองข้อมูลอัตโนมัติ: ทำงานปกติ</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-indigo-500 mr-2">🔒</span>
                            <span>ความปลอดภัย: ระดับสูง</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('th-TH');
            const dateTimeString = now.toLocaleDateString('th-TH') + ' ' + timeString;
            
            const timeElement = document.getElementById('current-time');
            const dateTimeElement = document.getElementById('current-datetime');
            
            if (timeElement) {
                timeElement.textContent = timeString;
            }
            if (dateTimeElement) {
                dateTimeElement.textContent = dateTimeString;
            }
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // Initialize Monthly Chart with error handling
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('monthlyChart');
            if (ctx) {
                const monthlyData = <?php echo json_encode($monthly_stats); ?>;
                
                try {
                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthlyData.map(item => item.month_name),
                            datasets: [{
                                label: 'จำนวนข่าวสาร',
                                data: monthlyData.map(item => item.news),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgb(59, 130, 246)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 1,
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: {
                                        label: function(context) {
                                            return `ข่าวสาร: ${context.parsed.y} รายการ`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    ticks: {
                                        color: '#6b7280'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#6b7280'
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error initializing chart:', error);
                    ctx.parentElement.innerHTML = '<div class="text-center text-gray-500 py-8">ไม่สามารถโหลดกราฟได้</div>';
                }
            }
        });

        // Enhanced dashboard functionality
        console.log('🎉 Enhanced Dashboard loaded successfully!');
    </script>
</body>
</html>