<?php
/**
 * Authentication and Authorization Middleware - ปรับปรุงแล้ว
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
        // Store the current URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirectTo($redirect_url);
    }
    
    // Check session validity
    if (!validateSession()) {
        destroySession();
        redirectTo($redirect_url);
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
                    " to page: " . $_SERVER['REQUEST_URI'] . 
                    " Required roles: " . (is_array($required_roles) ? implode(',', $required_roles) : $required_roles), 
                    __FILE__, __LINE__);
        }
        
        // Show access denied page instead of redirect
        showAccessDenied();
    }
}

/**
 * Require admin role
 */
function requireAdmin($redirect_url = '../admin/login.php') {
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
        'department_id' => $_SESSION['department_id'] ?? null,
        'login_time' => $_SESSION['login_time'] ?? null
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
            'nurse' => ['read', 'update'],
            'staff' => ['read']
        ],
        'visits' => [
            'admin' => ['create', 'read', 'update', 'delete'],
            'doctor' => ['create', 'read', 'update'],
            'nurse' => ['read', 'update'],
            'staff' => ['read']
        ],
        'users' => [
            'admin' => ['create', 'read', 'update', 'delete'],
            'doctor' => ['read'],
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
 * Validate session integrity
 */
function validateSession() {
    // Check if required session variables exist
    $required_vars = ['user_id', 'username', 'user_role', 'login_time'];
    foreach ($required_vars as $var) {
        if (!isset($_SESSION[$var])) {
            return false;
        }
    }
    
    // Check session timeout (2 hours default)
    $timeout = 2 * 60 * 60; // 2 hours
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
        return false;
    }
    
    // Check IP consistency (optional - can cause issues with dynamic IPs)
    if (isset($_SESSION['user_ip']) && isset($_SERVER['REMOTE_ADDR'])) {
        if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
            // Log potential session hijacking
            if (function_exists('logError')) {
                logError("Potential session hijacking detected. Session IP: " . $_SESSION['user_ip'] . 
                        ", Current IP: " . $_SERVER['REMOTE_ADDR'], __FILE__, __LINE__);
            }
            return false;
        }
    }
    
    return true;
}

/**
 * Destroy session securely
 */
function destroySession() {
    // Log logout
    if (isLoggedIn() && function_exists('logActivity')) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = new Database();
            $conn = $db->getConnection();
            logActivity($conn, $_SESSION['user_id'], 'session_destroyed', 'users', $_SESSION['user_id']);
        } catch (Exception $e) {
            // Silent fail
        }
    }
    
    // Clear all session data
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Show access denied page
 */
function showAccessDenied() {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ไม่มีสิทธิ์เข้าถึง - โรงพยาบาลทุ่งหัวช้าง</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>body { font-family: 'Sarabun', sans-serif; }</style>
    </head>
    <body class="bg-gray-100 min-h-screen flex items-center justify-center">
        <div class="text-center">
            <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-4xl">🚫</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">ไม่มีสิทธิ์เข้าถึง</h1>
            <p class="text-gray-600 mb-6">คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>
            <div class="space-x-4">
                <a href="javascript:history.back()" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                    กลับ
                </a>
                <a href="../index.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    หน้าหลัก
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
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
        ], JSON_UNESCAPED_UNICODE);
        
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
            destroySession();
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
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Strict transport security (HTTPS only)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'");
}

/**
 * Check for suspicious activity
 */
function checkSuspiciousActivity() {
    if (!isLoggedIn()) {
        return;
    }
    
    $suspicious = false;
    $reasons = [];
    
    // Check for rapid page requests
    if (!isset($_SESSION['page_requests'])) {
        $_SESSION['page_requests'] = [];
    }
    
    $_SESSION['page_requests'][] = time();
    
    // Keep only requests from last minute
    $_SESSION['page_requests'] = array_filter($_SESSION['page_requests'], function($time) {
        return $time > (time() - 60);
    });
    
    // More than 60 requests per minute is suspicious
    if (count($_SESSION['page_requests']) > 60) {
        $suspicious = true;
        $reasons[] = 'excessive_requests';
    }
    
    // Check for user agent changes
    if (isset($_SESSION['user_agent']) && isset($_SERVER['HTTP_USER_AGENT'])) {
        if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $suspicious = true;
            $reasons[] = 'user_agent_change';
        }
    }
    
    if ($suspicious) {
        logSecurityEvent('suspicious_activity', implode(',', $reasons));
        
        // For high-risk activity, destroy session
        if (in_array('user_agent_change', $reasons)) {
            destroySession();
            redirectTo('../login.php');
        }
    }
}

/**
 * Update last activity time
 */
function updateLastActivity() {
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
    }
}

// Auto-apply security measures
if (!headers_sent()) {
    setSecurityHeaders();
}

// Check session timeout and suspicious activity automatically
if (isLoggedIn()) {
    if (!checkSessionTimeout()) {
        redirectTo(($_SERVER['SCRIPT_NAME'] === '/admin/login.php') ? 'login.php' : '../login.php');
    }
    
    checkSuspiciousActivity();
    updateLastActivity();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 1800) {
        regenerateSession();
        $_SESSION['last_regeneration'] = time();
    }
}
?>