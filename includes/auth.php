<?php
/**
 * Authentication and Authorization Middleware
 * โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($role)) {
        return in_array($_SESSION['user_role'], $role);
    }
    
    return $_SESSION['user_role'] === $role;
}

/**
 * Require login - redirect to login page if not authenticated
 */
function requireLogin($redirect_url = '../login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Require specific role - redirect if user doesn't have required role
 */
function requireRole($required_roles, $redirect_url = '../login.php') {
    requireLogin($redirect_url);
    
    if (!hasRole($required_roles)) {
        // Log unauthorized access attempt
        if (function_exists('logError')) {
            logError("Unauthorized access attempt by user ID: " . ($_SESSION['user_id'] ?? 'unknown') . 
                    " to page: " . $_SERVER['REQUEST_URI'], __FILE__, __LINE__);
        }
        
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Require admin role
 */
function requireAdmin($redirect_url = '../login.php') {
    requireRole('admin', $redirect_url);
}

/**
 * Check if user belongs to specific department
 */
function belongsToDepartment($department_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return $_SESSION['department_id'] == $department_id;
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'department_id' => $_SESSION['department_id'] ?? null
    ];
}

/**
 * Check if user can access specific resource
 */
function canAccess($resource, $action = 'read') {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'];
    
    // Admin can access everything
    if ($user_role === 'admin') {
        return true;
    }
    
    // Define permissions matrix
    $permissions = [
        'appointments' => [
            'admin' => ['create', 'read', 'update', 'delete'],
            'doctor' => ['create', 'read', 'update'],
            'nurse' => ['create', 'read', 'update'],
            'staff' => ['create', 'read', 'update']
        ],
        'patients' => [
            'admin' => ['create', 'read', 'update', 'delete'],
            'doctor' => ['create', 'read', 'update'],
            'nurse' => ['read'],
            'staff' => ['read']
        ],
        'visits' => [
            'admin' => ['create', 'read', 'update', 'delete'],
            'doctor' => ['create', 'read', 'update'],
            'nurse' => ['read'],
            'staff' => ['read']
        ],
        'users' => [
            'admin' => ['create', 'read', 'update', 'delete'],
            'doctor' => [],
            'nurse' => [],
            'staff' => []
        ],
        'reports' => [
            'admin' => ['read'],
            'doctor' => ['read'],
            'nurse' => ['read'],
            'staff' => ['read']
        ],
        'settings' => [
            'admin' => ['read', 'update'],
            'doctor' => [],
            'nurse' => [],
            'staff' => []
        ]
    ];
    
    if (!isset($permissions[$resource][$user_role])) {
        return false;
    }
    
    return in_array($action, $permissions[$resource][$user_role]);
}

/**
 * Log security event
 */
function logSecurityEvent($event, $details = '') {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, ip_address, user_agent, created_at)
            VALUES (?, ?, 'security', NULL, ?, ?, ?, NOW())
        ");
        
        $user_id = $_SESSION['user_id'] ?? null;
        $details_json = json_encode([
            'event' => $event,
            'details' => $details,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? ''
        ]);
        
        $stmt->execute([
            $user_id,
            'security_event',
            $details_json,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        // Silent fail for logging
        error_log("Failed to log security event: " . $e->getMessage());
    }
}

/**
 * Check session timeout
 */
function checkSessionTimeout($timeout_minutes = 120) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (isset($_SESSION['last_activity'])) {
        $timeout_seconds = $timeout_minutes * 60;
        if (time() - $_SESSION['last_activity'] > $timeout_seconds) {
            // Session timed out
            logSecurityEvent('session_timeout');
            session_unset();
            session_destroy();
            return false;
        }
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Regenerate session ID for security
 */
function regenerateSession() {
    if (isLoggedIn()) {
        session_regenerate_id(true);
    }
}

/**
 * Check if IP is allowed (optional security feature)
 */
function checkAllowedIP() {
    // Define allowed IP ranges for admin access
    $allowed_ips = [
        '127.0.0.1',      // localhost
        '::1',            // localhost IPv6
        '192.168.1.0/24', // local network
        '10.0.0.0/8'      // private network
    ];
    
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // For admin users, check IP restrictions
    if (hasRole('admin')) {
        foreach ($allowed_ips as $allowed) {
            if (strpos($allowed, '/') !== false) {
                // CIDR notation
                if (ipInRange($user_ip, $allowed)) {
                    return true;
                }
            } else {
                // Exact IP match
                if ($user_ip === $allowed) {
                    return true;
                }
            }
        }
        
        // Log suspicious admin access
        logSecurityEvent('admin_access_from_restricted_ip', "IP: $user_ip");
        return false;
    }
    
    return true; // Allow all IPs for non-admin users
}

/**
 * Check if IP is in range (CIDR notation)
 */
function ipInRange($ip, $range) {
    if (strpos($range, '/') === false) {
        return $ip === $range;
    }
    
    list($subnet, $bits) = explode('/', $range);
    $ip_long = ip2long($ip);
    $subnet_long = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet_long &= $mask;
    
    return ($ip_long & $mask) === $subnet_long;
}

/**
 * Rate limiting for login attempts
 */
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

/**
 * Security headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Strict transport security (HTTPS only)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src 'self' https://fonts.gstatic.com");
}

// Auto-apply security measures
if (!headers_sent()) {
    setSecurityHeaders();
}

// Check session timeout automatically
if (isLoggedIn()) {
    if (!checkSessionTimeout()) {
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] === '/login.php' ? 'login.php' : '../login.php'));
        exit();
    }
}
?>