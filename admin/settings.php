<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin role
requireAdmin('../login.php');

$page_title = "ตั้งค่าระบบ";

// Handle actions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($action === 'update_general_settings') {
            $settings = [
                'hospital_name' => sanitizeInput($_POST['hospital_name'] ?? ''),
                'hospital_name_en' => sanitizeInput($_POST['hospital_name_en'] ?? ''),
                'hospital_address' => sanitizeInput($_POST['hospital_address'] ?? ''),
                'hospital_phone' => sanitizeInput($_POST['hospital_phone'] ?? ''),
                'hospital_fax' => sanitizeInput($_POST['hospital_fax'] ?? ''),
                'hospital_email' => sanitizeInput($_POST['hospital_email'] ?? ''),
                'emergency_phone' => sanitizeInput($_POST['emergency_phone'] ?? ''),
                'website_url' => sanitizeInput($_POST['website_url'] ?? ''),
                'timezone' => sanitizeInput($_POST['timezone'] ?? 'Asia/Bangkok')
            ];
            
            $updated_count = 0;
            foreach ($settings as $key => $value) {
                if (setSetting($conn, $key, $value, 'string', 'Hospital ' . ucfirst(str_replace('_', ' ', $key)))) {
                    $updated_count++;
                }
            }
            
            if ($updated_count > 0) {
                logActivity($conn, $_SESSION['user_id'], 'settings_updated', 'settings', null, null, $settings);
                $message = "อัพเดทการตั้งค่าทั่วไป $updated_count รายการเรียบร้อยแล้ว";
            } else {
                $error = "ไม่สามารถอัพเดทการตั้งค่าได้";
            }
            
        } elseif ($action === 'update_appointment_settings') {
            $settings = [
                'appointment_slots_per_hour' => (int)($_POST['appointment_slots_per_hour'] ?? 4),
                'max_advance_days' => (int)($_POST['max_advance_days'] ?? 30),
                'min_advance_hours' => (int)($_POST['min_advance_hours'] ?? 24),
                'appointment_duration' => (int)($_POST['appointment_duration'] ?? 30),
                'auto_confirm_appointments' => isset($_POST['auto_confirm_appointments']) ? '1' : '0',
                'send_sms_notifications' => isset($_POST['send_sms_notifications']) ? '1' : '0',
                'send_email_notifications' => isset($_POST['send_email_notifications']) ? '1' : '0',
                'working_days' => sanitizeInput($_POST['working_days'] ?? '1,2,3,4,5'),
                'working_hours_start' => sanitizeInput($_POST['working_hours_start'] ?? '08:00'),
                'working_hours_end' => sanitizeInput($_POST['working_hours_end'] ?? '16:30'),
                'weekend_hours_start' => sanitizeInput($_POST['weekend_hours_start'] ?? '08:00'),
                'weekend_hours_end' => sanitizeInput($_POST['weekend_hours_end'] ?? '12:00')
            ];
            
            $updated_count = 0;
            foreach ($settings as $key => $value) {
                $type = is_numeric($value) ? 'number' : (in_array($value, ['0', '1']) ? 'boolean' : 'string');
                if (setSetting($conn, $key, $value, $type, 'Appointment ' . ucfirst(str_replace('_', ' ', $key)))) {
                    $updated_count++;
                }
            }
            
            if ($updated_count > 0) {
                logActivity($conn, $_SESSION['user_id'], 'appointment_settings_updated', 'settings', null, null, $settings);
                $message = "อัพเดทการตั้งค่าการนัดหมาย $updated_count รายการเรียบร้อยแล้ว";
            } else {
                $error = "ไม่สามารถอัพเดทการตั้งค่าได้";
            }
            
        } elseif ($action === 'update_system_settings') {
            $settings = [
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
                'maintenance_message' => sanitizeInput($_POST['maintenance_message'] ?? ''),
                'session_timeout' => (int)($_POST['session_timeout'] ?? 120),
                'max_login_attempts' => (int)($_POST['max_login_attempts'] ?? 5),
                'login_lockout_time' => (int)($_POST['login_lockout_time'] ?? 30),
                'password_min_length' => (int)($_POST['password_min_length'] ?? 6),
                'require_password_complexity' => isset($_POST['require_password_complexity']) ? '1' : '0',
                'enable_registration' => isset($_POST['enable_registration']) ? '1' : '0',
                'enable_api' => isset($_POST['enable_api']) ? '1' : '0',
                'log_retention_days' => (int)($_POST['log_retention_days'] ?? 90),
                'backup_retention_days' => (int)($_POST['backup_retention_days'] ?? 30)
            ];
            
            $updated_count = 0;
            foreach ($settings as $key => $value) {
                $type = is_numeric($value) ? 'number' : (in_array($value, ['0', '1']) ? 'boolean' : 'string');
                if (setSetting($conn, $key, $value, $type, 'System ' . ucfirst(str_replace('_', ' ', $key)))) {
                    $updated_count++;
                }
            }
            
            if ($updated_count > 0) {
                logActivity($conn, $_SESSION['user_id'], 'system_settings_updated', 'settings', null, null, $settings);
                $message = "อัพเดทการตั้งค่าระบบ $updated_count รายการเรียบร้อยแล้ว";
            } else {
                $error = "ไม่สามารถอัพเดทการตั้งค่าได้";
            }
            
        } elseif ($action === 'backup_database') {
            // Basic backup functionality (in production, use mysqldump or similar)
            $backup_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backup_path = '../backups/' . $backup_name;
            
            if (!file_exists('../backups')) {
                mkdir('../backups', 0755, true);
            }
            
            // Log backup attempt
            logActivity($conn, $_SESSION['user_id'], 'database_backup_initiated', 'system', null, null, ['backup_name' => $backup_name]);
            $message = "เริ่มการสำรองข้อมูล: $backup_name (ใช้เครื่องมือภายนอกสำหรับการสำรองที่สมบูรณ์)";
            
        } elseif ($action === 'clear_logs') {
            $days = (int)($_POST['clear_days'] ?? 30);
            $stmt = $conn->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $affected = $stmt->execute([$days]);
            
            logActivity($conn, $_SESSION['user_id'], 'logs_cleared', 'activity_logs', null, null, ['days' => $days]);
            $message = "ล้างข้อมูล log ที่เก่ากว่า $days วันเรียบร้อยแล้ว";
            
        } elseif ($action === 'test_email') {
            $test_email = sanitizeInput($_POST['test_email'] ?? '');
            if (!empty($test_email)) {
                // In production, implement actual email sending
                logActivity($conn, $_SESSION['user_id'], 'email_test', 'system', null, null, ['email' => $test_email]);
                $message = "ส่งอีเมลทดสอบไปยัง $test_email (ต้องติดตั้งระบบส่งอีเมล)";
            } else {
                $error = "กรุณากรอกอีเมลสำหรับทดสอบ";
            }
        }
        
    } catch (Exception $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        logError($e->getMessage(), __FILE__, __LINE__);
    }
}

// Load current settings
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get all settings
    $stmt = $conn->prepare("SELECT setting_key, setting_value, setting_type FROM settings ORDER BY setting_key");
    $stmt->execute();
    $all_settings = $stmt->fetchAll();
    
    $settings = [];
    foreach ($all_settings as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    
    // Get system statistics
    $stats = [
        'total_users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'total_appointments' => $conn->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
        'total_patients' => $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
        'total_logs' => $conn->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn(),
        'database_size' => 0, // Would need SHOW TABLE STATUS in production
        'disk_usage' => 0 // Would need system call in production
    ];
    
} catch (Exception $e) {
    $error = "เกิดข้อผิดพลาดในการโหลดข้อมูล";
    logError($e->getMessage(), __FILE__, __LINE__);
    $settings = [];
    $stats = [];
}

// Helper function to get setting value
function getSettingValue($key, $default = '') {
    global $settings;
    return isset($settings[$key]) ? $settings[$key] : $default;
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
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button.active { background-color: #3b82f6; color: white; }
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
                    <a href="news.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📰 จัดการข่าวสาร
                    </a>
                    <a href="users.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👨‍💼 จัดการผู้ใช้
                    </a>
                    <a href="reports.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📊 รายงาน
                    </a>
                    <a href="settings.php" class="block py-2 px-4 text-blue-600 bg-blue-50 rounded font-medium">
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
                <h2 class="text-3xl font-bold text-gray-800">ตั้งค่าระบบ</h2>
                <p class="text-gray-600">จัดการการตั้งค่าและการกำหนดค่าระบบโรงพยาบาล</p>
            </div>

            <!-- System Status Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-blue-600 mr-4">👥</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_users'] ?? 0); ?></h3>
                            <p class="text-gray-600">ผู้ใช้ระบบ</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-green-600 mr-4">📅</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_appointments'] ?? 0); ?></h3>
                            <p class="text-gray-600">การนัดหมาย</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-purple-600 mr-4">🏥</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_patients'] ?? 0); ?></h3>
                            <p class="text-gray-600">ผู้ป่วย</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-orange-600 mr-4">📋</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_logs'] ?? 0); ?></h3>
                            <p class="text-gray-600">บันทึกกิจกรรม</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tabs -->
            <div class="bg-white rounded-lg shadow-lg">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6">
                        <button class="tab-button active py-4 text-sm font-medium border-b-2 border-blue-500" onclick="showTab('general')">
                            🏥 ข้อมูลทั่วไป
                        </button>
                        <button class="tab-button py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300" onclick="showTab('appointment')">
                            📅 การนัดหมาย
                        </button>
                        <button class="tab-button py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300" onclick="showTab('system')">
                            ⚙️ ระบบ
                        </button>
                        <button class="tab-button py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300" onclick="showTab('maintenance')">
                            🔧 การบำรุงรักษา
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- General Settings Tab -->
                    <div id="general-tab" class="tab-content active">
                        <h3 class="text-xl font-semibold mb-6">ข้อมูลทั่วไปของโรงพยาบาล</h3>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="update_general_settings">
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อโรงพยาบาล (ไทย)</label>
                                    <input type="text" name="hospital_name" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_name', 'โรงพยาบาลทุ่งหัวช้าง')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อโรงพยาบาล (อังกฤษ)</label>
                                    <input type="text" name="hospital_name_en" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_name_en', 'Tung Hua Chang Hospital')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ที่อยู่</label>
                                <textarea name="hospital_address" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars(getSettingValue('hospital_address', '123 ถนนหลัก ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน 51000')); ?></textarea>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">โทรศัพท์</label>
                                    <input type="text" name="hospital_phone" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_phone', '053-580-100')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">โทรสาร</label>
                                    <input type="text" name="hospital_fax" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_fax', '053-580-110')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">อีเมล</label>
                                    <input type="email" name="hospital_email" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_email', 'info@thchospital.go.th')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">โทรศัพท์ฉุกเฉิน</label>
                                    <input type="text" name="emergency_phone" 
                                           value="<?php echo htmlspecialchars(getSettingValue('emergency_phone', '053-580-999')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">เว็บไซต์</label>
                                    <input type="url" name="website_url" 
                                           value="<?php echo htmlspecialchars(getSettingValue('website_url', 'https://www.thchospital.go.th')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">เขตเวลา</label>
                                    <select name="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="Asia/Bangkok" <?php echo getSettingValue('timezone', 'Asia/Bangkok') === 'Asia/Bangkok' ? 'selected' : ''; ?>>Asia/Bangkok (UTC+7)</option>
                                        <option value="UTC" <?php echo getSettingValue('timezone') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                    💾 บันทึกการตั้งค่าทั่วไป
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Appointment Settings Tab -->
                    <div id="appointment-tab" class="tab-content">
                        <h3 class="text-xl font-semibold mb-6">การตั้งค่าระบบนัดหมาย</h3>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="update_appointment_settings">
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">จำนวนช่องนัดต่อชั่วโมง</label>
                                    <input type="number" name="appointment_slots_per_hour" min="1" max="20"
                                           value="<?php echo htmlspecialchars(getSettingValue('appointment_slots_per_hour', '4')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ระยะเวลานัดล่วงหน้าสูงสุด (วัน)</label>
                                    <input type="number" name="max_advance_days" min="1" max="365"
                                           value="<?php echo htmlspecialchars(getSettingValue('max_advance_days', '30')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ระยะเวลานัดล่วงหน้าต่ำสุด (ชั่วโมง)</label>
                                    <input type="number" name="min_advance_hours" min="1" max="168"
                                           value="<?php echo htmlspecialchars(getSettingValue('min_advance_hours', '24')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ระยะเวลาการนัดหมาย (นาที)</label>
                                    <input type="number" name="appointment_duration" min="15" max="120" step="15"
                                           value="<?php echo htmlspecialchars(getSettingValue('appointment_duration', '30')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <h4 class="font-semibold text-gray-800">เวลาทำการ</h4>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">เวลาเริ่มงาน (จันทร์-ศุกร์)</label>
                                        <input type="time" name="working_hours_start"
                                               value="<?php echo htmlspecialchars(getSettingValue('working_hours_start', '08:00')); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>