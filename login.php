<?php
require_once 'config/database.php';

// If already logged in, redirect to appropriate page
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if ($_SESSION['user_role'] === 'admin') {
        redirectTo('admin/dashboard.php');
    } else {
        redirectTo('staff/dashboard.php');
    }
}

$error_message = '';
$success_message = '';

// Check for logout message
if (isset($_SESSION['logout_message'])) {
    $success_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

if ($_POST) {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error_message = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check if account is locked
            $stmt = $conn->prepare("
                SELECT id, username, password_hash, first_name, last_name, role, 
                       login_attempts, locked_until, is_active, department_id
                FROM users 
                WHERE username = ? AND is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Check if account is locked
                if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                    $remaining_minutes = ceil((strtotime($user['locked_until']) - time()) / 60);
                    $error_message = "บัญชีถูกล็อค กรุณาลองใหม่ในอีก $remaining_minutes นาที";
                } else {
                    // Verify password
                    if (verifyPassword($password, $user['password_hash'])) {
                        // Reset login attempts on successful login
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET login_attempts = 0, locked_until = NULL, last_login = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$user['id']]);
                        
                        // Set session variables
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['department_id'] = $user['department_id'];
                        $_SESSION['login_time'] = time();
                        
                        // Set remember me cookie if checked
                        if ($remember_me) {
                            $token = bin2hex(random_bytes(32));
                            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                        }
                        
                        // Log successful login
                        logActivity($conn, $user['id'], 'login_success', 'users', $user['id']);
                        
                        // Redirect based on role
                        if ($user['role'] === 'admin') {
                            redirectTo('admin/dashboard.php');
                        } else {
                            redirectTo('staff/dashboard.php');
                        }
                    } else {
                        // Increment login attempts
                        $login_attempts = $user['login_attempts'] + 1;
                        $locked_until = null;
                        
                        // Lock account after 5 failed attempts
                        if ($login_attempts >= 5) {
                            $locked_until = date('Y-m-d H:i:s', time() + (15 * 60)); // Lock for 15 minutes
                            $error_message = 'บัญชีถูกล็อคเนื่องจากเข้าสู่ระบบผิดหลายครั้ง กรุณาลองใหม่ในอีก 15 นาที';
                        } else {
                            $remaining_attempts = 5 - $login_attempts;
                            $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง (เหลือ $remaining_attempts ครั้ง)";
                        }
                        
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET login_attempts = ?, locked_until = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$login_attempts, $locked_until, $user['id']]);
                        
                        // Log failed login attempt
                        logActivity($conn, $user['id'], 'login_failed', 'users', $user['id']);
                    }
                }
            } else {
                $error_message = 'ไม่พบชื่อผู้ใช้นี้ในระบบ';
                
                // Log unknown username attempt
                logActivity($conn, null, 'login_failed_unknown_user', 'users', null, null, ['username' => $username]);
            }
        } catch (Exception $e) {
            $error_message = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
            logError($e->getMessage(), __FILE__, __LINE__);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .bg-pattern {
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center">
    <!-- Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-200 rounded-full opacity-20"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-green-200 rounded-full opacity-20"></div>
    </div>

    <div class="relative w-full max-w-md mx-auto p-6">
        <!-- Hospital Logo and Name -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <span class="text-white font-bold text-2xl">THC</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">โรงพยาบาลทุ่งหัวช้าง</h1>
            <p class="text-gray-600">ระบบจัดการโรงพยาบาล</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">เข้าสู่ระบบ</h2>
            
            <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <span class="text-xl mr-2">❌</span>
                    <span><?php echo $error_message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <span class="text-xl mr-2">✅</span>
                    <span><?php echo $success_message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        👤 ชื่อผู้ใช้
                    </label>
                    <input type="text" id="username" name="username" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                           placeholder="กรอกชื่อผู้ใช้"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           autocomplete="username">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        🔒 รหัสผ่าน
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                               placeholder="กรอกรหัสผ่าน"
                               autocomplete="current-password">
                        <button type="button" onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <span id="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember_me" name="remember_me" 
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="remember_me" class="ml-2 text-sm text-gray-700">
                            จดจำการเข้าสู่ระบบ
                        </label>
                    </div>
                    <a href="forgot-password.php" class="text-sm text-blue-600 hover:text-blue-800 transition duration-300">
                        ลืมรหัสผ่าน?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition duration-300 shadow-lg">
                    🚀 เข้าสู่ระบบ
                </button>
            </form>

            <!-- Demo Accounts -->
            <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🔧 บัญชีทดสอบ:</h3>
                <div class="text-xs text-gray-600 space-y-1">
                    <div><strong>ผู้ดูแลระบบ:</strong> admin / admin123</div>
                    <div><strong>เจ้าหน้าที่:</strong> staff / staff123</div>
                    <div><strong>แพทย์:</strong> doctor / doctor123</div>
                </div>
            </div>

            <!-- System Status -->
            <div class="mt-6 text-center">
                <div class="flex items-center justify-center space-x-2 text-sm text-gray-500">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span>ระบบพร้อมใช้งาน</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">อัพเดทล่าสุด: <?php echo formatThaiDate(date('Y-m-d')); ?></p>
            </div>
        </div>

        <!-- Back to Website -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 transition duration-300 flex items-center justify-center space-x-2">
                <span>←</span>
                <span>กลับสู่เว็บไซต์หลัก</span>
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน</p>
            <p>ระบบจัดการโรงพยาบาล v2.0</p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }

        // Auto-focus on username field
        document.getElementById('username').focus();

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
                return false;
            }
        });

        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '⏳ กำลังเข้าสู่ระบบ...';
            submitBtn.disabled = true;
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + L = focus on login
            if (e.altKey && e.key === 'l') {
                e.preventDefault();
                document.getElementById('username').focus();
            }
        });

        // Security: Clear form on page unload
        window.addEventListener('beforeunload', function() {
            document.getElementById('password').value = '';
        });

        // Auto-hide success message after 5 seconds
        <?php if ($success_message): ?>
        setTimeout(function() {
            const successDiv = document.querySelector('.bg-green-100');
            if (successDiv) {
                successDiv.style.transition = 'opacity 0.5s';
                successDiv.style.opacity = '0';
                setTimeout(() => successDiv.remove(), 500);
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html> <?php
session_start();
require_once 'config/database.php';

// If already logged in, redirect to appropriate page
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: staff/dashboard.php');
    }
    exit();
}

$error_message = '';
$success_message = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error_message = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check if account is locked
            $stmt = $conn->prepare("
                SELECT id, username, password_hash, first_name, last_name, role, 
                       login_attempts, locked_until, is_active, department_id
                FROM users 
                WHERE username = ? AND is_active = TRUE
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Check if account is locked
                if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                    $error_message = 'บัญชีถูกล็อค กรุณาลองใหม่ภายหลัง';
                } else {
                    // Verify password
                    if (password_verify($password, $user['password_hash'])) {
                        // Reset login attempts on successful login
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET login_attempts = 0, locked_until = NULL, last_login = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$user['id']]);
                        
                        // Set session variables
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['department_id'] = $user['department_id'];
                        
                        // Set remember me cookie if checked
                        if ($remember_me) {
                            $token = bin2hex(random_bytes(32));
                            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                            // Store token in database (you might want to create a remember_tokens table)
                        }
                        
                        // Log successful login
                        logActivity($conn, $user['id'], 'login_success', 'users', $user['id']);
                        
                        // Redirect based on role
                        if ($user['role'] === 'admin') {
                            header('Location: admin/dashboard.php');
                        } else {
                            header('Location: staff/dashboard.php');
                        }
                        exit();
                    } else {
                        // Increment login attempts
                        $login_attempts = $user['login_attempts'] + 1;
                        $locked_until = null;
                        
                        // Lock account after 5 failed attempts
                        if ($login_attempts >= 5) {
                            $locked_until = date('Y-m-d H:i:s', time() + (15 * 60)); // Lock for 15 minutes
                            $error_message = 'บัญชีถูกล็อคเนื่องจากเข้าสู่ระบบผิดหลายครั้ง กรุณาลองใหม่ในอีก 15 นาที';
                        } else {
                            $remaining_attempts = 5 - $login_attempts;
                            $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง (เหลือ $remaining_attempts ครั้ง)";
                        }
                        
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET login_attempts = ?, locked_until = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$login_attempts, $locked_until, $user['id']]);
                        
                        // Log failed login attempt
                        logActivity($conn, $user['id'], 'login_failed', 'users', $user['id']);
                    }
                }
            } else {
                $error_message = 'ไม่พบชื่อผู้ใช้นี้ในระบบ';
            }
        } catch (Exception $e) {
            $error_message = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
            logError($e->getMessage(), __FILE__, __LINE__);
        }
    }
}

function logActivity($conn, $user_id, $action, $table_name, $record_id) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user_id, 
            $action, 
            $table_name, 
            $record_id, 
            $_SERVER['REMOTE_ADDR'] ?? '', 
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        // Silent fail for logging
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .bg-pattern {
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center">
    <!-- Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-200 rounded-full opacity-20"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-green-200 rounded-full opacity-20"></div>
    </div>

    <div class="relative w-full max-w-md mx-auto p-6">
        <!-- Hospital Logo and Name -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <span class="text-white font-bold text-2xl">THC</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">โรงพยาบาลทุ่งหัวช้าง</h1>
            <p class="text-gray-600">ระบบจัดการโรงพยาบาล</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">เข้าสู่ระบบ</h2>
            
            <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <span class="text-xl mr-2">❌</span>
                    <span><?php echo $error_message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <span class="text-xl mr-2">✅</span>
                    <span><?php echo $success_message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        👤 ชื่อผู้ใช้
                    </label>
                    <input type="text" id="username" name="username" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                           placeholder="กรอกชื่อผู้ใช้"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           autocomplete="username">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        🔒 รหัสผ่าน
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                               placeholder="กรอกรหัสผ่าน"
                               autocomplete="current-password">
                        <button type="button" onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <span id="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember_me" name="remember_me" 
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="remember_me" class="ml-2 text-sm text-gray-700">
                            จดจำการเข้าสู่ระบบ
                        </label>
                    </div>
                    <a href="forgot-password.php" class="text-sm text-blue-600 hover:text-blue-800 transition duration-300">
                        ลืมรหัสผ่าน?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition duration-300 shadow-lg">
                    🚀 เข้าสู่ระบบ
                </button>
            </form>

            <!-- Demo Accounts -->
            <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">🔧 บัญชีทดสอบ:</h3>
                <div class="text-xs text-gray-600 space-y-1">
                    <div><strong>ผู้ดูแลระบบ:</strong> admin / admin123</div>
                    <div><strong>เจ้าหน้าที่:</strong> staff / staff123</div>
                    <div><strong>แพทย์:</strong> doctor / doctor123</div>
                </div>
            </div>

            <!-- System Status -->
            <div class="mt-6 text-center">
                <div class="flex items-center justify-center space-x-2 text-sm text-gray-500">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span>ระบบพร้อมใช้งาน</span>
                </div>
            </div>
        </div>

        <!-- Back to Website -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 transition duration-300 flex items-center justify-center space-x-2">
                <span>←</span>
                <span>กลับสู่เว็บไซต์หลัก</span>
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน</p>
            <p>ระบบจัดการโรงพยาบาล v1.0</p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }

        // Auto-focus on username field
        document.getElementById('username').focus();

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
                return false;
            }
        });

        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '⏳ กำลังเข้าสู่ระบบ...';
            submitBtn.disabled = true;
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + L = focus on login
            if (e.altKey && e.key === 'l') {
                e.preventDefault();
                document.getElementById('username').focus();
            }
        });

        // Security: Clear form on page unload
        window.addEventListener('beforeunload', function() {
            document.getElementById('password').value = '';
        });
    </script>
</body>
</html>