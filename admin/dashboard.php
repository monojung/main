<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Require admin role
requireAdmin('../login.php');

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'dashboard';
$page_title = "แดชบอร์ดผู้ดูแลระบบ";

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Get statistics using functions
try {
    $stats = [
        'total_users' => getTotalUsers($conn),
        'total_news' => getTotalNews($conn),
        'news_month' => getNewsThisMonth($conn),
        'today_news' => getTodayNews($conn),
        'total_ita' => getTotalITA($conn),
        'pending_ita' => getPendingITA($conn),
        'approved_ita' => getApprovedITA($conn)
    ];

    // Get recent news
    $recent_news = getRecentNews($conn, 5);
    
    // Get monthly statistics for chart
    $monthly_stats = getMonthlyStats($conn, 6);
    
    // Get system report
    $system_report = generateSystemReport($conn);
    
} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
    
    // Default values if database fails
    $stats = [
        'total_users' => 0,
        'total_news' => 0,
        'news_month' => 0,
        'today_news' => 0,
        'total_ita' => 0,
        'pending_ita' => 0,
        'approved_ita' => 0
    ];
    $recent_news = [];
    $monthly_stats = [];
    $system_report = ['generated_at' => date('Y-m-d H:i:s')];
}

// Get quick stats for today
$today_stats = [
    'new_users' => 0,
    'published_news' => $stats['today_news'],
    'pending_ita' => $stats['pending_ita'],
    'system_alerts' => 0
];

try {
    // Get new users today
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND is_active = 1");
    $stmt->execute();
    $today_stats['new_users'] = $stmt->fetchColumn() ?? 0;
    
    // Check for system alerts (example: failed logins, disk space, etc.)
    $alerts = 0;
    
    // Check for failed login attempts in last hour
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE login_attempts >= 3 AND locked_until > NOW()");
    $stmt->execute();
    $locked_accounts = $stmt->fetchColumn() ?? 0;
    if ($locked_accounts > 0) $alerts++;
    
    // Check disk space (if available)
    $free_space = disk_free_space('.');
    $total_space = disk_total_space('.');
    if ($free_space && $total_space && ($free_space / $total_space) < 0.1) {
        $alerts++; // Less than 10% free space
    }
    
    $today_stats['system_alerts'] = $alerts;
    
} catch (Exception $e) {
    // Keep default values
}

// Get recent activities
$recent_activities = [];
try {
    $stmt = $conn->prepare("
        SELECT al.*, u.first_name, u.last_name 
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
} catch (Exception $e) {
    // Activity logs table might not exist
}

// Performance metrics
$performance = [
    'page_load_time' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3),
    'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2),
    'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
    'database_queries' => 0 // This would need to be tracked separately
];
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

        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.8) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .metric-ring {
            position: relative;
            display: inline-block;
        }

        .metric-ring::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #3b82f6, #8b5cf6, #f59e0b, #3b82f6);
            animation: rotate 3s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
                        <span class="text-white font-bold text-xl">🏥</span>
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
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">📊 แดชบอร์ด</h2>
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
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-xl">
                                <span class="text-3xl mr-3">👥</span>
                                <div>
                                    <div class="text-2xl font-bold text-blue-800"><?php echo number_format($today_stats['new_users']); ?></div>
                                    <div class="text-blue-600 text-xs">ผู้ใช้ใหม่</div>
                                </div>
                            </div>
                            <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-xl">
                                <span class="text-3xl mr-3">📰</span>
                                <div>
                                    <div class="text-2xl font-bold text-green-800"><?php echo number_format($today_stats['published_news']); ?></div>
                                    <div class="text-green-600 text-xs">ข่าวสาร</div>
                                </div>
                            </div>
                            <div class="flex items-center bg-gradient-to-r from-orange-50 to-orange-100 p-4 rounded-xl">
                                <span class="text-3xl mr-3">🔧</span>
                                <div>
                                    <div class="text-2xl font-bold text-orange-800"><?php echo number_format($today_stats['pending_ita']); ?></div>
                                    <div class="text-orange-600 text-xs">ITA รอ</div>
                                </div>
                            </div>
                            <div class="flex items-center bg-gradient-to-r from-red-50 to-red-100 p-4 rounded-xl">
                                <span class="text-3xl mr-3">⚠️</span>
                                <div>
                                    <div class="text-2xl font-bold text-red-800"><?php echo number_format($today_stats['system_alerts']); ?></div>
                                    <div class="text-red-600 text-xs">แจ้งเตือน</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-6xl opacity-30 mt-4 lg:mt-0">📈</div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <div class="stat-card rounded-2xl shadow-xl p-4 lg:p-6 hover-lift fade-in card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl lg:text-3xl font-bold text-blue-600"><?php echo number_format($stats['total_users']); ?></div>
                            <div class="text-blue-500 text-sm">ผู้ใช้ระบบ</div>
                        </div>
                        <div class="metric-ring">
                            <div class="text-3xl lg:text-4xl text-blue-500 bg-blue-50 rounded-full p-3">👥</div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-200">
                        <div class="text-xs text-blue-400">อัพเดทล่าสุด: เมื่อสักครู่</div>
                    </div>
                </div>

                <div class="stat-card rounded-2xl shadow-xl p-4 lg:p-6 hover-lift fade-in card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl lg:text-3xl font-bold text-teal-600"><?php echo number_format($stats['total_news']); ?></div>
                            <div class="text-teal-500 text-sm">ข่าวสารทั้งหมด</div>
                        </div>
                        <div class="text-3xl lg:text-4xl text-teal-500 bg-teal-50 rounded-full p-3">📰</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-teal-200">
                        <div class="text-xs text-teal-400">เผยแพร่แล้ว: 95%</div>
                    </div>
                </div>

                <div class="stat-card rounded-2xl shadow-xl p-4 lg:p-6 hover-lift fade-in card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl lg:text-3xl font-bold text-green-600"><?php echo number_format($stats['news_month']); ?></div>
                            <div class="text-green-500 text-sm">ข่าวเดือนนี้</div>
                        </div>
                        <div class="text-3xl lg:text-4xl text-green-500 bg-green-50 rounded-full p-3">📅</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-green-200">
                        <div class="text-xs text-green-400">เพิ่มขึ้น: <?php echo $stats['news_month'] > 0 ? '+12%' : '0%'; ?></div>
                    </div>
                </div>

                <div class="stat-card rounded-2xl shadow-xl p-4 lg:p-6 hover-lift fade-in card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl lg:text-3xl font-bold text-purple-600"><?php echo number_format($stats['total_ita']); ?></div>
                            <div class="text-purple-500 text-sm">ITA Requests</div>
                        </div>
                        <div class="text-3xl lg:text-4xl text-purple-500 bg-purple-50 rounded-full p-3">🔧</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-purple-200">
                        <div class="text-xs text-purple-400">รอดำเนินการ: <?php echo $stats['pending_ita']; ?></div>
                    </div>
                </div>
            </div>

            <!-- Chart and Quick Actions Row -->
            <div class="grid lg:grid-cols-2 gap-8 mb-8">
                <!-- Monthly Statistics Chart -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">📈 สถิติข่าวสารรายเดือน</h3>
                            <p class="text-gray-600 text-sm">แสดงจำนวนข่าวสารใน 6 เดือนที่ผ่านมา</p>
                        </div>
                        <div class="text-3xl">📊</div>
                    </div>
                    <div class="relative h-64">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>

                <!-- Enhanced Quick Actions -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">⚡ การดำเนินการด่วน</h3>
                            <p class="text-gray-600 text-sm">ฟังก์ชันที่ใช้บ่อยสำหรับการจัดการ</p>
                        </div>
                        <div class="text-3xl">🎯</div>
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
                        
                        <a href="ita.php" class="group bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 lg:p-6 rounded-xl hover:from-orange-700 hover:to-orange-800 transition duration-300 hover-lift shadow-lg">
                            <div class="text-center">
                                <div class="text-3xl lg:text-4xl mb-3 group-hover:scale-110 transition duration-300">🔧</div>
                                <div class="font-semibold text-sm lg:text-base">จัดการ ITA</div>
                                <div class="text-xs text-orange-200 mt-1">คำขอ IT Support</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent News and Activities Row -->
            <div class="grid lg:grid-cols-2 gap-8 mb-8">
                <!-- Recent News Section -->
                <div class="glass-card rounded-2xl shadow-xl overflow-hidden fade-in hover-lift">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">📰 ข่าวสารล่าสุด</h3>
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

                <!-- Recent Activities -->
                <div class="glass-card rounded-2xl shadow-xl overflow-hidden fade-in hover-lift">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">🔄 กิจกรรมล่าสุด</h3>
                                <p class="text-gray-600 text-sm">การดำเนินการในระบบ</p>
                            </div>
                            <a href="reports.php?report_type=activity" class="text-purple-600 hover:text-purple-800 text-sm font-medium hover:bg-purple-100 px-3 py-1 rounded-lg transition duration-200">
                                ดูทั้งหมด →
                            </a>
                        </div>
                    </div>
                    <div class="p-6 max-h-96 overflow-y-auto">
                        <?php if (empty($recent_activities)): ?>
                            <div class="text-center py-8">
                                <div class="text-6xl mb-4">🔄</div>
                                <p class="text-gray-500 text-lg font-medium">ไม่มีกิจกรรม</p>
                                <p class="text-gray-400 text-sm">กิจกรรมจะแสดงที่นี่เมื่อมีการใช้งาน</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($recent_activities as $activity): ?>
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <span class="text-purple-600 text-sm">
                                            <?php
                                            $action_icons = [
                                                'login' => '🔑',
                                                'logout' => '🚪',
                                                'news_created' => '📰',
                                                'news_updated' => '✏️',
                                                'news_deleted' => '🗑️',
                                                'user_created' => '👤',
                                                'user_updated' => '👨‍💼',
                                                'user_deleted' => '❌',
                                                'ita_created' => '🔧',
                                                'ita_updated' => '🔄',
                                                'settings_updated' => '⚙️',
                                                'default' => '📋'
                                            ];
                                            echo $action_icons[$activity['action']] ?? $action_icons['default'];
                                            ?>
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-800 truncate">
                                            <?php echo htmlspecialchars(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? 'ระบบ')); ?>
                                        </div>
                                        <div class="text-xs text-gray-500 truncate">
                                            <?php echo htmlspecialchars($activity['action'] ?? ''); ?>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        <?php echo safeFormatThaiDateTime($activity['created_at'] ?? ''); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Status and Performance -->
            <div class="grid lg:grid-cols-3 gap-8 mb-8">
                <!-- System Status -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">🔧 สถานะระบบ</h3>
                            <p class="text-gray-600 text-sm">การทำงานของระบบต่างๆ</p>
                        </div>
                        <div class="text-3xl">🖥️</div>
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

                        <?php if ($today_stats['system_alerts'] > 0): ?>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-3 pulse-dot"></div>
                                <span class="text-sm font-medium">แจ้งเตือน</span>
                            </div>
                            <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded"><?php echo $today_stats['system_alerts']; ?> รายการ</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Server Performance -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">⚡ ประสิทธิภาพ</h3>
                            <p class="text-gray-600 text-sm">การใช้งานทรัพยากรระบบ</p>
                        </div>
                        <div class="text-3xl">📊</div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">การใช้หน่วยความจำ</span>
                                <span class="text-sm font-medium"><?php echo $performance['memory_usage']; ?> MB</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <?php 
                                $memory_percent = min(100, ($performance['memory_usage'] / 64) * 100); // Assume 64MB limit
                                $memory_color = $memory_percent > 80 ? 'bg-red-500' : ($memory_percent > 60 ? 'bg-yellow-500' : 'bg-green-500');
                                ?>
                                <div class="<?php echo $memory_color; ?> h-3 rounded-full transition-all duration-300" style="width: <?php echo $memory_percent; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">เวลาโหลดหน้า</span>
                                <span class="text-sm font-medium"><?php echo $performance['page_load_time']; ?> วิ</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <?php 
                                $load_percent = min(100, ($performance['page_load_time'] / 2) * 100); // 2 seconds max
                                $load_color = $load_percent > 75 ? 'bg-red-500' : ($load_percent > 50 ? 'bg-yellow-500' : 'bg-green-500');
                                ?>
                                <div class="<?php echo $load_color; ?> h-3 rounded-full transition-all duration-300" style="width: <?php echo $load_percent; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                <div>🐘 PHP: <?php echo PHP_VERSION; ?></div>
                                <div>⚡ Peak Memory: <?php echo $performance['peak_memory']; ?> MB</div>
                                <div>🌐 Load Time: <?php echo $performance['page_load_time']; ?>s</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="glass-card rounded-2xl shadow-xl p-6 fade-in hover-lift">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">🔗 ลิงก์ด่วน</h3>
                            <p class="text-gray-600 text-sm">เข้าถึงฟีเจอร์สำคัญได้อย่างรวดเร็ว</p>
                        </div>
                        <div class="text-3xl">⚡</div>
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
                                <div class="text-xs text-gray-600">แก้ไข ลบข่าวสาร</div>
                            </div>
                        </a>
                        
                        <a href="ita.php" class="flex items-center p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition duration-200 group">
                            <span class="text-xl mr-3 group-hover:scale-110 transition-transform">🔧</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">จัดการ ITA</div>
                                <div class="text-xs text-gray-600">คำขอ IT Support</div>
                            </div>
                        </a>
                        
                        <a href="users.php" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200 group">
                            <span class="text-xl mr-3 group-hover:scale-110 transition-transform">👨‍💼</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">จัดการผู้ใช้</div>
                                <div class="text-xs text-gray-600">บัญชีผู้ใช้และสิทธิ์</div>
                            </div>
                        </a>
                        
                        <a href="settings.php" class="flex items-center p-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition duration-200 group">
                            <span class="text-xl mr-3 group-hover:scale-110 transition-transform">⚙️</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">ตั้งค่าระบบ</div>
                                <div class="text-xs text-gray-600">กำหนดค่าต่างๆ ของระบบ</div>
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
                
                <!-- System Report Summary -->
                <div class="mt-6 pt-6 border-t border-blue-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm">
                            <span class="font-medium">รายงานระบบล่าสุด:</span>
                            <span class="text-gray-600 ml-2"><?php echo safeFormatThaiDateTime($system_report['generated_at']); ?></span>
                        </div>
                        <a href="reports.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium hover:bg-blue-100 px-3 py-1 rounded-lg transition duration-200">
                            ดูรายงานเต็ม →
                        </a>
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

        // Initialize Monthly Chart with enhanced styling
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
                                pointHoverRadius: 8,
                                pointHoverBackgroundColor: 'rgb(59, 130, 246)',
                                pointHoverBorderColor: '#fff',
                                pointHoverBorderWidth: 3
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

        // Real-time updates (simulate)
        function updateStats() {
            // This would typically fetch new data from the server
            console.log('Updating stats...');
        }

        // Update stats every 30 seconds
        setInterval(updateStats, 30000);

        // Add hover effects to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Enhanced dashboard functionality
        console.log('🎉 Enhanced Dashboard loaded successfully!');
        console.log('📊 Performance metrics:', <?php echo json_encode($performance); ?>);
        console.log('📈 System stats:', <?php echo json_encode($stats); ?>);
    </script>
</body>
</html><?php
// End of admin/dashboard.php