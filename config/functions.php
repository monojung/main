<?php
/**
 * Utility Functions for Hospital Management System
 * File: config/functions.php
 */

/**
 * Rate limiting function
 * @param string $key - Unique identifier for rate limiting
 * @param int $max_attempts - Maximum attempts allowed
 * @param int $window_minutes - Time window in minutes
 * @return bool - True if within rate limit, false if exceeded
 */
if (!function_exists('checkRateLimit')) {
    function checkRateLimit($key, $max_attempts = 5, $window_minutes = 15) {
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }
        
        $now = time();
        $window_seconds = $window_minutes * 60;
        
        // Clean old entries
        foreach ($_SESSION['rate_limits'] as $rate_key => $data) {
            if ($now - $data['first_attempt'] > $window_seconds) {
                unset($_SESSION['rate_limits'][$rate_key]);
            }
        }
        
        // Check current key
        if (!isset($_SESSION['rate_limits'][$key])) {
            $_SESSION['rate_limits'][$key] = [
                'attempts' => 1,
                'first_attempt' => $now
            ];
            return true;
        }
        
        $rate_data = $_SESSION['rate_limits'][$key];
        
        // If window has passed, reset
        if ($now - $rate_data['first_attempt'] > $window_seconds) {
            $_SESSION['rate_limits'][$key] = [
                'attempts' => 1,
                'first_attempt' => $now
            ];
            return true;
        }
        
        // Increment attempts
        $_SESSION['rate_limits'][$key]['attempts']++;
        
        return $_SESSION['rate_limits'][$key]['attempts'] <= $max_attempts;
    }
}

/**
 * Sanitize input data
 * @param string $input - Input to sanitize
 * @return string - Sanitized input
 */
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
}

/**
 * Verify password against hash
 * @param string $password - Plain text password
 * @param string $hash - Hashed password
 * @return bool - True if password matches
 */
if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

/**
 * Hash password
 * @param string $password - Plain text password
 * @return string - Hashed password
 */
if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

/**
 * Redirect to a page
 * @param string $url - URL to redirect to
 */
if (!function_exists('redirectTo')) {
    function redirectTo($url) {
        // Ensure URL starts with / or is a relative path
        if (!preg_match('/^https?:\/\//', $url)) {
            if (strpos($url, '/') !== 0) {
                $url = '/' . $url;
            }
        }
        
        header("Location: $url");
        exit();
    }
}

/**
 * Log activity to database
 * @param PDO $conn - Database connection
 * @param int|null $user_id - User ID (null for anonymous)
 * @param string $action - Action performed
 * @param string $table_name - Table affected
 * @param int|null $record_id - Record ID affected
 * @param string|null $old_data - Old data (JSON)
 * @param array|null $additional_data - Additional data
 */
if (!function_exists('logActivity')) {
    function logActivity($conn, $user_id, $action, $table_name, $record_id = null, $old_data = null, $additional_data = null) {
        try {
            // Check if activity_logs table exists
            $stmt = $conn->prepare("SHOW TABLES LIKE 'activity_logs'");
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                // Create activity_logs table if it doesn't exist
                $conn->exec("
                    CREATE TABLE activity_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NULL,
                        action VARCHAR(100) NOT NULL,
                        table_name VARCHAR(50) NOT NULL,
                        record_id INT NULL,
                        old_data TEXT NULL,
                        additional_data TEXT NULL,
                        ip_address VARCHAR(45) NULL,
                        user_agent TEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_user_id (user_id),
                        INDEX idx_action (action),
                        INDEX idx_created_at (created_at)
                    )
                ");
            }
            
            $stmt = $conn->prepare("
                INSERT INTO activity_logs 
                (user_id, action, table_name, record_id, old_data, additional_data, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user_id,
                $action,
                $table_name,
                $record_id,
                $old_data ? json_encode($old_data) : null,
                $additional_data ? json_encode($additional_data) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
        } catch (Exception $e) {
            // Silently fail to avoid breaking the main functionality
            error_log("Activity logging failed: " . $e->getMessage());
        }
    }
}

/**
 * Log error to file
 * @param string $message - Error message
 * @param string $file - File where error occurred
 * @param int $line - Line number where error occurred
 */
if (!function_exists('logError')) {
    function logError($message, $file = '', $line = 0) {
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/error_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] ERROR: $message";
        
        if ($file) {
            $log_entry .= " in $file";
        }
        if ($line) {
            $log_entry .= " on line $line";
        }
        
        $log_entry .= "\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Format date in Thai format
 * @param string $date - Date in Y-m-d format
 * @param bool $short - Use short month names
 * @return string - Formatted Thai date
 */
if (!function_exists('formatThaiDate')) {
    function formatThaiDate($date, $short = false) {
        $thai_months = [
            1 => $short ? 'ม.ค.' : 'มกราคม',
            2 => $short ? 'ก.พ.' : 'กุมภาพันธ์',
            3 => $short ? 'มี.ค.' : 'มีนาคม',
            4 => $short ? 'เม.ย.' : 'เมษายน',
            5 => $short ? 'พ.ค.' : 'พฤษภาคม',
            6 => $short ? 'มิ.ย.' : 'มิถุนายน',
            7 => $short ? 'ก.ค.' : 'กรกฎาคม',
            8 => $short ? 'ส.ค.' : 'สิงหาคม',
            9 => $short ? 'ก.ย.' : 'กันยายน',
            10 => $short ? 'ต.ค.' : 'ตุลาคม',
            11 => $short ? 'พ.ย.' : 'พฤศจิกายน',
            12 => $short ? 'ธ.ค.' : 'ธันวาคม'
        ];
        
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = (int)date('n', $timestamp);
        $year = date('Y', $timestamp) + 543; // Convert to Buddhist year
        
        return $day . ' ' . $thai_months[$month] . ' ' . $year;
    }
}

/**
 * Format Thai date and time
 * @param string $datetime - DateTime string
 * @return string - Formatted Thai datetime
 */
if (!function_exists('formatThaiDateTime')) {
    function formatThaiDateTime($datetime) {
        $date_part = date('Y-m-d', strtotime($datetime));
        $time_part = date('H:i', strtotime($datetime));
        
        return formatThaiDate($date_part, true) . ' เวลา ' . $time_part . ' น.';
    }
}

/**
 * Generate CSRF token
 * @return string - CSRF token
 */
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Verify CSRF token
 * @param string $token - Token to verify
 * @return bool - True if valid
 */
if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Check if user is logged in
 * @return bool - True if logged in
 */
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    }
}

/**
 * Check if user has specific role
 * @param string $role - Role to check
 * @return bool - True if user has role
 */
if (!function_exists('hasRole')) {
    function hasRole($role) {
        return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }
}

/**
 * Get current user ID
 * @return int|null - User ID or null if not logged in
 */
if (!function_exists('getCurrentUserId')) {
    function getCurrentUserId() {
        return isLoggedIn() ? $_SESSION['user_id'] : null;
    }
}

/**
 * Clean old rate limiting data
 */
if (!function_exists('cleanRateLimitData')) {
    function cleanRateLimitData() {
        if (isset($_SESSION['rate_limits'])) {
            $now = time();
            foreach ($_SESSION['rate_limits'] as $key => $data) {
                if ($now - $data['first_attempt'] > 900) { // 15 minutes
                    unset($_SESSION['rate_limits'][$key]);
                }
            }
        }
    }
}

/**
 * Validate email format
 * @param string $email - Email to validate
 * @return bool - True if valid email
 */
if (!function_exists('isValidEmail')) {
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Generate random password
 * @param int $length - Password length
 * @return string - Random password
 */
if (!function_exists('generateRandomPassword')) {
    function generateRandomPassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }
}

/**
 * Check password strength
 * @param string $password - Password to check
 * @return array - Strength info
 */
if (!function_exists('checkPasswordStrength')) {
    function checkPasswordStrength($password) {
        $strength = 0;
        $feedback = [];
        
        if (strlen($password) >= 8) {
            $strength += 1;
        } else {
            $feedback[] = 'ต้องมีความยาวอย่างน้อย 8 ตัวอักษร';
        }
        
        if (preg_match('/[a-z]/', $password)) {
            $strength += 1;
        } else {
            $feedback[] = 'ต้องมีตัวพิมพ์เล็ก';
        }
        
        if (preg_match('/[A-Z]/', $password)) {
            $strength += 1;
        } else {
            $feedback[] = 'ต้องมีตัวพิมพ์ใหญ่';
        }
        
        if (preg_match('/\d/', $password)) {
            $strength += 1;
        } else {
            $feedback[] = 'ต้องมีตัวเลข';
        }
        
        if (preg_match('/[^a-zA-Z\d]/', $password)) {
            $strength += 1;
        } else {
            $feedback[] = 'ต้องมีอักขระพิเศษ';
        }
        
        $strength_levels = ['อ่อนมาก', 'อ่อน', 'ปานกลาง', 'แข็งแรง', 'แข็งแรงมาก'];
        
        return [
            'score' => $strength,
            'level' => $strength_levels[$strength] ?? 'ไม่ทราบ',
            'feedback' => $feedback
        ];
    }
}

/**
 * Create database tables if they don't exist
 * @param PDO $conn - Database connection
 */
if (!function_exists('createRequiredTables')) {
    function createRequiredTables($conn) {
        try {
            // Create user_tokens table for remember me functionality
            $conn->exec("
                CREATE TABLE IF NOT EXISTS user_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    type ENUM('remember', 'reset', 'verify') DEFAULT 'remember',
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_token (token),
                    INDEX idx_expires (expires_at),
                    UNIQUE KEY unique_user_type (user_id, type)
                )
            ");
            
            // Create activity_logs table
            $conn->exec("
                CREATE TABLE IF NOT EXISTS activity_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    action VARCHAR(100) NOT NULL,
                    table_name VARCHAR(50) NOT NULL,
                    record_id INT NULL,
                    old_data TEXT NULL,
                    additional_data TEXT NULL,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_action (action),
                    INDEX idx_created_at (created_at)
                )
            ");
            
        } catch (Exception $e) {
            logError("Failed to create required tables: " . $e->getMessage(), __FILE__, __LINE__);
        }
    }
}
?>