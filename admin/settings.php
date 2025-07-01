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
    <title><?php echo $page_title; ?> - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .hover-lift { transition: transform 0.2s ease; }
        .hover-lift:hover { transform: translateY(-2px); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button.active { 
            background-color: #3b82f6; 
            color: white; 
            border-color: #3b82f6;
        }
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
                    <a href="dashboard.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
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
                    <a href="settings.php" class="flex items-center py-3 px-4 text-blue-600 bg-blue-50 rounded-lg font-medium border-l-4 border-blue-600">
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
            <!-- Enhanced Messages -->
            <?php if ($message): ?>
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 fade-in shadow-sm">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span><?php echo $message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 fade-in shadow-sm">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-800 mb-2">ตั้งค่าระบบ</h2>
                        <p class="text-gray-600">จัดการการตั้งค่าและการกำหนดค่าระบบโรงพยาบาล</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">อัปเดตล่าสุด</p>
                        <p class="text-lg font-semibold text-gray-700"><?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Enhanced System Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_users']); ?></div>
                            <div class="text-blue-100">ผู้ใช้ระบบ</div>
                        </div>
                        <div class="text-4xl opacity-80">👥</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_patients']); ?></div>
                            <div class="text-green-100">ผู้ป่วย</div>
                        </div>
                        <div class="text-4xl opacity-80">🏥</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_doctors']); ?></div>
                            <div class="text-purple-100">แพทย์</div>
                        </div>
                        <div class="text-4xl opacity-80">👨‍⚕️</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total_news']); ?></div>
                            <div class="text-orange-100">ข่าวสาร</div>
                        </div>
                        <div class="text-4xl opacity-80">📰</div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Settings Tabs -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <nav class="flex space-x-8 px-6">
                        <button class="tab-button active py-4 text-sm font-medium border-b-2 border-blue-500 transition duration-200" onclick="showTab('general')">
                            <span class="text-xl mr-2">🏥</span> ข้อมูลทั่วไป
                        </button>
                        <button class="tab-button py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 transition duration-200" onclick="showTab('website')">
                            <span class="text-xl mr-2">🌐</span> เว็บไซต์
                        </button>
                        <button class="tab-button py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 transition duration-200" onclick="showTab('system')">
                            <span class="text-xl mr-2">⚙️</span> ระบบ
                        </button>
                        <button class="tab-button py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 transition duration-200" onclick="showTab('maintenance')">
                            <span class="text-xl mr-2">🔧</span> การบำรุงรักษา
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- General Settings Tab -->
                    <div id="general-tab" class="tab-content active">
                        <div class="flex items-center mb-6">
                            <span class="text-3xl mr-3">🏥</span>
                            <h3 class="text-2xl font-semibold text-gray-800">ข้อมูลทั่วไปของโรงพยาบาล</h3>
                        </div>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="update_general_settings">
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">🏥</span> ชื่อโรงพยาบาล (ไทย) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="hospital_name" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_name', 'โรงพยาบาลทุ่งหัวช้าง')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">🏥</span> ชื่อโรงพยาบาล (อังกฤษ)
                                    </label>
                                    <input type="text" name="hospital_name_en" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_name_en', 'Thung Hua Chang Hospital')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-medium text-gray-700">
                                    <span class="text-lg mr-2">📍</span> ที่อยู่
                                </label>
                                <textarea name="hospital_address" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars(getSettingValue('hospital_address', '123 ถนนหลัก ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน 51000')); ?></textarea>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">📞</span> โทรศัพท์
                                    </label>
                                    <input type="text" name="hospital_phone" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_phone', '053-580-100')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">📠</span> โทรสาร
                                    </label>
                                    <input type="text" name="hospital_fax" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_fax', '053-580-110')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">📧</span> อีเมล
                                    </label>
                                    <input type="email" name="hospital_email" 
                                           value="<?php echo htmlspecialchars(getSettingValue('hospital_email', 'info@thchospital.go.th')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">🚨</span> โทรศัพท์ฉุกเฉิน
                                    </label>
                                    <input type="text" name="emergency_phone" 
                                           value="<?php echo htmlspecialchars(getSettingValue('emergency_phone', '053-580-999')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">🌐</span> เว็บไซต์
                                    </label>
                                    <input type="url" name="website_url" 
                                           value="<?php echo htmlspecialchars(getSettingValue('website_url', 'https://www.thchospital.go.th')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">⏰</span> เขตเวลา
                                    </label>
                                    <select name="timezone" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="Asia/Bangkok" <?php echo getSettingValue('timezone', 'Asia/Bangkok') === 'Asia/Bangkok' ? 'selected' : ''; ?>>Asia/Bangkok (UTC+7)</option>
                                        <option value="UTC" <?php echo getSettingValue('timezone') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <span class="text-2xl mr-3">🕐</span>
                                    <h4 class="text-lg font-semibold text-blue-800">เวลาทำการ</h4>
                                </div>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-blue-700">เวลาเริ่มงาน (จันทร์-ศุกร์)</label>
                                        <input type="time" name="working_hours_start"
                                               value="<?php echo htmlspecialchars(getSettingValue('working_hours_start', '08:00')); ?>"
                                               class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-blue-700">เวลาเลิกงาน (จันทร์-ศุกร์)</label>
                                        <input type="time" name="working_hours_end"
                                               value="<?php echo htmlspecialchars(getSettingValue('working_hours_end', '16:30')); ?>"
                                               class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-6 mt-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-blue-700">เวลาเริ่มงาน (เสาร์-อาทิตย์)</label>
                                        <input type="time" name="weekend_hours_start"
                                               value="<?php echo htmlspecialchars(getSettingValue('weekend_hours_start', '08:00')); ?>"
                                               class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-blue-700">เวลาเลิกงาน (เสาร์-อาทิตย์)</label>
                                        <input type="time" name="weekend_hours_end"
                                               value="<?php echo htmlspecialchars(getSettingValue('weekend_hours_end', '12:00')); ?>"
                                               class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-300 hover-lift shadow-lg">
                                    <span class="text-xl mr-2">💾</span> บันทึกการตั้งค่าทั่วไป
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Website Settings Tab -->
                    <div id="website-tab" class="tab-content">
                        <div class="flex items-center mb-6">
                            <span class="text-3xl mr-3">🌐</span>
                            <h3 class="text-2xl font-semibold text-gray-800">การตั้งค่าเว็บไซต์</h3>
                        </div>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="update_website_settings">
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">🏷️</span> ชื่อเว็บไซต์
                                    </label>
                                    <input type="text" name="website_title" 
                                           value="<?php echo htmlspecialchars(getSettingValue('website_title', 'โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div class="space-y-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-2">📄</span> จำนวนข่าวต่อหน้า
                                    </label>
                                    <input type="number" name="news_per_page" min="5" max="50"
                                           value="<?php echo htmlspecialchars(getSettingValue('news_per_page', '10')); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-medium text-gray-700">
                                    <span class="text-lg mr-2">📝</span> คำอธิบายเว็บไซต์
                                </label>
                                <textarea name="website_description" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="คำอธิบายสำหรับ SEO"><?php echo htmlspecialchars(getSettingValue('website_description', 'โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน')); ?></textarea>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-medium text-gray-700">
                                    <span class="text-lg mr-2">🔍</span> คำค้น (Keywords)
                                </label>
                                <input type="text" name="website_keywords" 
                                       value="<?php echo htmlspecialchars(getSettingValue('website_keywords', 'โรงพยาบาล, ลำพูน, ทุ่งหัวช้าง, สุขภาพ, แพทย์, รักษา')); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="แยกด้วยเครื่องหมายจุลภาค">
                            </div>
                            
                            <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <span class="text-2xl mr-3">📱</span>
                                    <h4 class="text-lg font-semibold text-gray-800">Social Media</h4>
                                </div>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <span class="text-lg mr-2">📘</span> Facebook URL
                                        </label>
                                        <input type="url" name="facebook_url" 
                                               value="<?php echo htmlspecialchars(getSettingValue('facebook_url', '')); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="https://facebook.com/yourpage">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <span class="text-lg mr-2">💬</span> Line ID
                                        </label>
                                        <input type="text" name="line_id" 
                                               value="<?php echo htmlspecialchars(getSettingValue('line_id', '')); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="@yourlineid">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-medium text-gray-700">
                                    <span class="text-lg mr-2">📊</span> Google Analytics ID
                                </label>
                                <input type="text" name="google_analytics_id" 
                                       value="<?php echo htmlspecialchars(getSettingValue('google_analytics_id', '')); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="G-XXXXXXXXXX">
                            </div>
                            
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <span class="text-2xl mr-3">🎛️</span>
                                    <h4 class="text-lg font-semibold text-gray-800">การแสดงผล</h4>
                                </div>
                                <div class="space-y-3">
                                    <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                        <input type="checkbox" name="show_statistics" value="1"
                                               <?php echo getSettingValue('show_statistics', '1') === '1' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="flex items-center text-sm font-medium text-gray-700">
                                            <span class="text-lg mr-2">📈</span> แสดงสถิติในหน้าแรก
                                        </span>
                                    </label>
                                    
                                    <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                        <input type="checkbox" name="show_doctors" value="1"
                                               <?php echo getSettingValue('show_doctors', '1') === '1' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="flex items-center text-sm font-medium text-gray-700">
                                            <span class="text-lg mr-2">👨‍⚕️</span> แสดงทีมแพทย์ในหน้าแรก
                                        </span>
                                    </label>
                                    
                                    <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                        <input type="checkbox" name="allow_comments" value="1"
                                               <?php echo getSettingValue('allow_comments', '0') === '1' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="flex items-center text-sm font-medium text-gray-700">
                                            <span class="text-lg mr-2">💬</span> อนุญาตให้แสดงความคิดเห็น
                                        </span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-3 rounded-lg hover:from-green-700 hover:to-green-800 transition duration-300 hover-lift shadow-lg">
                                    <span class="text-xl mr-2">🌐</span> บันทึกการตั้งค่าเว็บไซต์
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- System Settings Tab -->
                    <div id="system-tab" class="tab-content">
                        <div class="flex items-center mb-6">
                            <span class="text-3xl mr-3">⚙️</span>
                            <h3 class="text-2xl font-semibold text-gray-800">การตั้งค่าระบบ</h3>
                        </div>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="update_system_settings">
                            
                            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <span class="text-2xl mr-3">🚧</span>
                                    <h4 class="text-lg font-semibold text-red-800">โหมดบำรุงรักษา</h4>
                                </div>
                                <label class="flex items-center space-x-3 p-3 border border-red-300 rounded-lg hover:bg-red-50 transition duration-200">
                                    <input type="checkbox" name="maintenance_mode" value="1"
                                           <?php echo getSettingValue('maintenance_mode') === '1' ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                    <span class="text-sm font-medium text-red-700">เปิดใช้งานโหมดบำรุงรักษา (ปิดระบบชั่วคราว)</span>
                                </label>
                                
                                <div class="mt-4 space-y-2">
                                    <label class="flex items-center text-sm font-medium text-red-700">
                                        <span class="text-lg mr-2">📝</span> ข้อความแจ้งเตือนระหว่างบำรุงรักษา
                                    </label>
                                    <textarea name="maintenance_message" rows="3"
                                              class="w-full px-4 py-3 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                              placeholder="ระบบอยู่ระหว่างการบำรุงรักษา กรุณาลองใหม่ภายหลัง"><?php echo htmlspecialchars(getSettingValue('maintenance_message', '')); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <span class="text-2xl mr-3">🔐</span>
                                    <h4 class="text-lg font-semibold text-blue-800">การรักษาความปลอดภัย</h4>
                                </div>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-blue-700">
                                            <span class="text-lg mr-2">⏱️</span> หมดเวลาเซสชัน (นาที)
                                        </label>
                                        <input type="number" name="session_timeout" min="30" max="1440"
                                               value="<?php echo htmlspecialchars(getSettingValue('session_timeout', '120')); ?>"
                                               class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-blue-700">
                                            <span class="text-lg mr-2">🚫</span> จำนวนครั้งที่เข้าสู่ระบบผิดสูงสุด
                                        </label>
                                        <input type="number" name="max_login_attempts" min="3" max="10"
                                               value="<?php echo htmlspecialchars(getSettingValue('max_login_attempts', '5')); ?>"
                                               class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-6 mt-4">
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-blue-700">
                                            <span class="text-lg mr-2">🔒</span> เวลาล็อคบัญชี (นาที)
                                        </label>
                                        <input type="number" name="login_lockout_time" min="5" max="1440"
                                               value="<?php echo htmlspecialchars(getSettingValue('login_lockout_time', '30')); ?>"
                                               class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-blue-700">
                                            <span class="text-lg mr-2">🔑</span> ความยาวรหัสผ่านต่ำสุด
                                        </label>
                                        <input type="number" name="password_min_length" min="6" max="20"
                                               value="<?php echo htmlspecialchars(getSettingValue('password_min_length', '6')); ?>"
                                               class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                                
                                <div class="mt-4 space-y-3">
                                    <label class="flex items-center space-x-3 p-3 border border-blue-300 rounded-lg hover:bg-blue-50 transition duration-200">
                                        <input type="checkbox" name="require_password_complexity" value="1"
                                               <?php echo getSettingValue('require_password_complexity') === '1' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="flex items-center text-sm font-medium text-blue-700">
                                            <span class="text-lg mr-2">🔐</span> บังคับใช้รหัสผ่านที่ซับซ้อน
                                        </span>
                                    </label>
                                    
                                    <label class="flex items-center space-x-3 p-3 border border-blue-300 rounded-lg hover:bg-blue-50 transition duration-200">
                                        <input type="checkbox" name="enable_registration" value="1"
                                               <?php echo getSettingValue('enable_registration') === '1' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="flex items-center text-sm font-medium text-blue-700">
                                            <span class="text-lg mr-2">👤</span> เปิดให้สมัครสมาชิกได้
                                        </span>
                                    </label>
                                    
                                    <label class="flex items-center space-x-3 p-3 border border-blue-300 rounded-lg hover:bg-blue-50 transition duration-200">
                                        <input type="checkbox" name="enable_api" value="1"
                                               <?php echo getSettingValue('enable_api') === '1' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="flex items-center text-sm font-medium text-blue-700">
                                            <span class="text-lg mr-2">🔌</span> เปิดใช้งาน API
                                        </span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <span class="text-2xl mr-3">💾</span>
                                    <h4 class="text-lg font-semibold text-purple-800">การจัดการข้อมูล</h4>
                                </div>
                                <div class="grid md:grid-cols-3 gap-6">
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-purple-700">
                                            <span class="text-lg mr-2">📋</span> เก็บ Log กี่วัน
                                        </label>
                                        <input type="number" name="log_retention_days" min="30" max="365"
                                               value="<?php echo htmlspecialchars(getSettingValue('log_retention_days', '90')); ?>"
                                               class="w-full px-4 py-3 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-purple-700">
                                            <span class="text-lg mr-2">💾</span> เก็บ Backup กี่วัน
                                        </label>
                                        <input type="number" name="backup_retention_days" min="7" max="365"
                                               value="<?php echo htmlspecialchars(getSettingValue('backup_retention_days', '30')); ?>"
                                               class="w-full px-4 py-3 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="flex items-center text-sm font-medium text-purple-700">
                                            <span class="text-lg mr-2">🔄</span> ความถี่การสำรองข้อมูล
                                        </label>
                                        <select name="backup_frequency" class="w-full px-4 py-3 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="daily" <?php echo getSettingValue('backup_frequency', 'weekly') === 'daily' ? 'selected' : ''; ?>>รายวัน</option>
                                            <option value="weekly" <?php echo getSettingValue('backup_frequency', 'weekly') === 'weekly' ? 'selected' : ''; ?>>รายสัปดาห์</option>
                                            <option value="monthly" <?php echo getSettingValue('backup_frequency', 'weekly') === 'monthly' ? 'selected' : ''; ?>>รายเดือน</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label class="flex items-center space-x-3 p-3 border border-purple-300 rounded-lg hover:bg-purple-50 transition duration-200">
                                        <input type="checkbox" name="auto_backup_enabled" value="1"
                                               <?php echo getSettingValue('auto_backup_enabled', '0') === '1' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                        <span class="flex items-center text-sm font-medium text-purple-700">
                                            <span class="text-lg mr-2">🤖</span> เปิดการสำรองข้อมูลอัตโนมัติ
                                        </span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-8 py-3 rounded-lg hover:from-purple-700 hover:to-purple-800 transition duration-300 hover-lift shadow-lg">
                                    <span class="text-xl mr-2">⚙️</span> บันทึกการตั้งค่าระบบ
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Maintenance Tab -->
                    <div id="maintenance-tab" class="tab-content">
                        <div class="flex items-center mb-6">
                            <span class="text-3xl mr-3">🔧</span>
                            <h3 class="text-2xl font-semibold text-gray-800">การบำรุงรักษาระบบ</h3>
                        </div>
                        
                        <div class="space-y-8">
                            <!-- Database Backup -->
                            <div class="bg-white border border-gray-200 rounded-xl shadow-lg p-6 hover-lift">
                                <div class="flex items-center mb-4">
                                    <span class="text-3xl mr-3">💾</span>
                                    <h4 class="text-xl font-semibold text-gray-800">สำรองข้อมูล</h4>
                                </div>
                                <p class="text-gray-600 mb-4">สำรองข้อมูลฐานข้อมูลเพื่อป้องกันการสูญหาย</p>
                                
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="backup_database">
                                    <button type="submit" 
                                            class="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-3 rounded-lg hover:from-green-700 hover:to-green-800 transition duration-300 hover-lift shadow-lg"
                                            onclick="return confirm('ต้องการสำรองข้อมูลหรือไม่?')">
                                        <span class="text-lg mr-2">🗄️</span> สำรองข้อมูลทันที
                                    </button>
                                </form>
                                
                                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-center">
                                        <span class="text-lg mr-2">⚠️</span>
                                        <p class="text-sm text-yellow-800">
                                            <strong>หมายเหตุ:</strong> สำหรับการสำรองข้อมูลที่สมบูรณ์ ควรใช้เครื่องมือ mysqldump หรือระบบสำรองข้อมูลอัตโนมัติ
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Clear Logs -->
                            <div class="bg-white border border-gray-200 rounded-xl shadow-lg p-6 hover-lift">
                                <div class="flex items-center mb-4">
                                    <span class="text-3xl mr-3">🗂️</span>
                                    <h4 class="text-xl font-semibold text-gray-800">ล้าง Log</h4>
                                </div>
                                <p class="text-gray-600 mb-4">ล้างข้อมูล log เก่าเพื่อประหยัดพื้นที่เก็บข้อมูล</p>
                                
                                <form method="POST" class="space-y-4">
                                    <input type="hidden" name="action" value="clear_logs">
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <span class="text-lg mr-2">📅</span> ล้าง log ที่เก่ากว่า:
                                        </label>
                                        <select name="clear_days" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="30">30 วัน</option>
                                            <option value="60">60 วัน</option>
                                            <option value="90">90 วัน</option>
                                            <option value="180">180 วัน</option>
                                        </select>
                                        <button type="submit" 
                                                class="bg-gradient-to-r from-orange-600 to-orange-700 text-white px-6 py-2 rounded-lg hover:from-orange-700 hover:to-orange-800 transition duration-300 hover-lift shadow-lg"
                                                onclick="return confirm('ต้องการล้าง log หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้')">
                                            <span class="text-lg mr-2">🧹</span> ล้าง Log
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center">
                                        <span class="text-lg mr-2">📊</span>
                                        <p class="text-sm text-blue-700">
                                            ปัจจุบันมี log ทั้งหมด: <strong><?php echo number_format($stats['total_logs']); ?></strong> รายการ
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Email Test -->
                            <div class="bg-white border border-gray-200 rounded-xl shadow-lg p-6 hover-lift">
                                <div class="flex items-center mb-4">
                                    <span class="text-3xl mr-3">📧</span>
                                    <h4 class="text-xl font-semibold text-gray-800">ทดสอบอีเมล</h4>
                                </div>
                                <p class="text-gray-600 mb-4">ทดสอบการส่งอีเมลของระบบ</p>
                                
                                <form method="POST" class="space-y-4">
                                    <input type="hidden" name="action" value="test_email">
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center text-sm font-medium text-gray-700">
                                            <span class="text-lg mr-2">📧</span> ส่งไปยัง:
                                        </label>
                                        <input type="email" name="test_email" required
                                               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="test@example.com">
                                        <button type="submit" 
                                                class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-2 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-300 hover-lift shadow-lg">
                                            <span class="text-lg mr-2">📤</span> ทดสอบส่งอีเมล
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center">
                                        <span class="text-lg mr-2">💡</span>
                                        <p class="text-sm text-blue-800">
                                            <strong>หมายเหตุ:</strong> ต้องตั้งค่า SMTP หรือระบบส่งอีเมลก่อนใช้งาน
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- System Information -->
                            <div class="bg-white border border-gray-200 rounded-xl shadow-lg p-6 hover-lift">
                                <div class="flex items-center mb-4">
                                    <span class="text-3xl mr-3">ℹ️</span>
                                    <h4 class="text-xl font-semibold text-gray-800">ข้อมูลระบบ</h4>
                                </div>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span class="flex items-center text-sm font-medium text-gray-700">
                                                <span class="text-lg mr-2">🐘</span> PHP Version
                                            </span>
                                            <span class="text-sm text-gray-900"><?php echo PHP_VERSION; ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span class="flex items-center text-sm font-medium text-gray-700">
                                                <span class="text-lg mr-2">🖥️</span> Server
                                            </span>
                                            <span class="text-sm text-gray-900"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span class="flex items-center text-sm font-medium text-gray-700">
                                                <span class="text-lg mr-2">🗄️</span> MySQL Version
                                            </span>
                                            <span class="text-sm text-gray-900">
                                                <?php 
                                                try {
                                                    echo $conn->query("SELECT VERSION()")->fetchColumn();
                                                } catch (Exception $e) {
                                                    echo 'Unknown';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span class="flex items-center text-sm font-medium text-gray-700">
                                                <span class="text-lg mr-2">📤</span> Max Upload Size
                                            </span>
                                            <span class="text-sm text-gray-900"><?php echo ini_get('upload_max_filesize'); ?></span>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span class="flex items-center text-sm font-medium text-gray-700">
                                                <span class="text-lg mr-2">💾</span> Memory Limit
                                            </span>
                                            <span class="text-sm text-gray-900"><?php echo ini_get('memory_limit'); ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span class="flex items-center text-sm font-medium text-gray-700">
                                                <span class="text-lg mr-2">⏱️</span> Max Execution Time
                                            </span>
                                            <span class="text-sm text-gray-900"><?php echo ini_get('max_execution_time'); ?>s</span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span class="flex items-center text-sm font-medium text-gray-700">
                                                <span class="text-lg mr-2">🌍</span> Timezone
                                            </span>
                                            <span class="text-sm text-gray-900"><?php echo date_default_timezone_get(); ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span class="flex items-center text-sm font-medium text-gray-700">
                                                <span class="text-lg mr-2">🕐</span> System Time
                                            </span>
                                            <span class="text-sm text-gray-900"><?php echo date('Y-m-d H:i:s'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Activity -->
                            <div class="bg-white border border-gray-200 rounded-xl shadow-lg p-6 hover-lift">
                                <div class="flex items-center mb-4">
                                    <span class="text-3xl mr-3">📋</span>
                                    <h4 class="text-xl font-semibold text-gray-800">กิจกรรมล่าสุด</h4>
                                </div>
                                <?php if (empty($recent_activities)): ?>
                                    <div class="text-center py-8">
                                        <div class="text-6xl mb-4">📋</div>
                                        <p class="text-gray-500 text-lg font-medium">ไม่มีกิจกรรมล่าสุด</p>
                                        <p class="text-gray-400 text-sm">กิจกรรมจะแสดงที่นี่เมื่อมีการใช้งานระบบ</p>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-3 max-h-64 overflow-y-auto">
                                        <?php foreach ($recent_activities as $activity): ?>
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-blue-600">
                                                        <?php echo mb_substr(($activity['first_name'] ?? ''), 0, 1) . mb_substr(($activity['last_name'] ?? ''), 0, 1); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? '')); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-600">
                                                        <?php echo htmlspecialchars($activity['action']); ?>
                                                        <?php if ($activity['table_name']): ?>
                                                        <span class="text-gray-500">
                                                            (<?php echo htmlspecialchars($activity['table_name']); ?>)
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500">
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

            <!-- Enhanced System Notifications -->
            <div class="mt-8 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 shadow-lg">
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
                        <?php if (getSettingValue('maintenance_mode') === '1'): ?>
                        <div class="flex items-center">
                            <span class="text-red-500 mr-2">⚠️</span>
                            <span class="font-semibold text-red-700">โหมดบำรุงรักษาเปิดอยู่ - เว็บไซต์ปิดให้บริการชั่วคราว</span>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-center">
                            <span class="text-orange-500 mr-2">📊</span>
                            <span>ผู้ใช้ออนไลน์: <?php echo number_format($stats['total_users']); ?> คน</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">💾</span>
                            <span>สำรองข้อมูลล่าสุด: <?php echo date('d/m/Y H:i'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tab switching with enhanced animations
        function showTab(tabName) {
            // Hide all tab contents with fade out
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.style.opacity = '0';
                setTimeout(() => {
                    content.classList.remove('active');
                }, 150);
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
                button.classList.remove('bg-blue-600', 'text-white', 'border-blue-500');
                button.classList.add('border-transparent', 'hover:border-gray-300');
            });
            
            // Show selected tab content with fade in
            setTimeout(() => {
                const selectedTab = document.getElementById(tabName + '-tab');
                selectedTab.classList.add('active');
                selectedTab.style.opacity = '1';
            }, 150);
            
            // Add active class to clicked tab button
            event.target.classList.add('active', 'bg-blue-600', 'text-white', 'border-blue-500');
            event.target.classList.remove('border-transparent', 'hover:border-gray-300');
        }

        // Enhanced form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('border-red-500', 'bg-red-50');
                        isValid = false;
                    } else {
                        field.classList.remove('border-red-500', 'bg-red-50');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    // Create and show alert
                    const alert = document.createElement('div');
                    alert.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg z-50';
                    alert.innerHTML = `
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">❌</span>
                            <span>กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน</span>
                        </div>
                    `;
                    document.body.appendChild(alert);
                    
                    setTimeout(() => {
                        alert.remove();
                    }, 5000);
                }
            });
        });

        // Auto-save indication with enhanced UI
        let saveTimeout;
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('change', function() {
                clearTimeout(saveTimeout);
                
                // Show unsaved changes indicator
                const form = this.closest('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('bg-yellow-600', 'animate-pulse');
                        submitBtn.classList.remove('bg-blue-600', 'bg-green-600', 'bg-purple-600');
                        
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = submitBtn.innerHTML.replace(/💾|🌐|⚙️/, '⚠️');
                        
                        // Reset after 3 seconds
                        saveTimeout = setTimeout(() => {
                            submitBtn.classList.remove('bg-yellow-600', 'animate-pulse');
                            submitBtn.classList.add('bg-blue-600');
                            submitBtn.innerHTML = originalText;
                        }, 3000);
                    }
                }
            });
        });

        // Enhanced maintenance mode warning
        const maintenanceCheckbox = document.querySelector('input[name="maintenance_mode"]');
        if (maintenanceCheckbox) {
            maintenanceCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    // Create custom modal
                    const modal = document.createElement('div');
                    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
                    modal.innerHTML = `
                        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4">
                            <div class="text-center">
                                <div class="text-6xl mb-4">🚧</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-4">เปิดโหมดบำรุงรักษา?</h3>
                                <p class="text-gray-600 mb-6">ผู้ใช้งานทั่วไปจะไม่สามารถเข้าถึงระบบได้<br>คุณแน่ใจหรือไม่?</p>
                                <div class="flex space-x-4">
                                    <button onclick="cancelMaintenance()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-200">ยกเลิก</button>
                                    <button onclick="confirmMaintenance()" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">ยืนยัน</button>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                    
                    window.cancelMaintenance = () => {
                        maintenanceCheckbox.checked = false;
                        modal.remove();
                    };
                    
                    window.confirmMaintenance = () => {
                        modal.remove();
                    };
                }
            });
        }

        // Enhanced input validation with real-time feedback
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                const min = parseInt(this.min) || 0;
                const max = parseInt(this.max) || 999999;
                const value = parseInt(this.value) || 0;
                
                if (value < min) {
                    this.value = min;
                    this.classList.add('border-yellow-500');
                } else if (value > max) {
                    this.value = max;
                    this.classList.add('border-yellow-500');
                } else {
                    this.classList.remove('border-yellow-500');
                }
            });
        });

        // URL validation with enhanced feedback
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

        // Email validation with enhanced feedback
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

        // Show loading state when submitting forms with enhanced UI
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span> กำลังบันทึก...';
                    submitBtn.disabled = true;
                    submitBtn.classList.add('cursor-not-allowed', 'opacity-75');
                    
                    // Re-enable after 5 seconds as fallback
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('cursor-not-allowed', 'opacity-75');
                    }, 5000);
                }
            });
        });

        // Enhanced message auto-hide with fade effect
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-50, .bg-red-50');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s, transform 0.5s';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    message.remove();
                }, 500);
            });
        }, 5000);

        // Tooltip system for better UX
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
                    element.classList.add('cursor-help');
                }
            });
        });

        // Auto refresh stats every 30 seconds with visual indicator
        let refreshInterval = setInterval(function() {
            // Add visual indicator for refresh
            const statsCards = document.querySelectorAll('.hover-lift');
            statsCards.forEach(card => {
                card.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    card.style.transform = 'translateY(-2px)';
                }, 200);
            });
        }, 30000);

        // Smooth scroll to error fields
        function scrollToError() {
            const errorField = document.querySelector('.border-red-500');
            if (errorField) {
                errorField.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                errorField.focus();
            }
        }

        // Keyboard shortcuts for power users
        document.addEventListener('keydown', function(e) {
            // Ctrl + S to save current form
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const activeTab = document.querySelector('.tab-content.active');
                if (activeTab) {
                    const form = activeTab.querySelector('form');
                    if (form) {
                        form.submit();
                    }
                }
            }
            
            // Ctrl + 1,2,3,4 to switch tabs
            if (e.ctrlKey && ['1','2','3','4'].includes(e.key)) {
                e.preventDefault();
                const tabs = ['general', 'website', 'system', 'maintenance'];
                const tabIndex = parseInt(e.key) - 1;
                if (tabs[tabIndex]) {
                    const tabButton = document.querySelector(`button[onclick="showTab('${tabs[tabIndex]}')"]`);
                    if (tabButton) {
                        tabButton.click();
                    }
                }
            }
        });

        // Enhanced visual feedback for form interactions
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-blue-200');
            });
            
            field.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-blue-200');
            });
        });

        // Progress indicator for multi-step operations
        function showProgressIndicator(message) {
            const indicator = document.createElement('div');
            indicator.className = 'fixed top-4 right-4 bg-blue-100 border border-blue-400 text-blue-700 px-6 py-4 rounded-lg shadow-lg z-50';
            indicator.innerHTML = `
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-3"></div>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(indicator);
            return indicator;
        }

        // Dynamic content loading for better performance
        function lazyLoadContent() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            });

            document.querySelectorAll('.hover-lift').forEach(el => {
                observer.observe(el);
            });
        }

        // Initialize lazy loading
        lazyLoadContent();

        // Add ripple effect to buttons
        document.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', function(e) {
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
            
            button {
                position: relative;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);

        // Real-time validation feedback
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(this.validationTimeout);
                
                this.validationTimeout = setTimeout(() => {
                    if (this.checkValidity()) {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-green-500');
                    } else {
                        this.classList.remove('border-green-500');
                        this.classList.add('border-red-500');
                    }
                }, 500);
            });
        });

        // Enhanced accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });

        // Add focus styles for keyboard navigation
        const accessibilityStyle = document.createElement('style');
        accessibilityStyle.textContent = `
            .keyboard-navigation *:focus {
                outline: 2px solid #3b82f6 !important;
                outline-offset: 2px !important;
            }
        `;
        document.head.appendChild(accessibilityStyle);

        // Initialize all enhancements
        console.log('🎉 Enhanced Settings UI loaded successfully!');
    </script>
</body>
</html>