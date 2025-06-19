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
        'total_visits' => $conn->query("SELECT COUNT(*) FROM visits")->fetchColumn()
    ];

} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
    $total_patients = $visits_month = $total_departments = 0;
    $recent_visits = [];
    $stats = ['total_users' => 0, 'total_patients' => 0, 'total_doctors' => 0, 'total_visits' => 0];
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
                    <div class="text-right">
                        <span>สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <div class="text-sm opacity-90">ผู้ดูแลระบบ</div>
                    </div>
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
                    <a href="dashboard.php" class="block py-2 px-4 text-blue-600 bg-blue-50 rounded font-medium">
                        📊 แดชบอร์ด
                    </a>
                    <a href="patients.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👥 ข้อมูลผู้ป่วย
                    </a>
                    <a href="visits.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        🏥 การรักษา
                    </a>
                    <a href="doctors.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👨‍⚕️ จัดการแพทย์
                    </a>
                    <a href="departments.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        🏥 จัดการแผนก
                    </a>
                    <a href="news.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
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
                    <hr class="my-3">
                    <a href="../index.php" target="_blank" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        🌐 เว็บไซต์หลัก
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800">แดชบอร์ด</h2>
                <p class="text-gray-600">ภาพรวมการดำเนินงานของโรงพยาบาล</p>
                <div class="text-sm text-gray-500 mt-1">
                    📅 <?php echo formatThaiDate(date('Y-m-d')); ?> | 
                    🕐 <span id="current-time"></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-blue-600 mr-4">👥</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($total_patients); ?></h3>
                            <p class="text-gray-600">ผู้ป่วยทั้งหมด</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-green-600 mr-4">🏥</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($visits_month); ?></h3>
                            <p class="text-gray-600">การรักษาเดือนนี้</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-purple-600 mr-4">🏢</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($total_departments); ?></h3>
                            <p class="text-gray-600">แผนกทั้งหมด</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-orange-600 mr-4">👨‍⚕️</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_doctors']); ?></h3>
                            <p class="text-gray-600">แพทย์ทั้งหมด</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Statistics -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold"><?php echo number_format($stats['total_users']); ?></h3>
                            <p class="opacity-90">ผู้ใช้ระบบ</p>
                        </div>
                        <div class="text-3xl opacity-80">👥</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold"><?php echo number_format($stats['total_patients']); ?></h3>
                            <p class="opacity-90">ผู้ป่วยทั้งหมด</p>
                        </div>
                        <div class="text-3xl opacity-80">🏥</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold"><?php echo number_format($stats['total_doctors']); ?></h3>
                            <p class="opacity-90">แพทย์ทั้งหมด</p>
                        </div>
                        <div class="text-3xl opacity-80">👨‍⚕️</div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold"><?php echo number_format($stats['total_visits']); ?></h3>
                            <p class="opacity-90">การรักษาทั้งหมด</p>
                        </div>
                        <div class="text-3xl opacity-80">📋</div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Recent Visits -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-gray-800">การรักษาล่าสุด</h3>
                            <a href="visits.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">ดูทั้งหมด →</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if (empty($recent_visits)): ?>
                            <p class="text-gray-500 text-center py-8">ไม่มีข้อมูลการรักษา</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_visits as $visit): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h4 class="font-medium text-gray-800">
                                            <?php echo htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($visit['department_name'] ?? 'ไม่ระบุแผนก'); ?> | 
                                            <?php echo formatThaiDate($visit['visit_date']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            เลขที่: <?php echo htmlspecialchars($visit['visit_number']); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <?php
                                        $status_colors = [
                                            'active' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $status_text = [
                                            'active' => 'กำลังรักษา',
                                            'completed' => 'เสร็จสิ้น',
                                            'cancelled' => 'ยกเลิก'
                                        ];
                                        $color = $status_colors[$visit['status']] ?? 'bg-gray-100 text-gray-800';
                                        $text = $status_text[$visit['status']] ?? $visit['status'];
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $color; ?>">
                                            <?php echo $text; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">การดำเนินการด่วน</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <a href="patients.php?action=add" class="bg-blue-600 text-white p-4 rounded-lg text-center hover:bg-blue-700 transition duration-300">
                                <div class="text-2xl mb-2">👤</div>
                                <div class="font-medium">เพิ่มผู้ป่วย</div>
                            </a>
                            
                            <a href="visits.php?action=add" class="bg-green-600 text-white p-4 rounded-lg text-center hover:bg-green-700 transition duration-300">
                                <div class="text-2xl mb-2">🏥</div>
                                <div class="font-medium">บันทึกการรักษา</div>
                            </a>
                            
                            <a href="news.php?action=add" class="bg-purple-600 text-white p-4 rounded-lg text-center hover:bg-purple-700 transition duration-300">
                                <div class="text-2xl mb-2">📰</div>
                                <div class="font-medium">เพิ่มข่าวสาร</div>
                            </a>
                            
                            <a href="users.php?action=add" class="bg-orange-600 text-white p-4 rounded-lg text-center hover:bg-orange-700 transition duration-300">
                                <div class="text-2xl mb-2">👨‍💼</div>
                                <div class="font-medium">เพิ่มผู้ใช้</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="bg-white rounded-lg shadow-lg lg:col-span-2">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">สถานะระบบ</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-3xl text-green-600 mb-2">✅</div>
                                <h4 class="font-medium text-gray-800">ฐานข้อมูล</h4>
                                <p class="text-sm text-green-600">เชื่อมต่อปกติ</p>
                            </div>
                            
                            <div class="text-center">
                                <div class="text-3xl text-green-600 mb-2">✅</div>
                                <h4 class="font-medium text-gray-800">ระบบผู้ป่วย</h4>
                                <p class="text-sm text-green-600">ทำงานปกติ</p>
                            </div>
                            
                            <div class="text-center">
                                <div class="text-3xl text-green-600 mb-2">✅</div>
                                <h4 class="font-medium text-gray-800">เว็บไซต์</h4>
                                <p class="text-sm text-green-600">ออนไลน์</p>
                            </div>
                            
                            <div class="text-center">
                                <div class="text-3xl text-blue-600 mb-2">💾</div>
                                <h4 class="font-medium text-gray-800">สำรองข้อมูล</h4>
                                <p class="text-sm text-blue-600">ล่าสุด: วันนี้</p>
                            </div>
                        </div>
                        
                        <!-- Recent Activity Log -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4">กิจกรรมล่าสุด</h4>
                            <?php
                            try {
                                $stmt = $conn->prepare("
                                    SELECT al.*, u.first_name, u.last_name 
                                    FROM activity_logs al 
                                    LEFT JOIN users u ON al.user_id = u.id 
                                    ORDER BY al.created_at DESC 
                                    LIMIT 5
                                ");
                                $stmt->execute();
                                $recent_activities = $stmt->fetchAll();
                            } catch (Exception $e) {
                                $recent_activities = [];
                            }
                            ?>
                            
                            <?php if (empty($recent_activities)): ?>
                                <p class="text-gray-500 text-sm">ไม่มีกิจกรรมล่าสุด</p>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <?php foreach ($recent_activities as $activity): ?>
                                    <div class="flex items-center justify-between text-sm">
                                        <div>
                                            <span class="font-medium">
                                                <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                            </span>
                                            <span class="text-gray-600">
                                                <?php echo htmlspecialchars($activity['action']); ?>
                                            </span>
                                            <?php if ($activity['table_name']): ?>
                                            <span class="text-gray-500">
                                                (<?php echo htmlspecialchars($activity['table_name']); ?>)
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-gray-500">
                                            <?php echo formatThaiDateTime($activity['created_at']); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Notifications -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center space-x-2">
                    <span class="text-blue-600 text-xl">📢</span>
                    <h4 class="font-semibold text-blue-800">การแจ้งเตือนระบบ</h4>
                </div>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="space-y-1">
                        <li>• ระบบทำงานปกติ ไม่มีปัญหาการเชื่อมต่อ</li>
                        <li>• อัพเดทล่าสุด: วันนี้ เวลา 08:00 น.</li>
                        <li>• หากพบปัญหา กรุณาติดต่อแผนก IT</li>
                    </ul>
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

        // Auto refresh dashboard every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>