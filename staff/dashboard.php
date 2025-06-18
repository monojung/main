<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Check if user is logged in
requireLogin('../login.php');

// Check role access (staff, doctor, nurse can access)
$allowed_roles = ['staff', 'doctor', 'nurse'];
requireRole($allowed_roles, '../login.php');

$page_title = "แดชบอร์ดเจ้าหน้าที่";

// Get user info
$db = new Database();
$conn = $db->getConnection();

try {
    // Get today's appointments for user's department
    $today = date('Y-m-d');
    $user_department = $_SESSION['department_id'];

    if ($user_department) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM appointments 
            WHERE appointment_date = ? AND department_id = ?
        ");
        $stmt->execute([$today, $user_department]);
        $dept_appointments_today = $stmt->fetch()['count'];

        // Get pending appointments for user's department
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM appointments 
            WHERE status = 'pending' AND department_id = ?
        ");
        $stmt->execute([$user_department]);
        $dept_pending_appointments = $stmt->fetch()['count'];

        // Get recent appointments for user's department
        $stmt = $conn->prepare("
            SELECT a.*, d.name as department_name 
            FROM appointments a 
            LEFT JOIN departments d ON a.department_id = d.id 
            WHERE a.department_id = ?
            ORDER BY a.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$user_department]);
        $recent_appointments = $stmt->fetchAll();

        // Get department info
        $stmt = $conn->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->execute([$user_department]);
        $department_info = $stmt->fetch();
        $department_name = $department_info['name'] ?? 'ไม่ระบุแผนก';
    } else {
        // User has no department assigned
        $dept_appointments_today = $dept_pending_appointments = 0;
        $recent_appointments = [];
        $department_name = 'ไม่ได้กำหนดแผนก';
    }

    // Get overall statistics for comparison
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ?");
    $stmt->execute([$today]);
    $total_appointments_today = $stmt->fetch()['count'];

} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
    $dept_appointments_today = $dept_pending_appointments = $total_appointments_today = 0;
    $recent_appointments = [];
    $department_name = 'เกิดข้อผิดพลาด';
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
    <nav class="bg-gradient-to-r from-blue-600 to-green-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">THC</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">ระบบจัดการโรงพยาบาลทุ่งหัวช้าง</h1>
                        <p class="text-sm opacity-90"><?php echo htmlspecialchars($department_name); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p class="text-sm opacity-90"><?php echo ucfirst($_SESSION['user_role']); ?></p>
                    </div>
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <span class="text-sm">👤</span>
                    </div>
                    <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded transition duration-300">
                        ออกจากระบบ
                    </a>
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
                    <a href="appointments.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📅 จัดการนัดหมาย
                    </a>
                    <?php if ($_SESSION['user_role'] === 'doctor'): ?>
                    <a href="patients.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👥 ข้อมูลผู้ป่วย
                    </a>
                    <a href="visits.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        🏥 การเข้ารับการรักษา
                    </a>
                    <?php endif; ?>
                    <a href="schedule.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📋 ตารางงาน
                    </a>
                    <a href="profile.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👨‍💼 โปรไฟล์
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
            <!-- Welcome Section -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-green-500 text-white rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-2">
                        สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋
                    </h2>
                    <p class="opacity-90">
                        ยินดีต้อนรับสู่ระบบจัดการโรงพยาบาล - <?php echo htmlspecialchars($department_name); ?>
                    </p>
                    <div class="mt-4 text-sm opacity-80">
                        📅 วันนี้: <?php echo formatThaiDate(date('Y-m-d')); ?> | 
                        🕐 เวลา: <span id="current-time"></span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-blue-600 mr-4">📅</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($dept_appointments_today); ?></h3>
                            <p class="text-gray-600">นัดหมายวันนี้</p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($department_name); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-yellow-600 mr-4">⏳</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($dept_pending_appointments); ?></h3>
                            <p class="text-gray-600">รอการยืนยัน</p>
                            <p class="text-xs text-gray-500">แผนกของคุณ</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-green-600 mr-4">👨‍⚕️</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo ucfirst($_SESSION['user_role']); ?></h3>
                            <p class="text-gray-600">บทบาทของคุณ</p>
                            <p class="text-xs text-gray-500">สิทธิ์การใช้งาน</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-purple-600 mr-4">🏥</div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($department_name); ?></h3>
                            <p class="text-gray-600">แผนกงาน</p>
                            <p class="text-xs text-green-600">● ออนไลน์</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Recent Appointments -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-gray-800">นัดหมายล่าสุด</h3>
                            <a href="appointments.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">ดูทั้งหมด →</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if (empty($recent_appointments)): ?>
                            <div class="text-center py-8">
                                <div class="text-4xl mb-2">📅</div>
                                <p class="text-gray-500">ไม่มีข้อมูลนัดหมาย</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach (array_slice($recent_appointments, 0, 5) as $appointment): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-300">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($appointment['patient_name']); ?></h4>
                                        <p class="text-sm text-gray-600">
                                            📞 <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            📅 <?php echo formatThaiDate($appointment['appointment_date']); ?> 
                                            🕐 <?php echo $appointment['appointment_time']; ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <?php
                                        $status_colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-blue-100 text-blue-800',
                                            'no_show' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $status_text = [
                                            'pending' => 'รอยืนยัน',
                                            'confirmed' => 'ยืนยันแล้ว',
                                            'cancelled' => 'ยกเลิก',
                                            'completed' => 'เสร็จสิ้น',
                                            'no_show' => 'ไม่มาตามนัด'
                                        ];
                                        $color = $status_colors[$appointment['status']] ?? 'bg-gray-100 text-gray-800';
                                        $text = $status_text[$appointment['status']] ?? $appointment['status'];
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $color; ?>">
                                            <?php echo $text; ?>
                                        </span>
                                        <?php if ($appointment['symptoms']): ?>
                                        <p class="text-xs text-gray-400 mt-1">💭 <?php echo htmlspecialchars(mb_substr($appointment['symptoms'], 0, 30)) . (mb_strlen($appointment['symptoms']) > 30 ? '...' : ''); ?></p>
                                        <?php endif; ?>
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
                            <a href="appointments.php?action=add" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg text-center hover:from-blue-600 hover:to-blue-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">➕</div>
                                <div class="font-medium">เพิ่มนัดหมาย</div>
                            </a>
                            
                            <a href="appointments.php?status=pending" class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white p-4 rounded-lg text-center hover:from-yellow-600 hover:to-yellow-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">⏳</div>
                                <div class="font-medium">รอยืนยัน</div>
                            </a>
                            
                            <?php if ($_SESSION['user_role'] === 'doctor'): ?>
                            <a href="patients.php" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg text-center hover:from-green-600 hover:to-green-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">👥</div>
                                <div class="font-medium">ผู้ป่วย</div>
                            </a>
                            
                            <a href="visits.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg text-center hover:from-purple-600 hover:to-purple-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">🏥</div>
                                <div class="font-medium">การรักษา</div>
                            </a>
                            <?php else: ?>
                            <a href="schedule.php" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg text-center hover:from-green-600 hover:to-green-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">📋</div>
                                <div class="font-medium">ตารางงาน</div>
                            </a>
                            
                            <a href="profile.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg text-center hover:from-purple-600 hover:to-purple-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">👤</div>
                                <div class="font-medium">โปรไฟล์</div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">ตารางงานวันนี้</h3>
                        <p class="text-sm text-gray-600"><?php echo formatThaiDate(date('Y-m-d')); ?></p>
                    </div>
                    <div class="p-6">
                        <?php
                        // Get today's appointments by time slots
                        if ($user_department) {
                            try {
                                $stmt = $conn->prepare("
                                    SELECT appointment_time, COUNT(*) as count, 
                                           GROUP_CONCAT(patient_name SEPARATOR ', ') as patients
                                    FROM appointments 
                                    WHERE appointment_date = ? AND department_id = ? AND status != 'cancelled'
                                    GROUP BY appointment_time
                                    ORDER BY appointment_time
                                ");
                                $stmt->execute([$today, $user_department]);
                                $time_slots = $stmt->fetchAll();
                            } catch (Exception $e) {
                                $time_slots = [];
                            }
                        } else {
                            $time_slots = [];
                        }
                        
                        if (empty($time_slots)):
                        ?>
                            <div class="text-center py-8">
                                <div class="text-4xl mb-2">📅</div>
                                <p class="text-gray-500">ไม่มีนัดหมายวันนี้</p>
                                <p class="text-sm text-gray-400">วันหยุดพักผ่อน</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($time_slots as $slot): ?>
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold text-sm"><?php echo substr($slot['appointment_time'], 0, 5); ?></span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-800"><?php echo $slot['count']; ?> นัดหมาย</h4>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars(mb_substr($slot['patients'], 0, 40) . (mb_strlen($slot['patients']) > 40 ? '...' : '')); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                                            <?php echo $slot['count']; ?> คน
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Department Status -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">สถานะแผนก</h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($department_name); ?></p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Department Status Indicators -->
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                    <span class="font-medium text-gray-800">ระบบนัดหมาย</span>
                                </div>
                                <span class="text-green-600 text-sm font-medium">ปกติ</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                                    <span class="font-medium text-gray-800">เวลาทำการ</span>
                                </div>
                                <span class="text-blue-600 text-sm font-medium">08:00-16:30</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                                    <span class="font-medium text-gray-800">คิวรอ</span>
                                </div>
                                <span class="text-yellow-600 text-sm font-medium"><?php echo $dept_pending_appointments; ?> คิว</span>
                            </div>
                            
                            <!-- Quick Stats -->
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-3">สถิติด่วน</h4>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="text-center p-2 bg-gray-50 rounded">
                                        <div class="font-semibold text-lg text-blue-600"><?php echo $dept_appointments_today; ?></div>
                                        <div class="text-gray-600">วันนี้</div>
                                    </div>
                                    <div class="text-center p-2 bg-gray-50 rounded">
                                        <div class="font-semibold text-lg text-green-600">
                                            <?php
                                            if ($user_department) {
                                                try {
                                                    $stmt = $conn->prepare("
                                                        SELECT COUNT(*) as count 
                                                        FROM appointments 
                                                        WHERE WEEK(appointment_date) = WEEK(NOW()) AND department_id = ?
                                                    ");
                                                    $stmt->execute([$user_department]);
                                                    echo $stmt->fetch()['count'];
                                                } catch (Exception $e) {
                                                    echo '0';
                                                }
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </div>
                                        <div class="text-gray-600">สัปดาห์นี้</div>
                                    </div>
                                </div>
                            </div>
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
            document.getElementById('current-time').textContent = timeString;
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // Auto refresh dashboard every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);

        // Add smooth hover effects
        document.querySelectorAll('.hover\\:scale-105').forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });
            element.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Welcome animation
        window.addEventListener('load', function() {
            const welcomeSection = document.querySelector('main > div:first-child');
            welcomeSection.style.opacity = '0';
            welcomeSection.style.transform = 'translateY(20px)';
            welcomeSection.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                welcomeSection.style.opacity = '1';
                welcomeSection.style.transform = 'translateY(0)';
            }, 100);
        });

        // Notification for pending appointments
        <?php if ($dept_pending_appointments > 0): ?>
        setTimeout(function() {
            if (confirm('มีนัดหมายรอการยืนยัน <?php echo $dept_pending_appointments; ?> รายการ ต้องการดูหรือไม่?')) {
                window.location.href = 'appointments.php?status=pending';
            }
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html><?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

// Check role access (staff, doctor, nurse can access)
$allowed_roles = ['staff', 'doctor', 'nurse'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: ../login.php');
    exit();
}

$page_title = "แดชบอร์ดเจ้าหน้าที่";

// Get user info
$db = new Database();
$conn = $db->getConnection();

// Get today's appointments for user's department
$today = date('Y-m-d');
$user_department = $_SESSION['department_id'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM appointments 
    WHERE appointment_date = ? AND department_id = ?
");
$stmt->execute([$today, $user_department]);
$dept_appointments_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get pending appointments for user's department
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM appointments 
    WHERE status = 'pending' AND department_id = ?
");
$stmt->execute([$user_department]);
$dept_pending_appointments = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent appointments for user's department
$stmt = $conn->prepare("
    SELECT a.*, d.name as department_name 
    FROM appointments a 
    LEFT JOIN departments d ON a.department_id = d.id 
    WHERE a.department_id = ?
    ORDER BY a.created_at DESC 
    LIMIT 10
");
$stmt->execute([$user_department]);
$recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get department info
$stmt = $conn->prepare("SELECT name FROM departments WHERE id = ?");
$stmt->execute([$user_department]);
$department_info = $stmt->fetch(PDO::FETCH_ASSOC);
$department_name = $department_info['name'] ?? 'ไม่ระบุแผนก';
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
    <nav class="bg-gradient-to-r from-blue-600 to-green-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">THC</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">ระบบจัดการโรงพยาบาลทุ่งหัวช้าง</h1>
                        <p class="text-sm opacity-90"><?php echo $department_name; ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="font-medium"><?php echo $_SESSION['user_name']; ?></p>
                        <p class="text-sm opacity-90"><?php echo ucfirst($_SESSION['user_role']); ?></p>
                    </div>
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <span class="text-sm">👤</span>
                    </div>
                    <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded transition duration-300">
                        ออกจากระบบ
                    </a>
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
                    <a href="appointments.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📅 จัดการนัดหมาย
                    </a>
                    <?php if ($_SESSION['user_role'] === 'doctor'): ?>
                    <a href="patients.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👥 ข้อมูลผู้ป่วย
                    </a>
                    <a href="visits.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        🏥 การเข้ารับการรักษา
                    </a>
                    <?php endif; ?>
                    <a href="schedule.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📋 ตารางงาน
                    </a>
                    <a href="profile.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👨‍💼 โปรไฟล์
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
            <!-- Welcome Section -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-green-500 text-white rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-2">
                        สวัสดี, <?php echo $_SESSION['user_name']; ?>! 👋
                    </h2>
                    <p class="opacity-90">
                        ยินดีต้อนรับสู่ระบบจัดการโรงพยาบาล - <?php echo $department_name; ?>
                    </p>
                    <div class="mt-4 text-sm opacity-80">
                        📅 วันนี้: <?php echo formatThaiDate(date('Y-m-d')); ?> | 
                        🕐 เวลา: <span id="current-time"></span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-blue-600 mr-4">📅</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $dept_appointments_today; ?></h3>
                            <p class="text-gray-600">นัดหมายวันนี้</p>
                            <p class="text-xs text-gray-500"><?php echo $department_name; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-yellow-600 mr-4">⏳</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $dept_pending_appointments; ?></h3>
                            <p class="text-gray-600">รอการยืนยัน</p>
                            <p class="text-xs text-gray-500">แผนกของคุณ</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-green-600 mr-4">👨‍⚕️</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo ucfirst($_SESSION['user_role']); ?></h3>
                            <p class="text-gray-600">บทบาทของคุณ</p>
                            <p class="text-xs text-gray-500">สิทธิ์การใช้งาน</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-purple-600 mr-4">🏥</div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800"><?php echo $department_name; ?></h3>
                            <p class="text-gray-600">แผนกงาน</p>
                            <p class="text-xs text-green-600">● ออนไลน์</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Recent Appointments -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-gray-800">นัดหมายล่าสุด</h3>
                            <a href="appointments.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">ดูทั้งหมด →</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if (empty($recent_appointments)): ?>
                            <div class="text-center py-8">
                                <div class="text-4xl mb-2">📅</div>
                                <p class="text-gray-500">ไม่มีข้อมูลนัดหมาย</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach (array_slice($recent_appointments, 0, 5) as $appointment): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-300">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($appointment['patient_name']); ?></h4>
                                        <p class="text-sm text-gray-600">
                                            📞 <?php echo $appointment['patient_phone']; ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            📅 <?php echo formatThaiDate($appointment['appointment_date']); ?> 
                                            🕐 <?php echo $appointment['appointment_time']; ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <?php
                                        $status_colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $status_text = [
                                            'pending' => 'รอยืนยัน',
                                            'confirmed' => 'ยืนยันแล้ว',
                                            'cancelled' => 'ยกเลิก',
                                            'completed' => 'เสร็จสิ้น'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$appointment['status']]; ?>">
                                            <?php echo $status_text[$appointment['status']]; ?>
                                        </span>
                                        <?php if ($appointment['symptoms']): ?>
                                        <p class="text-xs text-gray-400 mt-1">💭 <?php echo mb_substr($appointment['symptoms'], 0, 30) . '...'; ?></p>
                                        <?php endif; ?>
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
                            <a href="appointments.php?action=add" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg text-center hover:from-blue-600 hover:to-blue-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">➕</div>
                                <div class="font-medium">เพิ่มนัดหมาย</div>
                            </a>
                            
                            <a href="appointments.php?status=pending" class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white p-4 rounded-lg text-center hover:from-yellow-600 hover:to-yellow-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">⏳</div>
                                <div class="font-medium">รอยืนยัน</div>
                            </a>
                            
                            <?php if ($_SESSION['user_role'] === 'doctor'): ?>
                            <a href="patients.php" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg text-center hover:from-green-600 hover:to-green-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">👥</div>
                                <div class="font-medium">ผู้ป่วย</div>
                            </a>
                            
                            <a href="visits.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg text-center hover:from-purple-600 hover:to-purple-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">🏥</div>
                                <div class="font-medium">การรักษา</div>
                            </a>
                            <?php else: ?>
                            <a href="schedule.php" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg text-center hover:from-green-600 hover:to-green-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">📋</div>
                                <div class="font-medium">ตารางงาน</div>
                            </a>
                            
                            <a href="profile.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg text-center hover:from-purple-600 hover:to-purple-700 transition duration-300 transform hover:scale-105">
                                <div class="text-2xl mb-2">👤</div>
                                <div class="font-medium">โปรไฟล์</div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">ตารางงานวันนี้</h3>
                        <p class="text-sm text-gray-600"><?php echo formatThaiDate(date('Y-m-d')); ?></p>
                    </div>
                    <div class="p-6">
                        <?php
                        // Get today's appointments by time slots
                        $stmt = $conn->prepare("
                            SELECT appointment_time, COUNT(*) as count, 
                                   GROUP_CONCAT(patient_name SEPARATOR ', ') as patients
                            FROM appointments 
                            WHERE appointment_date = ? AND department_id = ? AND status != 'cancelled'
                            GROUP BY appointment_time
                            ORDER BY appointment_time
                        ");
                        $stmt->execute([$today, $user_department]);
                        $time_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($time_slots)):
                        ?>
                            <div class="text-center py-8">
                                <div class="text-4xl mb-2">📅</div>
                                <p class="text-gray-500">ไม่มีนัดหมายวันนี้</p>
                                <p class="text-sm text-gray-400">วันหยุดพักผ่อน</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($time_slots as $slot): ?>
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold text-sm"><?php echo substr($slot['appointment_time'], 0, 5); ?></span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-800"><?php echo $slot['count']; ?> นัดหมาย</h4>
                                            <p class="text-sm text-gray-600"><?php echo mb_substr($slot['patients'], 0, 40) . (mb_strlen($slot['patients']) > 40 ? '...' : ''); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                                            <?php echo $slot['count']; ?> คน
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Department Status -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800">สถานะแผนก</h3>
                        <p class="text-sm text-gray-600"><?php echo $department_name; ?></p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Department Status Indicators -->
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                    <span class="font-medium text-gray-800">ระบบนัดหมาย</span>
                                </div>
                                <span class="text-green-600 text-sm font-medium">ปกติ</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                                    <span class="font-medium text-gray-800">เวลาทำการ</span>
                                </div>
                                <span class="text-blue-600 text-sm font-medium">08:00-16:30</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                                    <span class="font-medium text-gray-800">คิวรอ</span>
                                </div>
                                <span class="text-yellow-600 text-sm font-medium"><?php echo $dept_pending_appointments; ?> คิว</span>
                            </div>
                            
                            <!-- Quick Stats -->
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-3">สถิติด่วน</h4>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="text-center p-2 bg-gray-50 rounded">
                                        <div class="font-semibold text-lg text-blue-600"><?php echo $dept_appointments_today; ?></div>
                                        <div class="text-gray-600">วันนี้</div>
                                    </div>
                                    <div class="text-center p-2 bg-gray-50 rounded">
                                        <div class="font-semibold text-lg text-green-600">
                                            <?php
                                            $stmt = $conn->prepare("
                                                SELECT COUNT(*) as count 
                                                FROM appointments 
                                                WHERE WEEK(appointment_date) = WEEK(NOW()) AND department_id = ?
                                            ");
                                            $stmt->execute([$user_department]);
                                            echo $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                            ?>
                                        </div>
                                        <div class="text-gray-600">สัปดาห์นี้</div>
                                    </div>
                                </div>
                            </div>
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
            document.getElementById('current-time').textContent = timeString;
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // Auto refresh dashboard every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);

        // Add smooth hover effects
        document.querySelectorAll('.hover\\:scale-105').forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });
            element.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Welcome animation
        window.addEventListener('load', function() {
            const welcomeSection = document.querySelector('main > div:first-child');
            welcomeSection.style.opacity = '0';
            welcomeSection.style.transform = 'translateY(20px)';
            welcomeSection.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                welcomeSection.style.opacity = '1';
                welcomeSection.style.transform = 'translateY(0)';
            }, 100);
        });

        // Notification for pending appointments
        <?php if ($dept_pending_appointments > 0): ?>
        setTimeout(function() {
            if (confirm('มีนัดหมายรอการยืนยัน <?php echo $dept_pending_appointments; ?> รายการ ต้องการดูหรือไม่?')) {
                window.location.href = 'appointments.php?status=pending';
            }
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>