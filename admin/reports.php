<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Require admin role
requireAdmin('../login.php');

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'reports';
$page_title = "รายงานและสถิติ";

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Date range filter
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today
$report_type = $_GET['report_type'] ?? 'overview';

// Validate dates
$start_date = date('Y-m-d', strtotime($start_date));
$end_date = date('Y-m-d', strtotime($end_date));

// Generate reports based on type
$report_data = [];

try {
    switch ($report_type) {
        case 'overview':
            $report_data = generateOverviewReport($conn, $start_date, $end_date);
            break;
        case 'news':
            $report_data = generateNewsReport($conn, $start_date, $end_date);
            break;
        case 'users':
            $report_data = generateUsersReport($conn, $start_date, $end_date);
            break;
        case 'ita':
            $report_data = generateITAReport($conn, $start_date, $end_date);
            break;
        case 'activity':
            $report_data = generateActivityReport($conn, $start_date, $end_date);
            break;
        default:
            $report_data = generateOverviewReport($conn, $start_date, $end_date);
    }
} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
    $report_data = ['error' => 'ไม่สามารถสร้างรายงานได้'];
}

// Helper functions for generating reports
function generateOverviewReport($conn, $start_date, $end_date) {
    $data = [];
    
    // Total statistics
    $data['total_users'] = getTotalUsers($conn);
    $data['total_news'] = getTotalNews($conn);
    
    // Try to get ITA total, if table doesn't exist, set to 0
    try {
        $data['total_ita'] = getTotalITA($conn);
    } catch (Exception $e) {
        $data['total_ita'] = 0;
    }
    
    // Period statistics
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM news 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        AND status = 'published'
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['news_period'] = $stmt->fetchColumn() ?? 0;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        AND is_active = 1
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['users_period'] = $stmt->fetchColumn() ?? 0;
    
    // Try to get ITA data
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM ita_requests 
            WHERE DATE(created_at) BETWEEN ? AND ? 
            AND status != 'deleted'
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['ita_period'] = $stmt->fetchColumn() ?? 0;
    } catch (Exception $e) {
        $data['ita_period'] = 0;
    }
    
    // Daily statistics
    $stmt = $conn->prepare("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM news 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        AND status = 'published'
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at)
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['daily_news'] = $stmt->fetchAll();
    
    return $data;
}

function generateNewsReport($conn, $start_date, $end_date) {
    $data = [];
    
    // News by status
    $stmt = $conn->prepare("
        SELECT status, COUNT(*) as count
        FROM news 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY status
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['by_status'] = $stmt->fetchAll();
    
    // News by author
    $stmt = $conn->prepare("
        SELECT u.first_name, u.last_name, COUNT(*) as count
        FROM news n
        JOIN users u ON n.author_id = u.id
        WHERE DATE(n.created_at) BETWEEN ? AND ?
        GROUP BY n.author_id, u.first_name, u.last_name
        ORDER BY count DESC
        LIMIT 10
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['by_author'] = $stmt->fetchAll();
    
    // Most viewed news (if views column exists)
    try {
        $stmt = $conn->prepare("
            SELECT title, views, created_at
            FROM news 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND status = 'published'
            ORDER BY views DESC
            LIMIT 10
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['most_viewed'] = $stmt->fetchAll();
    } catch (Exception $e) {
        // Views column doesn't exist
        $data['most_viewed'] = [];
    }
    
    // Featured vs regular
    $stmt = $conn->prepare("
        SELECT is_featured, COUNT(*) as count
        FROM news 
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND status = 'published'
        GROUP BY is_featured
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['featured_stats'] = $stmt->fetchAll();
    
    return $data;
}

function generateUsersReport($conn, $start_date, $end_date) {
    $data = [];
    
    // New users
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM users 
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND is_active = 1
    ");
    $stmt->execute([$start_date, $end_date]);
    $data['new_users'] = $stmt->fetchColumn() ?? 0;
    
    // Users by role
    $stmt = $conn->prepare("
        SELECT role, COUNT(*) as count
        FROM users 
        WHERE is_active = 1
        GROUP BY role
    ");
    $stmt->execute();
    $data['by_role'] = $stmt->fetchAll();
    
    // Users by department (if table exists)
    try {
        $stmt = $conn->prepare("
            SELECT d.name as department, COUNT(*) as count
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE u.is_active = 1
            GROUP BY u.department_id, d.name
            ORDER BY count DESC
        ");
        $stmt->execute();
        $data['by_department'] = $stmt->fetchAll();
    } catch (Exception $e) {
        $data['by_department'] = [];
    }
    
    // Login activity (if last_login column exists)
    try {
        $stmt = $conn->prepare("
            SELECT DATE(last_login) as date, COUNT(*) as count
            FROM users 
            WHERE DATE(last_login) BETWEEN ? AND ?
            AND is_active = 1
            GROUP BY DATE(last_login)
            ORDER BY DATE(last_login)
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['login_activity'] = $stmt->fetchAll();
    } catch (Exception $e) {
        $data['login_activity'] = [];
    }
    
    return $data;
}

function generateITAReport($conn, $start_date, $end_date) {
    $data = [];
    
    try {
        // ITA by status
        $stmt = $conn->prepare("
            SELECT status, COUNT(*) as count
            FROM ita_requests 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND status != 'deleted'
            GROUP BY status
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['by_status'] = $stmt->fetchAll();
        
        // ITA by priority
        $stmt = $conn->prepare("
            SELECT priority, COUNT(*) as count
            FROM ita_requests 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND status != 'deleted'
            GROUP BY priority
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['by_priority'] = $stmt->fetchAll();
        
        // ITA by department
        $stmt = $conn->prepare("
            SELECT department, COUNT(*) as count
            FROM ita_requests 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND status != 'deleted'
            GROUP BY department
            ORDER BY count DESC
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['by_department'] = $stmt->fetchAll();
        
        // Response time analysis
        $stmt = $conn->prepare("
            SELECT 
                AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_response_hours,
                MIN(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as min_response_hours,
                MAX(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as max_response_hours
            FROM ita_requests 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND status IN ('completed', 'rejected')
            AND updated_at IS NOT NULL
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['response_time'] = $stmt->fetch();
        
    } catch (Exception $e) {
        $data['error'] = 'ตาราง ITA ยังไม่ได้สร้าง';
    }
    
    return $data;
}

function generateActivityReport($conn, $start_date, $end_date) {
    $data = [];
    
    try {
        // Activity by action
        $stmt = $conn->prepare("
            SELECT action, COUNT(*) as count
            FROM activity_logs 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY action
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['by_action'] = $stmt->fetchAll();
        
        // Activity by user
        $stmt = $conn->prepare("
            SELECT u.first_name, u.last_name, COUNT(*) as count
            FROM activity_logs al
            JOIN users u ON al.user_id = u.id
            WHERE DATE(al.created_at) BETWEEN ? AND ?
            GROUP BY al.user_id, u.first_name, u.last_name
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['by_user'] = $stmt->fetchAll();
        
        // Daily activity
        $stmt = $conn->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM activity_logs 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at)
        ");
        $stmt->execute([$start_date, $end_date]);
        $data['daily_activity'] = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $data['error'] = 'ตาราง Activity Logs ยังไม่ได้สร้าง';
    }
    
    return $data;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .report-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.8) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white shadow-2xl sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                        <span class="text-white font-bold text-xl">📊</span>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold">รายงานและสถิติ</h1>
                        <p class="text-purple-200 text-sm">ข้อมูลวิเคราะห์และรายงานระบบ</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-purple-200"><?php echo date('d/m/Y H:i'); ?></p>
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
            <!-- Page Header -->
            <div class="mb-8 fade-in">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">📊 รายงานและสถิติ</h2>
                <p class="text-gray-200">ข้อมูลวิเคราะห์และสถิติการใช้งานระบบ</p>
            </div>

            <!-- Report Filters -->
            <div class="glass-card rounded-2xl p-6 mb-8 fade-in">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📅 วันที่เริ่มต้น</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📅 วันที่สิ้นสุด</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📋 ประเภทรายงาน</label>
                        <select name="report_type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="overview" <?php echo $report_type === 'overview' ? 'selected' : ''; ?>>ภาพรวม</option>
                            <option value="news" <?php echo $report_type === 'news' ? 'selected' : ''; ?>>ข่าวสาร</option>
                            <option value="users" <?php echo $report_type === 'users' ? 'selected' : ''; ?>>ผู้ใช้งาน</option>
                            <option value="ita" <?php echo $report_type === 'ita' ? 'selected' : ''; ?>>ITA Requests</option>
                            <option value="activity" <?php echo $report_type === 'activity' ? 'selected' : ''; ?>>กิจกรรมระบบ</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                        <button type="submit" class="w-full bg-purple-600 text-white py-3 px-6 rounded-xl hover:bg-purple-700 transition duration-300 font-medium">
                            📊 สร้างรายงาน
                        </button>
                    </div>
                </form>
            </div>

            <!-- Report Content -->
            <div class="space-y-8">
                <?php if (isset($report_data['error'])): ?>
                <div class="glass-card rounded-2xl p-8 text-center fade-in">
                    <div class="text-6xl mb-4">⚠️</div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">ไม่สามารถสร้างรายงานได้</h3>
                    <p class="text-gray-600"><?php echo $report_data['error']; ?></p>
                </div>
                <?php else: ?>

                <!-- Overview Report -->
                <?php if ($report_type === 'overview'): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 fade-in">
                    <div class="report-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">👥 ผู้ใช้งาน</h3>
                            <span class="text-3xl">👥</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">ทั้งหมด:</span>
                                <span class="font-bold text-blue-600"><?php echo number_format($report_data['total_users']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">ใหม่ (ช่วงนี้):</span>
                                <span class="font-bold text-green-600"><?php echo number_format($report_data['users_period']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">📰 ข่าวสาร</h3>
                            <span class="text-3xl">📰</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">ทั้งหมด:</span>
                                <span class="font-bold text-blue-600"><?php echo number_format($report_data['total_news']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">ใหม่ (ช่วงนี้):</span>
                                <span class="font-bold text-green-600"><?php echo number_format($report_data['news_period']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-card rounded-2xl p-6 hover-lift">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">🔧 ITA Requests</h3>
                            <span class="text-3xl">🔧</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">ทั้งหมด:</span>
                                <span class="font-bold text-blue-600"><?php echo number_format($report_data['total_ita']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">ใหม่ (ช่วงนี้):</span>
                                <span class="font-bold text-green-600"><?php echo number_format($report_data['ita_period']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily News Chart -->
                <?php if (!empty($report_data['daily_news'])): ?>
                <div class="glass-card rounded-2xl p-6 fade-in">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">📈 ข่าวสารรายวัน</h3>
                    <div class="h-64">
                        <canvas id="dailyNewsChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- News Report -->
                <?php if ($report_type === 'news'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- News by Status -->
                    <?php if (!empty($report_data['by_status'])): ?>
                    <div class="glass-card rounded-2xl p-6 fade-in">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6">📊 ข่าวสารตามสถานะ</h3>
                        <div class="space-y-3">
                            <?php foreach ($report_data['by_status'] as $item): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-700 font-medium">
                                    <?php 
                                    $status_names = ['published' => 'เผยแพร่', 'draft' => 'ร่าง', 'archived' => 'เก็บถาวร'];
                                    echo $status_names[$item['status']] ?? $item['status'];
                                    ?>
                                </span>
                                <span class="font-bold text-blue-600"><?php echo number_format($item['count']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- News by Author -->
                    <?php if (!empty($report_data['by_author'])): ?>
                    <div class="glass-card rounded-2xl p-6 fade-in">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6">✍️ ข่าวสารตามผู้เขียน</h3>
                        <div class="space-y-3">
                            <?php foreach ($report_data['by_author'] as $item): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-700 font-medium">
                                    <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                </span>
                                <span class="font-bold text-green-600"><?php echo number_format($item['count']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Featured Stats -->
                    <?php if (!empty($report_data['featured_stats'])): ?>
                    <div class="glass-card rounded-2xl p-6 fade-in">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6">⭐ ข่าวเด่น vs ข่าวทั่วไป</h3>
                        <div class="space-y-3">
                            <?php foreach ($report_data['featured_stats'] as $item): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-700 font-medium">
                                    <?php echo $item['is_featured'] ? 'ข่าวเด่น' : 'ข่าวทั่วไป'; ?>
                                </span>
                                <span class="font-bold text-purple-600"><?php echo number_format($item['count']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Users Report -->
                <?php if ($report_type === 'users'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Users by Role -->
                    <?php if (!empty($report_data['by_role'])): ?>
                    <div class="glass-card rounded-2xl p-6 fade-in">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6">👤 ผู้ใช้ตามบทบาท</h3>
                        <div class="space-y-3">
                            <?php foreach ($report_data['by_role'] as $item): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-700 font-medium">
                                    <?php 
                                    $role_names = ['admin' => 'ผู้ดูแลระบบ', 'editor' => 'บรรณาธิการ', 'user' => 'ผู้ใช้ทั่วไป'];
                                    echo $role_names[$item['role']] ?? $item['role'];
                                    ?>
                                </span>
                                <span class="font-bold text-blue-600"><?php echo number_format($item['count']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- New Users in Period -->
                    <div class="glass-card rounded-2xl p-6 fade-in">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6">🆕 ผู้ใช้ใหม่ในช่วงเวลา</h3>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-green-600 mb-2">
                                <?php echo number_format($report_data['new_users']); ?>
                            </div>
                            <p class="text-gray-600">ผู้ใช้ใหม่</p>
                            <p class="text-sm text-gray-500 mt-2">
                                ตั้งแต่ <?php echo safeFormatThaiDate($start_date); ?> ถึง <?php echo safeFormatThaiDate($end_date); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Users by Department -->
                    <?php if (!empty($report_data['by_department'])): ?>
                    <div class="glass-card rounded-2xl p-6 fade-in">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6">🏢 ผู้ใช้ตามแผนก</h3>
                        <div class="space-y-3">
                            <?php foreach ($report_data['by_department'] as $item): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-700 font-medium">
                                    <?php echo htmlspecialchars($item['department'] ?: 'ไม่ระบุแผนก'); ?>
                                </span>
                                <span class="font-bold text-purple-600"><?php echo number_format($item['count']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- ITA Report -->
                <?php if ($report_type === 'ita'): ?>
                    <?php if (isset($report_data['error'])): ?>
                    <div class="glass-card rounded-2xl p-8 text-center fade-in">
                        <div class="text-6xl mb-4">🔧</div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">ยังไม่มีข้อมูล ITA</h3>
                        <p class="text-gray-600"><?php echo $report_data['error']; ?></p>
                        <p class="text-sm text-gray-500 mt-2">กรุณาสร้างตาราง ITA หรือเลือกรายงานประเภทอื่น</p>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- ITA by Status -->
                        <?php if (!empty($report_data['by_status'])): ?>
                        <div class="glass-card rounded-2xl p-6 fade-in">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6">📊 ITA ตามสถานะ</h3>
                            <div class="space-y-3">
                                <?php foreach ($report_data['by_status'] as $item): ?>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($item['status']); ?></span>
                                    <span class="font-bold text-blue-600"><?php echo number_format($item['count']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ITA by Priority -->
                        <?php if (!empty($report_data['by_priority'])): ?>
                        <div class="glass-card rounded-2xl p-6 fade-in">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6">⚡ ITA ตามความสำคัญ</h3>
                            <div class="space-y-3">
                                <?php foreach ($report_data['by_priority'] as $item): ?>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($item['priority']); ?></span>
                                    <span class="font-bold text-orange-600"><?php echo number_format($item['count']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Activity Report -->
                <?php if ($report_type === 'activity'): ?>
                    <?php if (isset($report_data['error'])): ?>
                    <div class="glass-card rounded-2xl p-8 text-center fade-in">
                        <div class="text-6xl mb-4">📝</div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">ยังไม่มีข้อมูลกิจกรรม</h3>
                        <p class="text-gray-600"><?php echo $report_data['error']; ?></p>
                        <p class="text-sm text-gray-500 mt-2">กรุณาสร้างตาราง Activity Logs หรือเลือกรายงานประเภทอื่น</p>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Activity by Action -->
                        <?php if (!empty($report_data['by_action'])): ?>
                        <div class="glass-card rounded-2xl p-6 fade-in">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6">🎯 กิจกรรมตามประเภท</h3>
                            <div class="space-y-3">
                                <?php foreach ($report_data['by_action'] as $item): ?>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($item['action']); ?></span>
                                    <span class="font-bold text-blue-600"><?php echo number_format($item['count']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Activity by User -->
                        <?php if (!empty($report_data['by_user'])): ?>
                        <div class="glass-card rounded-2xl p-6 fade-in">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6">👥 กิจกรรมตามผู้ใช้</h3>
                            <div class="space-y-3">
                                <?php foreach ($report_data['by_user'] as $item): ?>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-700 font-medium">
                                        <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                    </span>
                                    <span class="font-bold text-green-600"><?php echo number_format($item['count']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php endif; ?>
            </div>

            <!-- Export Options -->
            <div class="glass-card rounded-2xl p-6 mt-8 fade-in">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📤 ส่งออกรายงาน</h3>
                <div class="flex flex-wrap gap-4">
                    <button onclick="exportToPDF()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200 flex items-center space-x-2">
                        <span>📄</span>
                        <span>ส่งออก PDF</span>
                    </button>
                    <button onclick="exportToExcel()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200 flex items-center space-x-2">
                        <span>📊</span>
                        <span>ส่งออก Excel</span>
                    </button>
                    <button onclick="printReport()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center space-x-2">
                        <span>🖨️</span>
                        <span>พิมพ์รายงาน</span>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Daily News Chart
            const dailyNewsCtx = document.getElementById('dailyNewsChart');
            if (dailyNewsCtx) {
                const dailyNewsData = <?php echo json_encode($report_data['daily_news'] ?? []); ?>;
                
                if (dailyNewsData.length > 0) {
                    new Chart(dailyNewsCtx, {
                        type: 'line',
                        data: {
                            labels: dailyNewsData.map(item => {
                                const date = new Date(item.date);
                                return date.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
                            }),
                            datasets: [{
                                label: 'จำนวนข่าวสาร',
                                data: dailyNewsData.map(item => item.count),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgb(59, 130, 246)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 6
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
                                    cornerRadius: 8
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
                            }
                        }
                    });
                }
            }
        });

        // Export Functions
        function exportToPDF() {
            // Simple implementation - could be enhanced with a PDF library
            window.print();
        }

        function exportToExcel() {
            // Simple CSV export
            const reportType = '<?php echo $report_type; ?>';
            const startDate = '<?php echo $start_date; ?>';
            const endDate = '<?php echo $end_date; ?>';
            
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += `รายงาน${reportType},${startDate} ถึง ${endDate}\n`;
            
            // Add report data based on type
            const reportData = <?php echo json_encode($report_data); ?>;
            
            if (reportType === 'overview') {
                csvContent += "ประเภท,จำนวน\n";
                csvContent += `ผู้ใช้ทั้งหมด,${reportData.total_users}\n`;
                csvContent += `ข่าวสารทั้งหมด,${reportData.total_news}\n`;
                csvContent += `ITA ทั้งหมด,${reportData.total_ita}\n`;
            }
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `report_${reportType}_${startDate}_${endDate}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printReport() {
            window.print();
        }

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('refresh', Date.now());
            
            // Only refresh if user is still on the page
            if (document.visibilityState === 'visible') {
                fetch(currentUrl.toString())
                    .then(response => response.text())
                    .then(html => {
                        // Update only the report content area
                        const parser = new DOMParser();
                        const newDoc = parser.parseFromString(html, 'text/html');
                        const newContent = newDoc.querySelector('main');
                        if (newContent) {
                            console.log('รีเฟรชข้อมูลรายงานแล้ว');
                        }
                    })
                    .catch(error => console.log('ไม่สามารถรีเฟรชข้อมูลได้:', error));
            }
        }, 300000); // 5 minutes

        // Date validation
        document.querySelector('input[name="start_date"]').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDateInput = document.querySelector('input[name="end_date"]');
            const endDate = new Date(endDateInput.value);
            
            if (startDate > endDate) {
                endDateInput.value = this.value;
            }
        });

        document.querySelector('input[name="end_date"]').addEventListener('change', function() {
            const endDate = new Date(this.value);
            const startDateInput = document.querySelector('input[name="start_date"]');
            const startDate = new Date(startDateInput.value);
            
            if (endDate < startDate) {
                startDateInput.value = this.value;
            }
        });

        console.log('📊 Reports system loaded successfully!');
    </script>
</body>
</html>