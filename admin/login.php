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
                            logActivity($conn, $user['id'], 'admin_login_success', 'users', $user['id']);
                            
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
                            logActivity($conn, $user['id'], 'admin_login_failed', 'users', $user['id']);
                        }
                    }
                } else {
                    $error_message = 'ไม่พบบัญชีผู้ดูแลระบบนี้';
                    
                    // Log unknown admin username attempt
                    logActivity($conn, null, 'admin_login_failed_unknown_user', 'users', null, null, array('username' => $username));
                }
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
    <title>เข้าสู่ระบบผู้ดูแล - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .bg-pattern {
            background-image: 
                linear-gradient(45deg, #1e40af 25%, transparent 25%),
                linear-gradient(-45deg, #1e40af 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #1e40af 75%),
                linear-gradient(-45deg, transparent 75%, #1e40af 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            opacity: 0.1;
        }
        .admin-border {
            background: linear-gradient(45deg, #dc2626, #ea580c);
            padding: 2px;
            border-radius: 1rem;
        }
        .admin-content {
            background: white;
            border-radius: 0.875rem;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 to-indigo-900 min-h-screen flex items-center justify-center relative">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-pattern"></div>
    
    <!-- Security Notice -->
    <div class="absolute top-4 left-4 bg-red-600 text-white px-4 py-2 rounded-lg text-sm shadow-lg">
        🔒 พื้นที่ผู้ดูแลระบบ
    </div>

    <!-- Version Info -->
    <div class="absolute top-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded text-xs">
        v2.0.1
    </div>

    <div class="relative w-full max-w-md mx-auto p-6">
        <!-- Admin Header -->
        <div class="text-center mb-8">
            <div class="admin-border mx-auto mb-4 w-20 h-20">
                <div class="admin-content w-full h-full flex items-center justify-center">
                    <span class="text-red-600 font-bold text-2xl">⚡</span>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-white">ระบบผู้ดูแล</h1>
            <p class="text-blue-200">โรงพยาบาลทุ่งหัวช้าง</p>
            <div class="mt-2 text-xs text-blue-300">
                🔐 ระดับความปลอดภัยสูง
            </div>
        </div>

        <!-- Login Form -->
        <div class="admin-border">
            <div class="admin-content p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">
                    🛡️ เข้าสู่ระบบผู้ดูแล
                </h2>
                
                <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <span class="text-xl mr-2">⚠️</span>
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

                <form method="POST" class="space-y-6" id="adminLoginForm">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            👤 ชื่อผู้ใช้ผู้ดูแล
                        </label>
                        <input type="text" id="username" name="username" required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                               placeholder="กรอกชื่อผู้ใช้ผู้ดูแล"
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
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-300"
                                   placeholder="กรอกรหัสผ่าน"
                                   autocomplete="current-password">
                            <button type="button" onclick="togglePassword()" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <span id="eye-icon">👁️</span>
                            </button>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                        <div class="flex items-start space-x-2">
                            <span class="text-yellow-600 text-sm">⚠️</span>
                            <div class="text-yellow-800 text-xs">
                                <p class="font-medium mb-1">ข้อมูลความปลอดภัย:</p>
                                <ul class="space-y-1">
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
                            class="w-full bg-gradient-to-r from-red-600 to-orange-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-red-700 hover:to-orange-700 transform hover:scale-105 transition duration-300 shadow-lg">
                        🚀 เข้าสู่ระบบผู้ดูแล
                    </button>
                </form>

                <!-- Demo Account 
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">🔧 บัญชีทดสอบ:</h3>
                    <div class="text-xs text-gray-600">
                        <div><strong>ผู้ดูแลระบบ:</strong> admin / admin123</div>
                        <div class="text-red-600 mt-1">⚠️ เฉพาะการทดสอบเท่านั้น</div>
                    </div>
                </div> -->

                <!-- Admin Info -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-sm font-semibold text-blue-800 mb-3">ℹ️ ข้อมูลระบบ:</h3>
                    <div class="text-xs text-blue-700 space-y-1">
                        <div><strong>เซิร์ฟเวอร์:</strong> <?php echo $_SERVER['SERVER_NAME'] ?? 'localhost'; ?></div>
                        <div><strong>IP Address:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?></div>
                        <div><strong>เข้าถึงเมื่อ:</strong> <?php echo formatThaiDateTime(date('Y-m-d H:i:s')); ?></div>
                        <div><strong>User Agent:</strong> <?php echo substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50) . '...'; ?></div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="mt-6 text-center">
                    <div class="flex items-center justify-center space-x-2 text-sm">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-green-700">ระบบปลอดภัยพร้อมใช้งาน</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        อัพเดทล่าสุด: <?php echo formatThaiDate(date('Y-m-d')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="text-center mt-6 space-y-2">
            <a href="../login.php" class="text-blue-200 hover:text-white transition duration-300 flex items-center justify-center space-x-2">
                <span>👥</span>
                <span>เข้าสู่ระบบเจ้าหน้าที่</span>
            </a>
            <a href="../index.php" class="text-blue-200 hover:text-white transition duration-300 flex items-center justify-center space-x-2">
                <span>🏠</span>
                <span>กลับสู่เว็บไซต์หลัก</span>
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-white/50">
            <p>&copy; <?php echo date('Y'); ?> โรงพยาบาลทุ่งหัวช้าง</p>
            <p>ระบบผู้ดูแล v2.0 🔒</p>
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
            document.getElementById('password').value = '';
        });

        // Form validation and submission
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
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
            
            // Add loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '⏳ กำลังตรวจสอบ...';
            submitBtn.disabled = true;
        });

        // Prevent right-click context menu (optional security)
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        // Prevent F12 developer tools (optional security)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
                alert('Developer tools ถูกปิดใช้งานในหน้าผู้ดูแล');
            }
        });

        // Security warning for non-HTTPS
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            console.warn('🔒 การเชื่อมต่อไม่ปลอดภัย! ควรใช้ HTTPS สำหรับระบบผู้ดูแล');
        }

        // Add visual feedback for form fields
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-red-200');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-red-200');
            });
        });

        // Session timeout warning (if applicable)
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(function() {
                    // Show warning after 25 minutes of inactivity
                    if (confirm('เซสชันกำลังจะหมดอายุ ต้องการขยายเวลาหรือไม่?')) {
                        window.location.reload();
                    }
                }, 1500000); // 25 minutes
            }
        };

        inactivityTime();
    </script>
</body>
</html>