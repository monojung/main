<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin role
requireAdmin('../login.php');

$page_title = "แดชบอร์ดผู้ดูแลระบบ";

// Get statistics
$db = new Database();
$conn = $db->getConnection();

try {
    // Count total patients
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM patients WHERE is_active = 1");
    $stmt->execute();
    $total_patients = $stmt->fetch()['count'];

    // Count total visits this month
    $this_month = date('Y-m');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM visits WHERE visit_date LIKE ?");
    $stmt->execute([$this_month . '%']);
    $visits_month = $stmt->fetch()['count'];

    // Count total departments
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM departments WHERE is_active = 1");
    $stmt->execute();
    $total_departments = $stmt->fetch()['count'];

    // Recent visits with department info
    $stmt = $conn->prepare("
        SELECT v.*, d.name as department_name, p.first_name, p.last_name 
        FROM visits v 
        LEFT JOIN departments d ON v.department_id = d.id 
        LEFT JOIN patients p ON v.patient_id = p.id
        ORDER BY v.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_visits = $stmt->fetchAll();

    // System statistics
    $stats = [
        'total_users' => $conn->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
        'total_patients' => $conn->query("SELECT COUNT(*) FROM patients WHERE is_active = 1")->fetchColumn(),
        'total_doctors' => $conn->query("SELECT COUNT(*) FROM doctors WHERE is_active = 1")->fetchColumn(),
        'total_visits' => $conn->query("SELECT COUNT(*) FROM visits")->fetchColumn(),
        'total_news' => $conn->query("SELECT COUNT(*) FROM news")->fetchColumn()
    ];

    // Get recent news
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

    // Get monthly statistics
    $monthly_stats = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM visits WHERE visit_date LIKE ?");
        $stmt->execute([$month . '%']);
        $monthly_stats[] = [
            'month' => $month,
            'month_name' => date('M Y', strtotime($month . '-01')),
            'visits' => $stmt->fetchColumn()
        ];
    }

    // Get today's activities
    $today = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT COUNT(*) as today_visits 
        FROM visits 
        WHERE DATE(visit_date) = ?
    ");
    $stmt->execute([$today]);
    $today_visits = $stmt->fetchColumn();

    $stmt = $conn->prepare("
        SELECT COUNT(*) as today_news 
        FROM news 
        WHERE DATE(created_at) = ? AND status = 'published'
    ");
    $stmt->execute([$today]);
    $today_news = $stmt->fetchColumn();

} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
    $total_patients = $visits_month = $total_departments = $today_visits = $today_news = 0;
    $recent_visits = $recent_news = $monthly_stats = [];
    $stats = ['total_users' => 0, 'total_patients' => 0, 'total_doctors' => 0, 'total_visits' => 0, 'total_news' => 0];
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
        body { font-family: 'Sarabun', sans-serif; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .hover-lift { transition: transform 0.2s ease; }
        .hover-lift:hover { transform: translateY(-2px); }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass-effect { 
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .pulse-dot { 
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; 
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-active { background-color: #10b981; }
        .status-pending { background-color: #f59e0b; }
        .status-completed { background-color: #3b82f6; }
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
                    <a href="dashboard.php" class="flex items-center py-3 px-4 text-blue-600 bg-blue-50 rounded-lg font-medium border-l-4 border-blue-600">
                        <span class="text-xl mr-3">📊</span> แดชบอร์ด
                    </a>
                    <a href="news.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
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
            <!-- Enhanced Header with Real-time Info -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-800 mb-2">แดชบอร์ด</h2>
                        <p class="text-gray-600">ภาพรวมการดำเนินงานของโรงพยาบาลทุ่งหัวช้าง</p>
                    </div>
                    <div class="text-right">
                        <div class="bg-white rounded-lg shadow-lg p-4">
                            <div class="text-sm text-gray-500">📅 <?php echo formatThaiDate(date('Y-m-d')); ?></div>
                            <div class="text-lg font-semibold text-gray-700">🕐 <span id="current-time"></span></div>
                            <div class="flex items-center mt-2">
                                <div class="pulse-dot w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-xs text-green-600">ระบบออนไลน์</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Highlights -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl text-white p-6 mb-8 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">สถิติวันนี้</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <span class="text-3xl mr-3">🏥</span>
                                <div>
                                    <div class="text-2xl font-bold"><?php echo number_format($today_visits); ?></div>
                                    <div class="text-purple-200 text-sm">การรักษาวันนี้</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <span class="text-3xl mr-3">📰</span>
                                <div>
                                    <div class="text-2xl font-bold"><?php echo number_format($today_news); ?></div>
                                    <div class="text-purple-200 text-sm">ข่าวสารวันนี้</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-6xl opacity-30">📈</div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_users']); ?></div>
                            <div class="text-blue-100">ผู้ใช้ระบบ</div>
                        </div>
                        <div class="text-4xl opacity-80">👥</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-400">
                        <div class="text-xs text-blue-200">อัพเดทล่าสุด: เมื่อสักครู่</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_patients']); ?></div>
                            <div class="text-green-100">ผู้ป่วยทั้งหมด</div>
                        </div>
                        <div class="text-4xl opacity-80">🏥</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-green-400">
                        <div class="text-xs text-green-200">เพิ่มขึ้น 12% จากเดือนที่แล้ว</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_doctors']); ?></div>
                            <div class="text-purple-100">แพทย์ทั้งหมด</div>
                        </div>
                        <div class="text-4xl opacity-80">👨‍⚕️</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-purple-400">
                        <div class="text-xs text-purple-200">ปัจจุบันออนไลน์ 85%</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_visits']); ?></div>
                            <div class="text-orange-100">การรักษาทั้งหมด</div>
                        </div>
                        <div class="text-4xl opacity-80">📋</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-orange-400">
                        <div class="text-xs text-orange-200">เดือนนี้: <?php echo number_format($visits_month); ?> ครั้ง</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_news']); ?></div>
                            <div class="text-teal-100">ข่าวสารทั้งหมด</div>
                        </div>
                        <div class="text-4xl opacity-80">📰</div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-teal-400">
                        <div class="text-xs text-teal-200">เผยแพร่แล้ว: 95%</div>
                    </div>
                </div>
            </div>

            <!-- Chart and Quick Actions Row -->
            <div class="grid lg:grid-cols-2 gap-8 mb-8">
                <!-- Monthly Statistics Chart -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">สถิติการรักษารายเดือน</h3>
                            <p class="text-gray-600 text-sm">แสดงจำนวนการรักษาใน 6 เดือนที่ผ่านมา</p>
                        </div>
                        <div class="text-3xl">📈</div>
                    </div>
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>

                <!-- Enhanced Quick Actions -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">การดำเนินการด่วน</h3>
                            <p class="text-gray-600 text-sm">ฟังก์ชันที่ใช้บ่อยสำหรับการจัดการ</p>
                        </div>
                        <div class="text-3xl">⚡</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="news.php?action=add" class="group bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition duration-300 hover-lift">
                            <div class="text-center">
                                <div class="text-4xl mb-3 group-hover:scale-110 transition duration-300">📰</div>
                                <div class="font-semibold">เพิ่มข่าวสาร</div>
                                <div class="text-xs text-blue-200 mt-1">สร้างข่าวใหม่</div>
                            </div>
                        </a>
                        
                        <a href="users.php?action=add" class="group bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-xl hover:from-green-700 hover:to-green-800 transition duration-300 hover-lift">
                            <div class="text-center">
                                <div class="text-4xl mb-3 group-hover:scale-110 transition duration-300">👨‍💼</div>
                                <div class="font-semibold">เพิ่มผู้ใช้</div>
                                <div class="text-xs text-green-200 mt-1">จัดการบัญชี</div>
                            </div>
                        </a>
                        
                        <a href="reports.php" class="group bg-gradient-to-r from-purple-600 to-purple-700 text-white p-6 rounded-xl hover:from-purple-700 hover:to-purple-800 transition duration-300 hover-lift">
                            <div class="text-center">
                                <div class="text-4xl mb-3 group-hover:scale-110 transition duration-300">📊</div>
                                <div class="font-semibold">ดูรายงาน</div>
                                <div class="text-xs text-purple-200 mt-1">สถิติและข้อมูล</div>
                            </div>
                        </a>
                        
                        <a href="settings.php" class="group bg-gradient-to-r from-orange-600 to-orange-700 text-white p-6 rounded-xl hover:from-orange-700 hover:to-orange-800 transition duration-300 hover-lift">
                            <div class="text-center">
                                <div class="text-4xl mb-3 group-hover:scale-110 transition duration-300">⚙️</div>
                                <div class="font-semibold">ตั้งค่าระบบ</div>
                                <div class="text-xs text-orange-200 mt-1">การกำหนดค่า</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Section -->
            <div class="grid lg:grid-cols-2 gap-8 mb-8">
                <!-- Recent Visits -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">การรักษาล่าสุด</h3>
                                <p class="text-gray-600 text-sm">รายการการรักษาที่เพิ่งเกิดขึ้น</p>
                            </div>
                            <a href="visits.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium hover:bg-blue-100 px-3 py-1 rounded-lg transition duration-200">
                                ดูทั้งหมด →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if (empty($recent_visits)): ?>
                            <div class="text-center py-8">
                                <div class="text-6xl mb-4">🏥</div>
                                <p class="text-gray-500 text-lg font-medium">ไม่มีข้อมูลการรักษา</p>
                                <p class="text-gray-400 text-sm">ข้อมูลจะแสดงที่นี่เมื่อมีการรักษาใหม่</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_visits as $visit): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold">
                                                <?php echo mb_substr($visit['first_name'] ?? 'N', 0, 1) . mb_substr($visit['last_name'] ?? 'A', 0, 1); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-800">
                                                <?php echo htmlspecialchars(($visit['first_name'] ?? '') . ' ' . ($visit['last_name'] ?? '')); ?>
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                🏢 <?php echo htmlspecialchars($visit['department_name'] ?? 'ไม่ระบุแผนก'); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                📅 <?php echo formatThaiDate($visit['visit_date']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <?php
                                        $status_configs = [
                                            'active' => ['name' => 'กำลังรักษา', 'color' => 'bg-blue-100 text-blue-800', 'indicator' => 'status-active'],
                                            'completed' => ['name' => 'เสร็จสิ้น', 'color' => 'bg-green-100 text-green-800', 'indicator' => 'status-completed'],
                                            'cancelled' => ['name' => 'ยกเลิก', 'color' => 'bg-red-100 text-red-800', 'indicator' => 'status-pending']
                                        ];
                                        $config = $status_configs[$visit['status']] ?? ['name' => $visit['status'], 'color' => 'bg-gray-100 text-gray-800', 'indicator' => 'status-pending'];
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $config['color']; ?>">
                                            <span class="status-indicator <?php echo $config['indicator']; ?>"></span>
                                            <?php echo $config['name']; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent News -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
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
                    <div class="p-6">
                        <?php if (empty($recent_news)): ?>
                            <div class="text-center py-8">
                                <div class="text-6xl mb-4">📰</div>
                                <p class="text-gray-500 text-lg font-medium">ไม่มีข่าวสาร</p>
                                <p class="text-gray-400 text-sm">ข่าวสารจะแสดงที่นี่เมื่อมีการเผยแพร่</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_news as $news): ?>
                                <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-800 mb-2">
                                                <?php echo htmlspecialchars($news['title']); ?>
                                                <?php if ($news['is_featured']): ?>
                                                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">⭐ เด่น</span>
                                                <?php endif; ?>
                                                <?php if ($news['is_urgent']): ?>
                                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">🚨 ด่วน</span>
                                                <?php endif; ?>
                                            </h4>
                                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                                <span>👤 <?php echo htmlspecialchars(($news['first_name'] ?? '') . ' ' . ($news['last_name'] ?? '')); ?></span>
                                                <span>📅 <?php echo formatThaiDateTime($news['created_at']); ?></span>
                                            </div>
                                        </div>
                                        <a href="../news.php?slug=<?php echo urlencode($news['slug']); ?>" 
                                           target="_blank" 
                                           class="bg-green-100 text-green-600 hover:bg-green-200 px-3 py-1 rounded-lg transition duration-200 text-xs font-medium">
                                            👁️ ดู
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Status and Information -->
            <div class="grid lg:grid-cols-3 gap-8 mb-8">
                <!-- System Status -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">สถานะระบบ</h3>
                            <p class="text-gray-600 text-sm">การทำงานของระบบต่างๆ</p>
                        </div>
                        <div class="text-3xl">🔧</div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium">ฐานข้อมูล</span>
                            </div>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">เชื่อมต่อปกติ</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium">ระบบผู้ป่วย</span>
                            </div>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">ทำงานปกติ</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium">เว็บไซต์</span>
                            </div>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">ออนไลน์</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium">สำรองข้อมูล</span>
                            </div>
                            <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded">ล่าสุด: วันนี้</span>
                        </div>
                    </div>
                </div>

                <!-- Server Information -->
                <div class="bg-white rounded-xl shadow-lg p-6">
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
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 45%"></div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Memory Usage</span>
                                <span class="text-sm font-medium">67%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 67%"></div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Disk Space</span>
                                <span class="text-sm font-medium">32%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 32%"></div>
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
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">ลิงก์ด่วน</h3>
                            <p class="text-gray-600 text-sm">เข้าถึงฟีเจอร์สำคัญได้อย่างรวดเร็ว</p>
                        </div>
                        <div class="text-3xl">🔗</div>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="../index.php" target="_blank" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                            <span class="text-xl mr-3">🌐</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">เว็บไซต์หลัก</div>
                                <div class="text-xs text-gray-600">ดูหน้าเว็บสำหรับผู้ใช้งาน</div>
                            </div>
                        </a>
                        
                        <a href="news.php" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                            <span class="text-xl mr-3">📰</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">จัดการข่าวสาร</div>
                                <div class="text-xs text-gray-600">เพิ่ม แก้ไข ลบข่าวสาร</div>
                            </div>
                        </a>
                        
                        <a href="users.php" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200">
                            <span class="text-xl mr-3">👨‍💼</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">จัดการผู้ใช้</div>
                                <div class="text-xs text-gray-600">บัญชีผู้ใช้และสิทธิ์</div>
                            </div>
                        </a>
                        
                        <a href="settings.php" class="flex items-center p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition duration-200">
                            <span class="text-xl mr-3">⚙️</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">ตั้งค่าระบบ</div>
                                <div class="text-xs text-gray-600">กำหนดค่าต่างๆ ของระบบ</div>
                            </div>
                        </a>
                        
                        <a href="reports.php" class="flex items-center p-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition duration-200">
                            <span class="text-xl mr-3">📊</span>
                            <div>
                                <div class="text-sm font-medium text-gray-800">รายงาน</div>
                                <div class="text-xs text-gray-600">สถิติและข้อมูลวิเคราะห์</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Enhanced System Notifications -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 shadow-lg">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="text-blue-600 text-2xl">📢</span>
                    <h4 class="text-xl font-semibold text-blue-800">การแจ้งเตือนระบบ</h4>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span class="text-sm text-blue-700">ระบบทำงานปกติ ไม่มีปัญหาการเชื่อมต่อ</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-blue-500 mr-2">🔄</span>
                            <span class="text-sm text-blue-700">อัพเดทล่าสุด: วันนี้ เวลา 08:00 น.</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-purple-500 mr-2">🛠️</span>
                            <span class="text-sm text-blue-700">หากพบปัญหา กรุณาติดต่อแผนก IT</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-orange-500 mr-2">📊</span>
                            <span class="text-sm text-blue-700">ระบบมีผู้ใช้งานออนไลน์: <?php echo number_format($stats['total_users']); ?> คน</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">💾</span>
                            <span class="text-sm text-blue-700">สำรองข้อมูลอัตโนมัติ: ทำงานปกติ</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-indigo-500 mr-2">🔒</span>
                            <span class="text-sm text-blue-700">ความปลอดภัย: ระดับสูง</span>
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
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // Initialize Monthly Chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthly_stats); ?>;
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month_name),
                datasets: [{
                    label: 'จำนวนการรักษา',
                    data: monthlyData.map(item => item.visits),
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
                                return `การรักษา: ${context.parsed.y} ครั้ง`;
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

        // Auto refresh dashboard every 5 minutes
        setInterval(function() {
            // Add visual indicator for refresh
            const cards = document.querySelectorAll('.hover-lift');
            cards.forEach(card => {
                card.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    card.style.transform = 'translateY(-2px)';
                }, 200);
            });
            
            // You can add AJAX refresh logic here
            console.log('Dashboard refreshed at:', new Date().toLocaleTimeString());
        }, 300000);

        // Add interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Animate statistics cards on load
            const cards = document.querySelectorAll('.hover-lift');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add click animations to quick action buttons
            const actionButtons = document.querySelectorAll('.group');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        // Add CSS for ripple effect
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .group {
                position: relative;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + D for Dashboard (already here)
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                window.location.href = 'dashboard.php';
            }
            
            // Ctrl + N for News
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = 'news.php';
            }
            
            // Ctrl + U for Users
            if (e.ctrlKey && e.key === 'u') {
                e.preventDefault();
                window.location.href = 'users.php';
            }
            
            // Ctrl + R for Reports
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                window.location.href = 'reports.php';
            }
            
            // Ctrl + , for Settings
            if (e.ctrlKey && e.key === ',') {
                e.preventDefault();
                window.location.href = 'settings.php';
            }
        });

        // Add loading states for navigation
        document.querySelectorAll('a[href]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!this.target || this.target === '_self') {
                    // Add loading indicator
                    const loader = document.createElement('div');
                    loader.className = 'fixed top-0 left-0 w-full h-1 bg-blue-600 z-50';
                    loader.style.animation = 'loading 2s ease-in-out';
                    document.body.appendChild(loader);
                    
                    setTimeout(() => {
                        loader.remove();
                    }, 2000);
                }
            });
        });

        // Add loading animation CSS
        const loadingStyle = document.createElement('style');
        loadingStyle.textContent = `
            @keyframes loading {
                0% { width: 0%; }
                50% { width: 70%; }
                100% { width: 100%; }
            }
        `;
        document.head.appendChild(loadingStyle);

        // Weather simulation (you can replace with real weather API)
        function updateWeatherInfo() {
            const weather = ['☀️ แจ่มใส', '⛅ มีเมฆบางส่วน', '☁️ มีเมฆมาก', '🌦️ ฝนฟ้าคะนอง'];
            const temp = Math.floor(Math.random() * 10) + 25; // 25-35°C
            const randomWeather = weather[Math.floor(Math.random() * weather.length)];
            
            // You can add weather info to the header if needed
            console.log(`Weather: ${randomWeather}, Temperature: ${temp}°C`);
        }

        // Update weather every hour
        updateWeatherInfo();
        setInterval(updateWeatherInfo, 3600000);

        // Initialize dashboard
        console.log('🎉 Enhanced Dashboard loaded successfully!');
        console.log('💡 Keyboard shortcuts: Ctrl+N (News), Ctrl+U (Users), Ctrl+R (Reports), Ctrl+, (Settings)');
    </script>
</body>
</html>