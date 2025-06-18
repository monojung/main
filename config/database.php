<?php
/**
 * Database Configuration
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
define('SITE_URL', 'https://www.thchospital.go.th');
define('ADMIN_EMAIL', 'admin@thchospital.go.th');

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
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            echo "Database connection failed.";
        }

        return $this->conn;
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
            $_SESSION['user_ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
            $_SESSION['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }
        
        // Validate session
        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')) {
            session_destroy();
            return false;
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
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@thchospital.go.th');
define('SMTP_PASS', 'your_email_password');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'));
define('UPLOAD_PATH', 'uploads/');

// Hospital specific settings
$hospital_departments = array(
    'general' => 'แพทย์ทั่วไป',
    'pediatric' => 'กุมารเวชกรรม',
    'obstetric' => 'สูติ-นรีเวชกรรม',
    'surgery' => 'ศัลยกรรม',
    'orthopedic' => 'ออร์โธปิดิกส์',
    'dental' => 'ทันตกรรม',
    'emergency' => 'แผนกฉุกเฉิน',
    'lab' => 'ห้องปฏิบัติการ'
);

$appointment_times = array(
    '08:00', '09:00', '10:00', '11:00',
    '13:00', '14:00', '15:00', '16:00'
);

// Hospital contact information
$hospital_info = array(
    'name' => 'โรงพยาบาลทุ่งหัวช้าง',
    'name_en' => 'Tung Hua Chang Hospital',
    'address' => '123 ถนนหลัก ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน 51000',
    'phone' => '053-580-xxx',
    'fax' => '053-580-xxx',
    'email' => 'info@thchospital.go.th',
    'emergency' => '053-580-xxx',
    'website' => 'https://www.thchospital.go.th'
);

// Operating hours
$operating_hours = array(
    'weekday' => array('start' => '08:00', 'end' => '16:30'),
    'weekend' => array('start' => '08:00', 'end' => '12:00'),
    'emergency' => '24 ชั่วโมง'
);

// Social media links
$social_media = array(
    'facebook' => 'https://facebook.com/thchospital',
    'line' => '@thchospital',
    'youtube' => 'https://youtube.com/thchospital'
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
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
        ));
    } catch (Exception $e) {
        logError("Failed to log activity: " . $e->getMessage());
        return false;
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

// Generate patient ID
function generatePatientId($conn) {
    try {
        if (!$conn) return null;
        
        $year = date('Y');
        
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM patients 
            WHERE YEAR(created_at) = ?
        ");
        $stmt->execute(array($year));
        $result = $stmt->fetch();
        
        $num = str_pad($result['count'] + 1, 6, '0', STR_PAD_LEFT);
        return 'P' . $year . $num;
        
    } catch (Exception $e) {
        logError("Failed to generate patient ID: " . $e->getMessage());
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
            INSERT INTO settings (setting_key, setting_value, setting_type, description) 
            VALUES (?, ?, ?, ?)
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

// Initialize secure session only if not in CLI mode
if (php_sapi_name() !== 'cli') {
    startSecureSession();
}
?>