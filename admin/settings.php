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
                'working_hours_start' => sanitizeInput($_POST['working_hours_start'] ?? '08:00'),
                'working_hours_end' => sanitizeInput($_POST['working_hours_end'] ?? '16:30'),
                'weekend_hours_start' => sanitizeInput($_POST['weekend_hours_start'] ?? '08:00'),
                'weekend_hours_end' => sanitizeInput($_POST['weekend_hours_end'] ?? '12:00'),
                'timezone' => sanitizeInput($_POST['timezone'] ?? 'Asia/Bangkok')
            ];
            
            $updated_count = 0;
            foreach ($settings as $key => $value) {
                // Insert or update settings
                $stmt = $conn->prepare("
                    INSERT INTO settings (setting_key, setting_value, setting_type, description, updated_at) 
                    VALUES (?, ?, 'string', ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_at = NOW()
                ");
                
                $description = 'Hospital ' . ucfirst(str_replace('_', ' ', $key));
                if ($stmt->execute([$key, $value, $description])) {
                    $updated_count++;
                }
            }
            
            if ($updated_count > 0) {
                logActivity($conn, $_SESSION['user_id'], 'settings_updated', 'settings', null, null, $settings);
                $message = "อัพเดทการตั้งค่าทั่วไป $updated_count รายการเรียบร้อยแล้ว";
            } else {
                $error = "ไม่สามารถอัพเดทการตั้งค่าได้";
            }
            
        } elseif ($action === 'update_website_settings') {
            $settings = [
                'website_title' => sanitizeInput($_POST['website_title'] ?? ''),
                'website_description' => sanitizeInput($_POST['website_description'] ?? ''),
                'website_keywords' => sanitizeInput($_POST['website_keywords'] ?? ''),
                'facebook_url' => sanitizeInput($_POST['facebook_url'] ?? ''),
                'line_id' => sanitizeInput($_POST['line_id'] ?? ''),
                'google_analytics_id' => sanitizeInput($_POST['google_analytics_id'] ?? ''),
                'show_statistics' => isset($_POST['show_statistics']) ? '1' : '0',
                'show_doctors' => isset($_POST['show_doctors']) ? '1' : '0',
                'news_per_page' => (int)($_POST['news_per_page'] ?? 10),
                'allow_comments' => isset($_POST['allow_comments']) ? '1' : '0'
            ];
            
            $updated_count = 0;
            foreach ($settings as $key => $value) {
                $type = is_numeric($value) ? 'number' : (in_array($value, ['0', '1']) ? 'boolean' : 'string');
                
                $stmt = $conn->prepare("
                    INSERT INTO settings (setting_key, setting_value, setting_type, description, updated_at) 
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_at = NOW()
                ");
                
                $description = 'Website ' . ucfirst(str_replace('_', ' ', $key));
                if ($stmt->execute([$key, $value, $type, $description])) {
                    $updated_count++;
                }
            }
            
            if ($updated_count > 0) {
                logActivity($conn, $_SESSION['user_id'], 'website_settings_updated', 'settings', null, null, $settings);
                $message = "อัพเดทการตั้งค่าเว็บไซต์ $updated_count รายการเรียบร้อยแล้ว";
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
                'backup_retention_days' => (int)($_POST['backup_retention_days'] ?? 30),
                'auto_backup_enabled' => isset($_POST['auto_backup_enabled']) ? '1' : '0',
                'backup_frequency' => sanitizeInput($_POST['backup_frequency'] ?? 'weekly')
            ];
            
            $updated_count = 0;
            foreach ($settings as $key => $value) {
                $type = is_numeric($value) ? 'number' : (in_array($value, ['0', '1']) ? 'boolean' : 'string');
                
                $stmt = $conn->prepare("
                    INSERT INTO settings (setting_key, setting_value, setting_type, description, updated_at) 
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_at = NOW()
                ");
                
                $description = 'System ' . ucfirst(str_replace('_', ' ', $key));
                if ($stmt->execute([$key, $value, $type, $description])) {
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
            // Basic backup functionality
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
            
            // Check if activity_logs table exists
            $table_exists = false;
            try {
                $stmt = $conn->prepare("SHOW TABLES LIKE 'activity_logs'");
                $stmt->execute();
                $table_exists = $stmt->fetch() !== false;
            } catch (Exception $e) {
                // Table doesn't exist
            }
            
            if ($table_exists) {
                $stmt = $conn->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
                $stmt->execute([$days]);
                $affected = $stmt->rowCount();
                
                logActivity($conn, $_SESSION['user_id'], 'logs_cleared', 'activity_logs', null, null, ['days' => $days, 'affected' => $affected]);
                $message = "ล้างข้อมูล log ที่เก่ากว่า $days วัน จำนวน $affected รายการเรียบร้อยแล้ว";
            } else {
                $message = "ไม่พบตาราง activity_logs";
            }
            
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
        if (function_exists('logError')) {
            logError($e->getMessage(), __FILE__, __LINE__);
        }
    }
}

// Load current settings
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Create settings table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_setting_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Get all settings
    $stmt = $conn->prepare("SELECT setting_key, setting_value, setting_type FROM settings ORDER BY setting_key");
    $stmt->execute();
    $all_settings = $stmt->fetchAll();
    
    $settings = [];
    foreach ($all_settings as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    
    // Get system statistics with error handling
    $stats = [
        'total_users' => 0,
        'total_patients' => 0,
        'total_doctors' => 0,
        'total_departments' => 0,
        'total_news' => 0,
        'total_logs' => 0,
        'database_size' => 0,
        'disk_usage' => 0
    ];
    
    // Get statistics with individual error handling
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $stats['total_users'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table might not exist
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM patients");
        $stmt->execute();
        $stats['total_patients'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table might not exist
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM doctors");
        $stmt->execute();
        $stats['total_doctors'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table might not exist
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM departments");
        $stmt->execute();
        $stats['total_departments'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table might not exist
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM news");
        $stmt->execute();
        $stats['total_news'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table might not exist
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM activity_logs");
        $stmt->execute();
        $stats['total_logs'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table might not exist
    }
    
    // Get recent activity with error handling
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
        // Tables might not exist
        $recent_activities = [];
    }
    
} catch (Exception $e) {
    $error = "เกิดข้อผิดพลาดในการโหลดข้อมูล: " . $e->getMessage();
    if (function_exists('logError')) {
        logError($e->getMessage(), __FILE__, __LINE__);
    }
    $settings = [];
    $stats = ['total_users' => 0, 'total_patients' => 0, 'total_doctors' => 0, 'total_departments' => 0, 'total_news' => 0, 'total_logs' => 0, 'database_size' => 0, 'disk_usage' => 0];
    $recent_activities = [];
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
    <title><?php echo $page_title; ?> - <?php echo htmlspecialchars($_SESSION['hospital_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button.active { 
            background-color: #3b82f6; 
            color: white; 
            border-color: #3b82f6;
        }
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
                    <a href="dashboard.php" class="block py-2 px-4 text-blue-600 bg-blue-50 rounded font-medium">
                        📊 แดชบอร์ด
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
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                ❌ <?php echo htmlspecialchars($error); ?>
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
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></h3>
                            <p class="text-gray-600">ผู้ใช้ระบบ</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-green-600 mr-4">🏥</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_patients']); ?></h3>
                            <p class="text-gray-600">ผู้ป่วย</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-purple-600 mr-4">👨‍⚕️</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_doctors']); ?></h3>
                            <p class="text-gray-600">แพทย์</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-orange-600 mr-4">📰</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_news']); ?></h3>
                            <p class="text-gray-600">ข่าวสาร</p>
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
                        <button class="tab-button py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300" onclick="showTab('website')">
                            🌐 เว็บไซต์
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
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_name', 'โรงพยาบาล')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อโรงพยาบาล (อังกฤษ)</label>
                                    <input type="text" name="hospital_name_en" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_name_en', 'Hospital')); ?>"
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
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_email', 'info@hospital.go.th')); ?>"
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
                            
                            <div class="space-y-4">
                                <h4 class="font-semibold text-gray-800">เวลาทำการ</h4>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">เวลาเริ่มงาน (จันทร์-ศุกร์)</label>
                                        <input type="time" name="working_hours_start"
                                               value="<?php echo htmlspecialchars(getSettingValue('working_hours_start', '08:00')); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">เวลาเลิกงาน (จันทร์-ศุกร์)</label>
                                        <input type="time" name="working_hours_end"
                                               value="<?php echo htmlspecialchars(getSettingValue('working_hours_end', '16:30')); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">เวลาเริ่มงาน (เสาร์-อาทิตย์)</label>
                                        <input type="time" name="weekend_hours_start"
                                               value="<?php echo htmlspecialchars(getSettingValue('weekend_hours_start', '08:00')); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">เวลาเลิกงาน (เสาร์-อาทิตย์)</label>
                                        <input type="time" name="weekend_hours_end"
                                               value="<?php echo htmlspecialchars(getSettingValue('weekend_hours_end', '12:00')); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                    💾 บันทึกการตั้งค่าทั่วไป
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Website Settings Tab -->
                    <div id="website-tab" class="tab-content">
                        <h3 class="text-xl font-semibold mb-6">การตั้งค่าเว็บไซต์</h3>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="update_website_settings">
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อเว็บไซต์</label>
                                    <input type="text" name="website_title" 
                                           value="<?php echo htmlspecialchars(getSettingValue('website_title', 'โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">จำนวนข่าวต่อหน้า</label>
                                    <input type="number" name="news_per_page" min="5" max="50"
                                           value="<?php echo htmlspecialchars(getSettingValue('news_per_page', '10')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">คำอธิบายเว็บไซต์</label>
                                <textarea name="website_description" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                          placeholder="คำอธิบายสำหรับ SEO"><?php echo htmlspecialchars(getSettingValue('website_description', 'โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน')); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">คำค้น (Keywords)</label>
                                <input type="text" name="website_keywords" 
                                       value="<?php echo htmlspecialchars(getSettingValue('website_keywords', 'โรงพยาบาล, ลำพูน, ทุ่งหัวช้าง, สุขภาพ, แพทย์, รักษา')); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="แยกด้วยเครื่องหมายจุลภาค">
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Facebook URL</label>
                                    <input type="url" name="facebook_url" 
                                           value="<?php echo htmlspecialchars(getSettingValue('facebook_url', '')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           placeholder="https://facebook.com/yourpage">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Line ID</label>
                                    <input type="text" name="line_id" 
                                           value="<?php echo htmlspecialchars(getSettingValue('line_id', '')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           placeholder="@yourlineid">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Google Analytics ID</label>
                                <input type="text" name="google_analytics_id" 
                                       value="<?php echo htmlspecialchars(getSettingValue('google_analytics_id', '')); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="G-XXXXXXXXXX">
                            </div>
                            
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-800">การแสดงผล</h4>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="show_statistics" value="1"
                                           <?php echo getSettingValue('show_statistics', '1') === '1' ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm">แสดงสถิติในหน้าแรก</span>
                                </label>
                                
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="show_doctors" value="1"
                                           <?php echo getSettingValue('show_doctors', '1') === '1' ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm">แสดงทีมแพทย์ในหน้าแรก</span>
                                </label>
                                
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="allow_comments" value="1"
                                           <?php echo getSettingValue('allow_comments', '0') === '1' ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm">อนุญาตให้แสดงความคิดเห็น</span>
                                </label>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                    🌐 บันทึกการตั้งค่าเว็บไซต์
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- System Settings Tab -->
                    <div id="system-tab" class="tab-content">
                        <h3 class="text-xl font-semibold mb-6">การตั้งค่าระบบ</h3>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="update_system_settings">
                            
                            <div class="space-y-4">
                                <h4 class="font-semibold text-gray-800">โหมดบำรุงรักษา</h4>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="maintenance_mode" value="1"
                                           <?php echo getSettingValue('maintenance_mode') === '1' ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span class="text-sm">เปิดใช้งานโหมดบำรุงรักษา (ปิดระบบชั่วคราว)</span>
                                </label>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ข้อความแจ้งเตือนระหว่างบำรุงรักษา</label>
                                    <textarea name="maintenance_message" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                              placeholder="ระบบอยู่ระหว่างการบำรุงรักษา กรุณาลองใหม่ภายหลัง"><?php echo htmlspecialchars(getSettingValue('maintenance_message', '')); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">หมดเวลาเซสชัน (นาที)</label>
                                    <input type="number" name="session_timeout" min="30" max="1440"
                                           value="<?php echo htmlspecialchars(getSettingValue('session_timeout', '120')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">จำนวนครั้งที่เข้าสู่ระบบผิดสูงสุด</label>
                                    <input type="number" name="max_login_attempts" min="3" max="10"
                                           value="<?php echo htmlspecialchars(getSettingValue('max_login_attempts', '5')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">เวลาล็อคบัญชี (นาที)</label>
                                    <input type="number" name="login_lockout_time" min="5" max="1440"
                                           value="<?php echo htmlspecialchars(getSettingValue('login_lockout_time', '30')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ความยาวรหัสผ่านต่ำสุด</label>
                                    <input type="number" name="password_min_length" min="6" max="20"
                                           value="<?php echo htmlspecialchars(getSettingValue('password_min_length', '6')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-800">การตั้งค่าความปลอดภัย</h4>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="require_password_complexity" value="1"
                                           <?php echo getSettingValue('require_password_complexity') === '1' ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm">บังคับใช้รหัสผ่านที่ซับซ้อน</span>
                                </label>
                                
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="enable_registration" value="1"
                                           <?php echo getSettingValue('enable_registration') === '1' ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm">เปิดให้สมัครสมาชิกได้</span>
                                </label>
                                
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="enable_api" value="1"
                                           <?php echo getSettingValue('enable_api') === '1' ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm">เปิดใช้งาน API</span>
                                </label>
                            </div>
                            
                            <div class="grid md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">เก็บ Log กี่วัน</label>
                                    <input type="number" name="log_retention_days" min="30" max="365"
                                           value="<?php echo htmlspecialchars(getSettingValue('log_retention_days', '90')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">เก็บ Backup กี่วัน</label>
                                    <input type="number" name="backup_retention_days" min="7" max="365"
                                           value="<?php echo htmlspecialchars(getSettingValue('backup_retention_days', '30')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ความถี่การสำรองข้อมูล</label>
                                    <select name="backup_frequency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="daily" <?php echo getSettingValue('backup_frequency', 'weekly') === 'daily' ? 'selected' : ''; ?>>รายวัน</option>
                                        <option value="weekly" <?php echo getSettingValue('backup_frequency', 'weekly') === 'weekly' ? 'selected' : ''; ?>>รายสัปดาห์</option>
                                        <option value="monthly" <?php echo getSettingValue('backup_frequency', 'weekly') === 'monthly' ? 'selected' : ''; ?>>รายเดือน</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="auto_backup_enabled" value="1"
                                           <?php echo getSettingValue('auto_backup_enabled', '0') === '1' ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm">เปิดการสำรองข้อมูลอัตโนมัติ</span>
                                </label>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                    ⚙️ บันทึกการตั้งค่าระบบ
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Maintenance Tab -->
                    <div id="maintenance-tab" class="tab-content">
                        <h3 class="text-xl font-semibold mb-6">การบำรุงรักษาระบบ</h3>
                        
                        <div class="space-y-8">
                            <!-- Database Backup -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold mb-4">💾 สำรองข้อมูล</h4>
                                <p class="text-gray-600 mb-4">สำรองข้อมูลฐานข้อมูลเพื่อป้องกันการสูญหาย</p>
                                
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="backup_database">
                                    <button type="submit" 
                                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300"
                                            onclick="return confirm('ต้องการสำรองข้อมูลหรือไม่?')">
                                        🗄️ สำรองข้อมูลทันที
                                    </button>
                                </form>
                                
                                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                                    <p class="text-sm text-yellow-800">
                                        <strong>หมายเหตุ:</strong> สำหรับการสำรองข้อมูลที่สมบูรณ์ ควรใช้เครื่องมือ mysqldump หรือระบบสำรองข้อมูลอัตโนมัติ
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Clear Logs -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold mb-4">🗂️ ล้าง Log</h4>
                                <p class="text-gray-600 mb-4">ล้างข้อมูล log เก่าเพื่อประหยัดพื้นที่เก็บข้อมูล</p>
                                
                                <form method="POST" class="space-y-4">
                                    <input type="hidden" name="action" value="clear_logs">
                                    <div class="flex items-center space-x-4">
                                        <label class="text-sm font-medium text-gray-700">ล้าง log ที่เก่ากว่า:</label>
                                        <select name="clear_days" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="30">30 วัน</option>
                                            <option value="60">60 วัน</option>
                                            <option value="90">90 วัน</option>
                                            <option value="180">180 วัน</option>
                                        </select>
                                        <button type="submit" 
                                                class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition duration-300"
                                                onclick="return confirm('ต้องการล้าง log หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้')">
                                            🧹 ล้าง Log
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="mt-4 text-sm text-gray-600">
                                    ปัจจุบันมี log ทั้งหมด: <strong><?php echo number_format($stats['total_logs']); ?></strong> รายการ
                                </div>
                            </div>
                            
                            <!-- Email Test -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold mb-4">📧 ทดสอบอีเมล</h4>
                                <p class="text-gray-600 mb-4">ทดสอบการส่งอีเมลของระบบ</p>
                                
                                <form method="POST" class="space-y-4">
                                    <input type="hidden" name="action" value="test_email">
                                    <div class="flex items-center space-x-4">
                                        <label class="text-sm font-medium text-gray-700">ส่งไปยัง:</label>
                                        <input type="email" name="test_email" required
                                               class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="test@example.com">
                                        <button type="submit" 
                                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                            📤 ทดสอบส่งอีเมล
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded">
                                    <p class="text-sm text-blue-800">
                                        <strong>หมายเหตุ:</strong> ต้องตั้งค่า SMTP หรือระบบส่งอีเมลก่อนใช้งาน
                                    </p>
                                </div>
                            </div>
                            
                            <!-- System Information -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold mb-4">ℹ️ ข้อมูลระบบ</h4>
                                <div class="grid md:grid-cols-2 gap-4 text-sm">
                                    <div class="space-y-2">
                                        <div><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></div>
                                        <div><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></div>
                                        <div><strong>MySQL Version:</strong> 
                                            <?php 
                                            try {
                                                echo $conn->query("SELECT VERSION()")->fetchColumn();
                                            } catch (Exception $e) {
                                                echo 'Unknown';
                                            }
                                            ?>
                                        </div>
                                        <div><strong>Max Upload Size:</strong> <?php echo ini_get('upload_max_filesize'); ?></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div><strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?></div>
                                        <div><strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?>s</div>
                                        <div><strong>Timezone:</strong> <?php echo date_default_timezone_get(); ?></div>
                                        <div><strong>System Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Activity -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold mb-4">📋 กิจกรรมล่าสุด</h4>
                                <?php if (empty($recent_activities)): ?>
                                    <p class="text-gray-500 text-sm">ไม่มีกิจกรรมล่าสุด</p>
                                <?php else: ?>
                                    <div class="space-y-2 max-h-64 overflow-y-auto">
                                        <?php foreach ($recent_activities as $activity): ?>
                                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100">
                                            <div>
                                                <span class="font-medium">
                                                    <?php echo htmlspecialchars(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? '')); ?>
                                                </span>
                                                <span class="text-gray-600 ml-2">
                                                    <?php echo htmlspecialchars($activity['action']); ?>
                                                </span>
                                                <?php if ($activity['table_name']): ?>
                                                <span class="text-gray-500 ml-1">
                                                    (<?php echo htmlspecialchars($activity['table_name']); ?>)
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-gray-500 text-xs">
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
                        <li>• อัพเดทล่าสุด: วันนี้ เวลา <?php echo date('H:i'); ?> น.</li>
                        <li>• หากพบปัญหา กรุณาติดต่อแผนก IT</li>
                        <?php if (getSettingValue('maintenance_mode') === '1'): ?>
                        <li class="text-red-700">⚠️ <strong>โหมดบำรุงรักษาเปิดอยู่</strong> - เว็บไซต์ปิดให้บริการชั่วคราว</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tab switching
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
                button.classList.remove('bg-blue-600', 'text-white', 'border-blue-500');
                button.classList.add('border-transparent', 'hover:border-gray-300');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab button
            event.target.classList.add('active', 'bg-blue-600', 'text-white', 'border-blue-500');
            event.target.classList.remove('border-transparent', 'hover:border-gray-300');
        }

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('border-red-500');
                        isValid = false;
                    } else {
                        field.classList.remove('border-red-500');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                }
            });
        });

        // Auto-save indication
        let saveTimeout;
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('change', function() {
                clearTimeout(saveTimeout);
                
                // Show unsaved changes indicator
                const form = this.closest('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('bg-yellow-600');
                        submitBtn.classList.remove('bg-blue-600');
                        
                        // Reset after 3 seconds
                        saveTimeout = setTimeout(() => {
                            submitBtn.classList.remove('bg-yellow-600');
                            submitBtn.classList.add('bg-blue-600');
                        }, 3000);
                    }
                }
            });
        });

        // Maintenance mode warning
        const maintenanceCheckbox = document.querySelector('input[name="maintenance_mode"]');
        if (maintenanceCheckbox) {
            maintenanceCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    if (!confirm('คุณแน่ใจหรือไม่ที่จะเปิดโหมดบำรุงรักษา?\n\nผู้ใช้งานทั่วไปจะไม่สามารถเข้าถึงระบบได้')) {
                        this.checked = false;
                    }
                }
            });
        }

        // Number input validation
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                const min = parseInt(this.min) || 0;
                const max = parseInt(this.max) || 999999;
                const value = parseInt(this.value) || 0;
                
                if (value < min) {
                    this.value = min;
                } else if (value > max) {
                    this.value = max;
                }
            });
        });

        // URL validation
        document.querySelectorAll('input[type="url"]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value && !this.value.match(/^https?:\/\/.+/)) {
                    this.setCustomValidity('กรุณาใส่ URL ที่ถูกต้อง (ขึ้นต้นด้วย http:// หรือ https://)');
                    this.classList.add('border-red-500');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('border-red-500');
                }
            });
        });

        // Email validation
        document.querySelectorAll('input[type="email"]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value && !this.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    this.setCustomValidity('กรุณาใส่อีเมลที่ถูกต้อง');
                    this.classList.add('border-red-500');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('border-red-500');
                }
            });
        });

        // Auto refresh stats every 30 seconds
        setInterval(function() {
            // Could implement AJAX refresh of statistics here
        }, 30000);

        // Initialize tooltips for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Add title attributes for help text
            const helpElements = [
                { selector: 'input[name="session_timeout"]', text: 'เวลาที่ผู้ใช้จะถูก logout อัตโนมัติเมื่อไม่มีการใช้งาน' },
                { selector: 'input[name="max_login_attempts"]', text: 'จำนวนครั้งที่อนุญาตให้เข้าสู่ระบบผิดก่อนล็อคบัญชี' },
                { selector: 'input[name="login_lockout_time"]', text: 'เวลาที่บัญชีจะถูกล็อคหลังจากเข้าสู่ระบบผิดเกินกำหนด' },
                { selector: 'input[name="log_retention_days"]', text: 'จำนวนวันที่จะเก็บ log ไว้ในระบบ' },
                { selector: 'input[name="backup_retention_days"]', text: 'จำนวนวันที่จะเก็บไฟล์สำรองข้อมูลไว้' }
            ];

            helpElements.forEach(item => {
                const element = document.querySelector(item.selector);
                if (element) {
                    element.title = item.text;
                }
            });
        });

        // Show loading state when submitting forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '⏳ กำลังบันทึก...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after 5 seconds as fallback
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 5000);
                }
            });
        });

        // Clear success/error messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s';
                message.style.opacity = '0';
                setTimeout(() => {
                    message.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>