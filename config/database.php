<?php
/**
 * Database Configuration - ปรับปรุงแล้ว
 * โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'thc_hospital');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'โรงพยาบาลทุ่งหัวช้าง');
define('SITE_URL', 'http://localhost/hospital'); // เปลี่ยนตาม URL ของคุณ
define('ADMIN_EMAIL', 'admin@thchospital.go.th');

// Timezone setting
date_default_timezone_set('Asia/Bangkok');

// Error reporting (ปิดในโปรดักชัน)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ");
        }

        return $this->conn;
    }
    
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return $conn !== null;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Utility functions
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function formatThaiDate($date) {
    if (empty($date)) return '';
    
    $thai_months = array(
        '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
        '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
        '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน',
        '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
    );
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    if (!$timestamp) return $date;
    
    $year = date('Y', $timestamp) + 543; // Convert to Buddhist year
    $month = $thai_months[date('m', $timestamp)];
    $day = (int)date('d', $timestamp);
    
    return "$day $month $year";
}

function formatThaiDateTime($datetime) {
    if (empty($datetime)) return '';
    
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    if (!$timestamp) return $datetime;
    
    $date_part = formatThaiDate($timestamp);
    $time_part = date('H:i', $timestamp);
    
    return $date_part . ' เวลา ' . $time_part . ' น.';
}

function redirectTo($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
        exit();
    }
}

// Error logging function
function logError($message, $file = '', $line = '') {
    $log_dir = dirname(__FILE__) . '/../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_message = date('Y-m-d H:i:s') . " - Error: $message";
    if ($file) $log_message .= " in $file";
    if ($line) $log_message .= " on line $line";
    $log_message .= "\n";
    
    error_log($log_message, 3, $log_dir . '/error.log');
}

// Session management
function startSecureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        // Secure session settings
        if (function_exists('ini_set')) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        }
        
        session_start();
        
        // Regenerate session ID for security
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
    }
    return true;
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Password utilities
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Hospital specific settings
$hospital_departments = array(
    'GEN' => 'แพทย์ทั่วไป',
    'PED' => 'กุมารเวชกรรม',
    'OBS' => 'สูติ-นรีเวชกรรม',
    'SUR' => 'ศัลยกรรม',
    'ORT' => 'ออร์โธปิดิกส์',
    'DEN' => 'ทันตกรรม',
    'EMR' => 'แผนกฉุกเฉิน',
    'LAB' => 'ห้องปฏิบัติการ'
);

$appointment_times = array(
    '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
);

// Hospital contact information
$hospital_info = array(
    'name' => 'โรงพยาบาลทุ่งหัวช้าง',
    'name_en' => 'Tung Hua Chang Hospital',
    'address' => '123 ถนนหลัก ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน 51000',
    'phone' => '053-580-100',
    'fax' => '053-580-110',
    'email' => 'info@thchospital.go.th',
    'emergency' => '053-580-999',
    'website' => 'https://www.thchospital.go.th'
);

// Operating hours
$operating_hours = array(
    'weekday' => array('start' => '08:00', 'end' => '16:30'),
    'weekend' => array('start' => '08:00', 'end' => '12:00'),
    'emergency' => '24 ชั่วโมง'
);

// Activity logging function
function logActivity($conn, $user_id, $action, $table_name = '', $record_id = null, $old_values = null, $new_values = null) {
    try {
        if (!$conn) return false;
        
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $old_values_json = $old_values ? json_encode($old_values, JSON_UNESCAPED_UNICODE) : null;
        $new_values_json = $new_values ? json_encode($new_values, JSON_UNESCAPED_UNICODE) : null;
        
        return $stmt->execute(array(
            $user_id,
            $action,
            $table_name,
            $record_id,
            $old_values_json,
            $new_values_json,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ));
    } catch (Exception $e) {
        logError("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

// Generate appointment number
function generateAppointmentNumber($conn, $department_id) {
    try {
        if (!$conn) return null;
        
        $dept = getDepartmentById($conn, $department_id);
        if (!$dept) return null;
        
        $date_str = date('Ymd');
        $dept_code = $dept['code'];
        
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM appointments 
            WHERE appointment_date = CURDATE() 
            AND appointment_number LIKE ?
        ");
        $stmt->execute(array($dept_code . $date_str . '%'));
        $result = $stmt->fetch();
        
        $num = str_pad($result['count'] + 1, 3, '0', STR_PAD_LEFT);
        return $dept_code . $date_str . $num;
        
    } catch (Exception $e) {
        logError("Failed to generate appointment number: " . $e->getMessage());
        return null;
    }
}

// Get department by ID
function getDepartmentById($conn, $id) {
    try {
        if (!$conn) return null;
        
        $stmt = $conn->prepare("SELECT * FROM departments WHERE id = ? AND is_active = 1");
        $stmt->execute(array($id));
        return $stmt->fetch();
    } catch (Exception $e) {
        logError("Failed to get department: " . $e->getMessage());
        return null;
    }
}

// Get department by code
function getDepartmentByCode($conn, $code) {
    try {
        if (!$conn) return null;
        
        $stmt = $conn->prepare("SELECT * FROM departments WHERE code = ? AND is_active = 1");
        $stmt->execute(array($code));
        return $stmt->fetch();
    } catch (Exception $e) {
        logError("Failed to get department: " . $e->getMessage());
        return null;
    }
}

// Validate Thai ID card number
function validateThaiID($id) {
    if (strlen($id) != 13) return false;
    if (!ctype_digit($id)) return false;
    
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += (int)$id[$i] * (13 - $i);
    }
    
    $check = (11 - ($sum % 11)) % 10;
    return $check == (int)$id[12];
}

// Get setting value
function getSetting($conn, $key, $default = '') {
    try {
        if (!$conn) return $default;
        
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute(array($key));
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        logError("Failed to get setting: " . $e->getMessage());
        return $default;
    }
}

// Set setting value
function setSetting($conn, $key, $value, $type = 'string', $description = '') {
    try {
        if (!$conn) return false;
        
        $stmt = $conn->prepare("
            INSERT INTO settings (setting_key, setting_value, setting_type, description, updated_at) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value),
            setting_type = VALUES(setting_type),
            description = VALUES(description),
            updated_at = NOW()
        ");
        return $stmt->execute(array($key, $value, $type, $description));
    } catch (Exception $e) {
        logError("Failed to set setting: " . $e->getMessage());
        return false;
    }
}

// Rate limiting for login attempts
function checkRateLimit($identifier, $max_attempts = 5, $window_minutes = 15) {
    $cache_key = "rate_limit_" . md5($identifier);
    $cache_file = sys_get_temp_dir() . "/" . $cache_key;
    
    $attempts = [];
    if (file_exists($cache_file)) {
        $attempts = json_decode(file_get_contents($cache_file), true) ?: [];
    }
    
    // Remove old attempts outside the window
    $window_start = time() - ($window_minutes * 60);
    $attempts = array_filter($attempts, function($timestamp) use ($window_start) {
        return $timestamp > $window_start;
    });
    
    if (count($attempts) >= $max_attempts) {
        return false; // Rate limit exceeded
    }
    
    // Add current attempt
    $attempts[] = time();
    file_put_contents($cache_file, json_encode($attempts));
    
    return true;
}

// Initialize secure session
if (php_sapi_name() !== 'cli') {
    startSecureSession();
}

// Test database connection on include
try {
    $test_db = new Database();
    if (!$test_db->testConnection()) {
        error_log("Database connection test failed");
    }
} catch (Exception $e) {
    error_log("Database test error: " . $e->getMessage());
}
?>