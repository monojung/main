<?php
// ฟังก์ชันสำหรับการจัดการระบบโรงพยาบาลทุ่งหัวช้าง

// Helper Functions - ตรวจสอบว่ามีฟังก์ชันอยู่แล้วหรือไม่
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirectTo')) {
    function redirectTo($url) {
        header("Location: $url");
        exit();
    }
}

if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

// Thai Date Functions
if (!function_exists('formatThaiDate')) {
    function formatThaiDate($date) {
        if (!$date) return 'ไม่ระบุ';
        
        $thai_months = [
            1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
        ];
        
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = $thai_months[(int)date('n', $timestamp)];
        $year = date('Y', $timestamp) + 543;
        
        return "$day $month $year";
    }
}

if (!function_exists('formatThaiDateTime')) {
    function formatThaiDateTime($datetime) {
        if (!$datetime) return 'ไม่ระบุ';
        
        $date_part = formatThaiDate($datetime);
        $time_part = date('H:i', strtotime($datetime));
        
        return "$date_part เวลา $time_part น.";
    }
}

if (!function_exists('safeFormatThaiDate')) {
    function safeFormatThaiDate($date) {
        if (!$date) return 'ไม่ระบุ';
        try {
            return formatThaiDate($date);
        } catch (Exception $e) {
            return date('d/m/Y', strtotime($date));
        }
    }
}

if (!function_exists('safeFormatThaiDateTime')) {
    function safeFormatThaiDateTime($datetime) {
        if (!$datetime) return 'ไม่ระบุ';
        try {
            return formatThaiDateTime($datetime);
        } catch (Exception $e) {
            return date('d/m/Y H:i', strtotime($datetime));
        }
    }
}

// Authentication Functions
if (!function_exists('requireAdmin')) {
    function requireAdmin($redirect_url = '../login.php') {
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            redirectTo($redirect_url);
        }
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            redirectTo($redirect_url);
        }
    }
}

if (!function_exists('requireAuth')) {
    function requireAuth($redirect_url = '../login.php') {
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            redirectTo($redirect_url);
        }
    }
}

// Logging Functions
if (!function_exists('logError')) {
    function logError($message, $file = '', $line = '') {
        $log_message = date('Y-m-d H:i:s') . " ERROR: $message";
        if ($file) $log_message .= " in $file";
        if ($line) $log_message .= " on line $line";
        $log_message .= "\n";
        
        error_log($log_message, 3, '../logs/error.log');
    }
}

if (!function_exists('logActivity')) {
    function logActivity($conn, $user_id, $action, $table_name = '', $record_id = '', $old_data = null, $new_data = null) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO activity_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $old_data_json = $old_data ? json_encode($old_data) : null;
            $new_data_json = $new_data ? json_encode($new_data) : null;
            
            $stmt->execute([
                $user_id,
                $action,
                $table_name,
                $record_id,
                $old_data_json,
                $new_data_json,
                $ip_address,
                $user_agent
            ]);
        } catch (Exception $e) {
            logError("Failed to log activity: " . $e->getMessage());
        }
    }
}

// Statistics Functions
if (!function_exists('getTotalUsers')) {
    function getTotalUsers($conn) {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
            $stmt->execute();
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            logError($e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('getTotalNews')) {
    function getTotalNews($conn) {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
            $stmt->execute();
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            logError($e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('getNewsThisMonth')) {
    function getNewsThisMonth($conn) {
        try {
            $this_month = date('Y-m');
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE created_at LIKE ? AND status = 'published'");
            $stmt->execute([$this_month . '%']);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            logError($e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('getTodayNews')) {
    function getTodayNews($conn) {
        try {
            $today = date('Y-m-d');
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE DATE(created_at) = ? AND status = 'published'");
            $stmt->execute([$today]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            logError($e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('getMonthlyStats')) {
    function getMonthlyStats($conn, $months = 6) {
        $stats = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            try {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE created_at LIKE ? AND status = 'published'");
                $stmt->execute([$month . '%']);
                $count = $stmt->fetchColumn() ?? 0;
            } catch (Exception $e) {
                $count = 0;
            }
            
            $stats[] = [
                'month' => $month,
                'month_name' => date('M Y', strtotime($month . '-01')),
                'news' => $count
            ];
        }
        return $stats;
    }
}

if (!function_exists('getRecentNews')) {
    function getRecentNews($conn, $limit = 5) {
        try {
            $stmt = $conn->prepare("
                SELECT n.*, u.first_name, u.last_name 
                FROM news n 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published'
                ORDER BY n.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError($e->getMessage());
            return [];
        }
    }
}

// File Upload Functions
if (!function_exists('uploadFile')) {
    function uploadFile($file, $upload_dir = '../uploads/', $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed');
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception('File type not allowed');
        }
        
        $max_size = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $max_size) {
            throw new Exception('File too large');
        }
        
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        return $filename;
    }
}

if (!function_exists('deleteFile')) {
    function deleteFile($filename, $upload_dir = '../uploads/') {
        $filepath = $upload_dir . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return true;
    }
}

// Utility Functions
if (!function_exists('generateSlug')) {
    function generateSlug($text) {
        // Convert Thai characters to readable format
        $text = str_replace(' ', '-', $text);
        $text = preg_replace('/[^a-zA-Z0-9\-\u0E00-\u0E7F]/', '', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        
        return strtolower($text) . '-' . time();
    }
}

if (!function_exists('truncateText')) {
    function truncateText($text, $length = 100) {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '...';
    }
}

if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Database Functions
if (!function_exists('executeQuery')) {
    function executeQuery($conn, $query, $params = []) {
        try {
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (Exception $e) {
            logError("Database query failed: " . $e->getMessage());
            throw $e;
        }
    }
}

if (!function_exists('getRecord')) {
    function getRecord($conn, $table, $id, $id_field = 'id') {
        try {
            $stmt = $conn->prepare("SELECT * FROM $table WHERE $id_field = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logError($e->getMessage());
            return false;
        }
    }
}

if (!function_exists('deleteRecord')) {
    function deleteRecord($conn, $table, $id, $id_field = 'id') {
        try {
            $stmt = $conn->prepare("DELETE FROM $table WHERE $id_field = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            logError($e->getMessage());
            return false;
        }
    }
}

// Pagination Functions
if (!function_exists('getPagination')) {
    function getPagination($total_records, $records_per_page, $current_page) {
        $total_pages = ceil($total_records / $records_per_page);
        $offset = ($current_page - 1) * $records_per_page;
        
        return [
            'total_pages' => $total_pages,
            'current_page' => $current_page,
            'offset' => $offset,
            'limit' => $records_per_page,
            'has_prev' => $current_page > 1,
            'has_next' => $current_page < $total_pages,
            'prev_page' => $current_page - 1,
            'next_page' => $current_page + 1
        ];
    }
}

// Notification Functions
if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

if (!function_exists('getFlash')) {
    function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

// ITA Management Functions
function getTotalITA($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ita_requests WHERE status != 'deleted'");
        $stmt->execute();
        return $stmt->fetch()['count'] ?? 0;
    } catch (Exception $e) {
        logError($e->getMessage());
        return 0;
    }
}

function getPendingITA($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ita_requests WHERE status = 'pending'");
        $stmt->execute();
        return $stmt->fetch()['count'] ?? 0;
    } catch (Exception $e) {
        logError($e->getMessage());
        return 0;
    }
}

function getApprovedITA($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ita_requests WHERE status = 'approved'");
        $stmt->execute();
        return $stmt->fetch()['count'] ?? 0;
    } catch (Exception $e) {
        logError($e->getMessage());
        return 0;
    }
}

// Report Functions
function generateSystemReport($conn) {
    $report = [
        'total_users' => getTotalUsers($conn),
        'total_news' => getTotalNews($conn),
        'total_ita' => getTotalITA($conn),
        'pending_ita' => getPendingITA($conn),
        'approved_ita' => getApprovedITA($conn),
        'news_this_month' => getNewsThisMonth($conn),
        'generated_at' => date('Y-m-d H:i:s')
    ];
    
    return $report;
}

// Email Functions (if needed)
function sendEmail($to, $subject, $message, $from = 'noreply@tunghuachang-hospital.com') {
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Configuration Functions
function getSystemConfig($conn, $key = null) {
    try {
        if ($key) {
            $stmt = $conn->prepare("SELECT config_value FROM system_config WHERE config_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            return $result ? $result['config_value'] : null;
        } else {
            $stmt = $conn->prepare("SELECT config_key, config_value FROM system_config");
            $stmt->execute();
            $config = [];
            while ($row = $stmt->fetch()) {
                $config[$row['config_key']] = $row['config_value'];
            }
            return $config;
        }
    } catch (Exception $e) {
        logError($e->getMessage());
        return $key ? null : [];
    }
}

function setSystemConfig($conn, $key, $value) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO system_config (config_key, config_value, updated_at) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE config_value = ?, updated_at = NOW()
        ");
        return $stmt->execute([$key, $value, $value]);
    } catch (Exception $e) {
        logError($e->getMessage());
        return false;
    }
}

// Security Functions
function checkCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCSRF() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function getRateLimitKey($action, $ip = null) {
    $ip = $ip ?: $_SERVER['REMOTE_ADDR'];
    return "rate_limit_{$action}_{$ip}";
}

function isRateLimited($action, $max_attempts = 5, $time_window = 300) {
    $key = getRateLimitKey($action);
    $attempts = $_SESSION[$key] ?? 0;
    
    if ($attempts >= $max_attempts) {
        return true;
    }
    
    return false;
}

function incrementRateLimit($action) {
    $key = getRateLimitKey($action);
    $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
}

function resetRateLimit($action) {
    $key = getRateLimitKey($action);
    unset($_SESSION[$key]);
}
?>