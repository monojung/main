<?php
require_once '../config/database.php';

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if ($_SESSION['user_role'] === 'admin') {
        redirectTo('dashboard.php');
    } else {
        // Non-admin users should go to main login
        redirectTo('../login.php');
    }
}

$error_message = '';
$success_message = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            if ($conn) {
                // Only check for admin users
                $stmt = $conn->prepare("
                    SELECT id, username, password_hash, first_name, last_name, role, 
                           login_attempts, locked_until, is_active, department_id
                    FROM users 
                    WHERE username = ? AND role = 'admin' AND is_active = 1
                ");
                $stmt->execute(array($username));
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
                            $stmt->execute(array($user['id']));
                            
                            // Set session variables
                            $_SESSION['user_logged_in'] = true;
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['user_role'] = $user['role'];
                            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                            $_SESSION['department_id'] = $user['department_id'];
                            $_SESSION['login_time'] = time();
                            $_SESSION['is_admin'] = true;
                            
                            // Log successful login
                            if (function_exists('logActivity')) {
                                logActivity($conn, $user['id'], 'admin_login_success', 'users', $user['id']);
                            }
                            
                            redirectTo('dashboard.php');
                        } else {
                            // Increment login attempts
                            $login_attempts = $user['login_attempts'] + 1;
                            $locked_until = null;
                            
                            // Lock account after 3 failed attempts for admin (more strict)
                            if ($login_attempts >= 3) {
                                $locked_until = date('Y-m-d H:i:s', time() + (30 * 60)); // Lock for 30 minutes
                                $error_message = 'บัญชีถูกล็อคเนื่องจากเข้าสู่ระบบผิดหลายครั้ง กรุณาลองใหม่ในอีก 30 นาที';
                            } else {
                                $remaining_attempts = 3 - $login_attempts;
                                $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง (เหลือ $remaining_attempts ครั้ง)";
                            }
                            
                            $stmt = $conn->prepare("
                                UPDATE users 
                                SET login_attempts = ?, locked_until = ? 
                                WHERE id = ?
                            ");
                            $stmt->execute(array($login_attempts, $locked_until, $user['id']));
                            
                            // Log failed login attempt
                            if (function_exists('logActivity')) {
                                logActivity($conn, $user['id'], 'admin_login_failed', 'users', $user['id']);
                            }
                        }
                    }
                } else {
                    $error_message = 'ไม่พบบัญชีผู้ดูแลระบบนี้';
                    
                    // Log unknown admin username attempt
                    if (function_exists('logActivity')) {
                        logActivity($conn, null, 'admin_login_failed_unknown_user', 'users', null, null, array('username' => $username));
                    }
                }
            }
        } catch (Exception $e) {
            $error_message = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
            if (function_exists('logError')) {
                logError($e->getMessage(), __FILE__, __LINE__);
            }
        }
    }
}

// Helper functions if not available
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

if (!function_exists('redirectTo')) {
    function redirectTo($url) {
        header("Location: $url");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแล - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: linear-gradient(135deg, #1e40af 0%, #7c3aed 50%, #dc2626 100%);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .bg-pattern {
            background-image: 
                linear-gradient(45deg, rgba(30, 64, 175, 0.1) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(30, 64, 175, 0.1) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, rgba(30, 64, 175, 0.1) 75%),
                linear-gradient(-45deg, transparent 75%, rgba(30, 64, 175, 0.1) 75%);
            background-size: 30px 30px;
            background-position: 0 0, 0 15px, 15px -15px, -15px 0px;
            opacity: 0.3;
        }
        
        .admin-border {
            background: linear-gradient(45deg, #dc2626, #ea580c, #f59e0b);
            padding: 3px;
            border-radius: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .admin-border::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        
        .admin-border:hover::before {
            left: 100%;
        }
        
        .admin-content {
            background: white;
            border-radius: 1.375rem;
            position: relative;
            z-index: 1;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .slide-in-bottom {
            animation: slideInBottom 0.8s ease-out;
        }
        
        @keyframes slideInBottom {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite;
        }
        
        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(220, 38, 38, 0.5);
            }
            50% {
                box-shadow: 0 0 40px rgba(220, 38, 38, 0.8);
            }
        }
        
        .security-badge {
            position: relative;
            overflow: hidden;
        }
        
        .security-badge::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s ease-in-out infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-pattern"></div>
    
    <!-- Floating Elements -->
    <div class="absolute top-10 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-full floating" style="animation-delay: 0s;"></div>
    <div class="absolute top-32 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-full floating" style="animation-delay: 1s;"></div>
    <div class="absolute bottom-20 left-32 w-12 h-12 bg-white bg-opacity-10 rounded-full floating" style="animation-delay: 2s;"></div>
    <div class="absolute bottom-32 right-16 w-24 h-24 bg-white bg-opacity-10 rounded-full floating" style="animation-delay: 0.5s;"></div>
    
    <!-- Security Notice -->
    <div class="absolute top-4 left-4 glass-effect security-badge text-white px-6 py-3 rounded-xl text-sm shadow-xl">
        🔒 <span class="font-bold">SECURE ADMIN ZONE</span>
    </div>

    <!-- Version Info -->
    <div class="absolute top-4 right-4 glass-effect text-white px-4 py-2 rounded-lg text-xs font-mono">
        v2.1.0
    </div>

    <div class="relative w-full max-w-md mx-auto p-6 slide-in-bottom">
        <!-- Admin Header -->
        <div class="text-center mb-8">
            <div class="admin-border mx-auto mb-6 w-24 h-24 pulse-glow">
                <div class="admin-content w-full h-full flex items-center justify-center">
                    <span class="text-red-600 font-bold text-3xl">⚡</span>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">ระบบผู้ดูแล</h1>
            <p class="text-blue-200 text-lg">โรงพยาบาลทุ่งหัวช้าง</p>
            <div class="mt-3 text-sm text-blue-300">
                🔐 <span class="font-semibold">ระดับความปลอดภัยสูง</span>
            </div>
        </div>

        <!-- Login Form -->
        <div class="admin-border">
            <div class="admin-content p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">
                        🛡️ เข้าสู่ระบบผู้ดูแล
                    </h2>
                    <p class="text-gray-600 text-sm">กรุณายืนยันตัวตนเพื่อเข้าถึงระบบจัดการ</p>
                </div>
                
                <?php if ($error_message): ?>
                <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 animate-pulse">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">⚠️</span>
                        <div>
                            <p class="font-medium">เกิดข้อผิดพลาด!</p>
                            <p class="text-sm"><?php echo $error_message; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                <div class="bg-green-50 border-l-4 border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">✅</span>
                        <span><?php echo $success_message; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6" id="adminLoginForm">
                    <!-- Username -->
                    <div class="space-y-2">
                        <label for="username" class="block text-sm font-medium text-gray-700">
                            <span class="flex items-center">
                                <span class="text-lg mr-2">👤</span>
                                ชื่อผู้ใช้ผู้ดูแล
                            </span>
                        </label>
                        <div class="relative">
                            <input type="text" id="username" name="username" required 
                                   class="w-full px-4 py-3 pl-12 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300 bg-gray-50 focus:bg-white"
                                   placeholder="กรอกชื่อผู้ใช้ผู้ดูแล"
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                   autocomplete="username">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-lg">👤</span>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            <span class="flex items-center">
                                <span class="text-lg mr-2">🔒</span>
                                รหัสผ่าน
                            </span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required 
                                   class="w-full px-4 py-3 pl-12 pr-12 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300 bg-gray-50 focus:bg-white"
                                   placeholder="กรอกรหัสผ่าน"
                                   autocomplete="current-password">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-lg">🔒</span>
                            </div>
                            <button type="button" onclick="togglePassword()" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition duration-200">
                                <span id="eye-icon" class="text-lg">👁️</span>
                            </button>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-300 rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <span class="text-yellow-600 text-lg flex-shrink-0">⚠️</span>
                            <div class="text-yellow-800 text-sm">
                                <p class="font-semibold mb-2">นโยบายความปลอดภัย:</p>
                                <ul class="space-y-1 text-xs">
                                    <li>• ล็อคบัญชีหลังผิด 3 ครั้ง (30 นาที)</li>
                                    <li>• บันทึกการเข้าถึงทั้งหมด</li>
                                    <li>• สำหรับผู้ดูแลระบบเท่านั้น</li>
                                    <li>• ใช้งานในเครือข่ายปลอดภัยเท่านั้น</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn"
                            class="w-full bg-gradient-to-r from-red-600 via-orange-600 to-yellow-600 text-white py-4 px-6 rounded-xl font-semibold hover:from-red-700 hover:via-orange-700 hover:to-yellow-700 transform hover:scale-105 transition duration-300 shadow-xl relative overflow-hidden">
                        <span class="relative z-10 flex items-center justify-center">
                            <span class="text-lg mr-2">🚀</span>
                            เข้าสู่ระบบผู้ดูแล
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 hover:opacity-10 transform -skew-x-12 -translate-x-full hover:translate-x-full transition-all duration-700"></div>
                    </button>
                </form>

                <!-- Admin Info -->
                <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <h3 class="text-sm font-semibold text-blue-800 mb-3 flex items-center">
                        <span class="text-lg mr-2">ℹ️</span>
                        ข้อมูลระบบ:
                    </h3>
                    <div class="text-xs text-blue-700 space-y-1">
                        <div><strong>เซิร์ฟเวอร์:</strong> <?php echo $_SERVER['SERVER_NAME'] ?? 'localhost'; ?></div>
                        <div><strong>IP Address:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?></div>
                        <div><strong>เข้าถึงเมื่อ:</strong> <?php echo date('d/m/Y H:i:s'); ?></div>
                        <div><strong>User Agent:</strong> <?php echo substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50) . '...'; ?></div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="mt-6 text-center">
                    <div class="flex items-center justify-center space-x-2 text-sm">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-green-700 font-medium">ระบบปลอดภัยพร้อมใช้งาน</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        อัพเดทล่าสุด: <?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="text-center mt-8 space-y-3">
            <a href="../login.php" class="glass-effect text-white hover:bg-white hover:bg-opacity-20 transition duration-300 flex items-center justify-center space-x-2 py-3 px-6 rounded-xl">
                <span class="text-lg">👥</span>
                <span>เข้าสู่ระบบเจ้าหน้าที่</span>
            </a>
            <a href="../index.php" class="glass-effect text-white hover:bg-white hover:bg-opacity-20 transition duration-300 flex items-center justify-center space-x-2 py-3 px-6 rounded-xl">
                <span class="text-lg">🏠</span>
                <span>กลับสู่เว็บไซต์หลัก</span>
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-white opacity-75">
            <p>&copy; <?php echo date('Y'); ?> โรงพยาบาลทุ่งหัวช้าง</p>
            <p class="flex items-center justify-center mt-1">
                <span class="mr-2">ระบบผู้ดูแล v2.1</span>
                <span class="text-lg">🔒</span>
            </p>
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

        // Security: Clear form on page unload
        window.addEventListener('beforeunload', function() {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.value = '';
            }
        });

        // Enhanced form validation and submission
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                showAlert('กรุณากรอกชื่อผู้ใช้และรหัสผ่าน', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร', 'error');
                return false;
            }
            
            // Add loading state
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="flex items-center justify-center"><span class="animate-spin mr-2">⏳</span>กำลังตรวจสอบ...</span>';
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            
            // Re-enable after 10 seconds as fallback
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            }, 10000);
        });

        // Enhanced alert function
        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            const bgColor = type === 'error' ? 'bg-red-500' : type === 'success' ? 'bg-green-500' : 'bg-blue-500';
            const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
            
            alertDiv.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-xl shadow-xl z-50 animate-pulse`;
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <span class="text-xl mr-3">${icon}</span>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateX(100%)';
                setTimeout(() => alertDiv.remove(), 300);
            }, 3000);
        }

        // Prevent right-click context menu (optional security)
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showAlert('การคลิกขวาถูกปิดใช้งานเพื่อความปลอดภัย', 'info');
        });

        // Prevent F12 developer tools (optional security)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
                showAlert('Developer tools ถูกปิดใช้งานในหน้าผู้ดูแล', 'info');
            }
        });

        // Security warning for non-HTTPS
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            console.warn('🔒 การเชื่อมต่อไม่ปลอดภัย! ควรใช้ HTTPS สำหรับระบบผู้ดูแล');
        }

        // Enhanced visual feedback for form fields
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.classList.add('ring-4', 'ring-red-200');
                this.parentElement.classList.add('transform', 'scale-105');
            });
            
            input.addEventListener('blur', function() {
                this.classList.remove('ring-4', 'ring-red-200');
                this.parentElement.classList.remove('transform', 'scale-105');
            });
            
            // Real-time validation feedback
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                } else {
                    this.classList.remove('border-green-500');
                    this.classList.add('border-gray-300');
                }
            });
        });

        // Session timeout warning
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(function() {
                    showAlert('เซสชันกำลังจะหมดอายุ กรุณาเข้าสู่ระบบใหม่', 'info');
                }, 1800000); // 30 minutes
            }
        };

        inactivityTime();

        // Caps Lock detection
        document.addEventListener('keydown', function(e) {
            if (e.getModifierState('CapsLock')) {
                showAlert('Caps Lock เปิดอยู่', 'info');
            }
        });

        // Enhanced keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Enter to submit form
            if (e.key === 'Enter' && (e.target.tagName === 'INPUT')) {
                const form = document.getElementById('adminLoginForm');
                if (form) {
                    form.submit();
                }
            }
        });

        // Connection status monitoring
        window.addEventListener('online', function() {
            showAlert('เชื่อมต่ออินเทอร์เน็ตแล้ว', 'success');
        });

        window.addEventListener('offline', function() {
            showAlert('ขาดการเชื่อมต่ออินเทอร์เน็ต', 'error');
        });

        // Performance monitoring
        window.addEventListener('load', function() {
            console.log('🎉 Enhanced Admin Login loaded successfully!');
            console.log('🔒 Security features enabled');
        });

        // Add smooth animations to elements
        document.addEventListener('DOMContentLoaded', function() {
            // Stagger animations for form elements
            const formElements = document.querySelectorAll('#adminLoginForm > div');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    element.style.transition = 'all 0.5s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Mouse tracking effect (subtle)
        document.addEventListener('mousemove', function(e) {
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            const adminBorder = document.querySelector('.admin-border');
            if (adminBorder) {
                const rotateX = (mouseY - 0.5) * 10;
                const rotateY = (mouseX - 0.5) * 10;
                adminBorder.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            }
        });

        // Reset transform when mouse leaves
        document.addEventListener('mouseleave', function() {
            const adminBorder = document.querySelector('.admin-border');
            if (adminBorder) {
                adminBorder.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
            }
        });
    </script>
</body>
</html>