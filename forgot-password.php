<?php
session_start();
require_once 'config/database.php';

$error_message = '';
$success_message = '';
$step = 1; // 1 = Enter email, 2 = Enter OTP, 3 = Reset password

if ($_POST) {
    if (isset($_POST['step']) && $_POST['step'] == '1') {
        // Step 1: Email submission
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error_message = 'กรุณากรอกอีเมล';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'รูปแบบอีเมลไม่ถูกต้อง';
        } else {
            try {
                $db = new Database();
                $conn = $db->getConnection();
                
                $stmt = $conn->prepare("
                    SELECT id, username, first_name, last_name 
                    FROM users 
                    WHERE email = ? AND is_active = TRUE
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Generate OTP
                    $otp = sprintf('%06d', mt_rand(100000, 999999));
                    $expire_time = date('Y-m-d H:i:s', time() + (15 * 60)); // 15 minutes
                    
                    // Store OTP in session (in production, store in database)
                    $_SESSION['reset_otp'] = $otp;
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_expire'] = $expire_time;
                    
                    // In production, send email here
                    // For demo, show OTP in success message
                    $success_message = "รหัส OTP ของคุณคือ: <strong>$otp</strong> (ใช้ได้ 15 นาที)";
                    $step = 2;
                    
                    // Log password reset request
                    $stmt = $conn->prepare("
                        INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at)
                        VALUES (?, 'password_reset_request', 'users', ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user['id'],
                        $user['id'],
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ]);
                } else {
                    $error_message = 'ไม่พบอีเมลนี้ในระบบ';
                }
            } catch (Exception $e) {
                $error_message = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
            }
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == '2') {
        // Step 2: OTP verification
        $otp = sanitizeInput($_POST['otp'] ?? '');
        
        if (empty($otp)) {
            $error_message = 'กรุณากรอกรหัส OTP';
            $step = 2;
        } elseif (!isset($_SESSION['reset_otp']) || 
                  !isset($_SESSION['reset_expire']) || 
                  time() > strtotime($_SESSION['reset_expire'])) {
            $error_message = 'รหัส OTP หมดอายุ กรุณาขอรหัสใหม่';
            $step = 1;
            unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['reset_expire']);
        } elseif ($otp !== $_SESSION['reset_otp']) {
            $error_message = 'รหัส OTP ไม่ถูกต้อง';
            $step = 2;
        } else {
            $success_message = 'ยืนยันรหัส OTP สำเร็จ กรุณาตั้งรหัสผ่านใหม่';
            $step = 3;
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == '3') {
        // Step 3: Reset password
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($new_password) || empty($confirm_password)) {
            $error_message = 'กรุณากรอกรหัสผ่านใหม่ให้ครบถ้วน';
            $step = 3;
        } elseif (strlen($new_password) < 6) {
            $error_message = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
            $step = 3;
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'รหัสผ่านไม่ตรงกัน';
            $step = 3;
        } elseif (!isset($_SESSION['reset_user_id'])) {
            $error_message = 'เซสชันหมดอายุ กรุณาเริ่มใหม่';
            $step = 1;
        } else {
            try {
                $db = new Database();
                $conn = $db->getConnection();
                
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET password_hash = ?, login_attempts = 0, locked_until = NULL, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$password_hash, $_SESSION['reset_user_id']]);
                
                // Log password reset success
                $stmt = $conn->prepare("
                    INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at)
                    VALUES (?, 'password_reset_success', 'users', ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $_SESSION['reset_user_id'],
                    $_SESSION['reset_user_id'],
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
                
                // Clear session
                unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['reset_expire']);
                
                $success_message = 'เปลี่ยนรหัสผ่านสำเร็จ กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่';
                $step = 4; // Success step
            } catch (Exception $e) {
                $error_message = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                $step = 3;
            }
        }
    }
}

// Check existing session for step continuation
if (isset($_SESSION['reset_otp']) && isset($_SESSION['reset_expire'])) {
    if (time() <= strtotime($_SESSION['reset_expire'])) {
        $step = 2;
    } else {
        unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['reset_expire']);
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-green-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto p-6">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-white text-2xl">🔑</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">ลืมรหัสผ่าน</h1>
            <p class="text-gray-600">ระบบกู้คืนรหัสผ่าน</p>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full <?php echo $step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'; ?> flex items-center justify-center text-sm font-semibold">1</div>
                    <span class="ml-2 text-sm <?php echo $step >= 1 ? 'text-blue-600' : 'text-gray-500'; ?>">อีเมล</span>
                </div>
                <div class="w-8 h-0.5 <?php echo $step >= 2 ? 'bg-blue-600' : 'bg-gray-200'; ?>"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full <?php echo $step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'; ?> flex items-center justify-center text-sm font-semibold">2</div>
                    <span class="ml-2 text-sm <?php echo $step >= 2 ? 'text-blue-600' : 'text-gray-500'; ?>">OTP</span>
                </div>
                <div class="w-8 h-0.5 <?php echo $step >= 3 ? 'bg-blue-600' : 'bg-gray-200'; ?>"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full <?php echo $step >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'; ?> flex items-center justify-center text-sm font-semibold">3</div>
                    <span class="ml-2 text-sm <?php echo $step >= 3 ? 'text-blue-600' : 'text-gray-500'; ?>">รหัสผ่าน</span>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
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

            <?php if ($step == 1): ?>
            <!-- Step 1: Enter Email -->
            <h2 class="text-xl font-semibold text-gray-800 mb-4">กรอกอีเมลของคุณ</h2>
            <p class="text-gray-600 mb-6">เราจะส่งรหัส OTP ไปยังอีเมลของคุณ</p>
            
            <form method="POST">
                <input type="hidden" name="step" value="1">
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        📧 อีเมล
                    </label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="กรอกอีเมลของคุณ"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                    📤 ส่งรหัส OTP
                </button>
            </form>

            <?php elseif ($step == 2): ?>
            <!-- Step 2: Enter OTP -->
            <h2 class="text-xl font-semibold text-gray-800 mb-4">กรอกรหัส OTP</h2>
            <p class="text-gray-600 mb-6">
                รหัส OTP ถูกส่งไปยัง: <strong><?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?></strong>
            </p>
            
            <!-- Countdown Timer -->
            <div class="text-center mb-6">
                <div class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg">
                    เหลือเวลา: <span id="countdown" class="font-bold"></span>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="step" value="2">
                <div class="mb-6">
                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                        🔢 รหัส OTP (6 หลัก)
                    </label>
                    <input type="text" id="otp" name="otp" required maxlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-2xl tracking-widest"
                           placeholder="000000"
                           pattern="[0-9]{6}">
                </div>
                
                <button type="submit" 
                        class="w-full bg-green-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-green-700 transition duration-300">
                    ✅ ยืนยันรหัส OTP
                </button>
            </form>
            
            <div class="text-center mt-4">
                <form method="POST" class="inline">
                    <input type="hidden" name="step" value="1">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?>">
                    <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm">
                        ส่งรหัส OTP ใหม่
                    </button>
                </form>
            </div>

            <?php elseif ($step == 3): ?>
            <!-- Step 3: Reset Password -->
            <h2 class="text-xl font-semibold text-gray-800 mb-4">ตั้งรหัสผ่านใหม่</h2>
            <p class="text-gray-600 mb-6">กรุณาตั้งรหัสผ่านใหม่ที่ปลอดภัย</p>
            
            <form method="POST">
                <input type="hidden" name="step" value="3">
                <div class="mb-4">
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                        🔒 รหัสผ่านใหม่
                    </label>
                    <input type="password" id="new_password" name="new_password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)">
                </div>
                
                <div class="mb-6">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                        🔒 ยืนยันรหัสผ่าน
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="ยืนยันรหัสผ่านใหม่">
                </div>
                
                <!-- Password Strength Indicator -->
                <div class="mb-6">
                    <div class="text-sm text-gray-600 mb-2">ความแข็งแกร่งของรหัสผ่าน:</div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="password-strength" class="bg-red-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div id="password-feedback" class="text-xs text-gray-500 mt-1"></div>
                </div>
                
                <button type="submit" 
                        class="w-full bg-purple-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-purple-700 transition duration-300">
                    💾 บันทึกรหัสผ่านใหม่
                </button>
            </form>

            <?php elseif ($step == 4): ?>
            <!-- Step 4: Success -->
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-green-600 text-3xl">✅</span>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">เปลี่ยนรหัสผ่านสำเร็จ!</h2>
                <p class="text-gray-600 mb-6">คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้แล้ว</p>
                
                <a href="login.php" 
                   class="inline-block bg-blue-600 text-white py-3 px-8 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                    🚀 เข้าสู่ระบบ
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Back to Login -->
        <?php if ($step != 4): ?>
        <div class="text-center mt-6">
            <a href="login.php" class="text-blue-600 hover:text-blue-800 transition duration-300 flex items-center justify-center space-x-2">
                <span>←</span>
                <span>กลับสู่หน้าเข้าสู่ระบบ</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Countdown timer for OTP expiry
        <?php if ($step == 2 && isset($_SESSION['reset_expire'])): ?>
        const expireTime = <?php echo strtotime($_SESSION['reset_expire']) * 1000; ?>;
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = expireTime - now;
            
            if (distance > 0) {
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById('countdown').innerHTML = 
                    String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            } else {
                document.getElementById('countdown').innerHTML = 'หมดเวลา';
                document.getElementById('countdown').className = 'font-bold text-red-600';
            }
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        <?php endif; ?>

        // OTP input formatting
        <?php if ($step == 2): ?>
        document.getElementById('otp').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
        
        document.getElementById('otp').focus();
        <?php endif; ?>

        // Password strength checker
        <?php if ($step == 3): ?>
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthBar = document.getElementById('password-strength');
            const feedback = document.getElementById('password-feedback');
            
            let strength = 0;
            let messages = [];
            
            if (password.length >= 6) strength += 20;
            else messages.push('อย่างน้อย 6 ตัวอักษร');
            
            if (password.match(/[a-z]/)) strength += 20;
            else messages.push('ตัวอักษรพิมพ์เล็ก');
            
            if (password.match(/[A-Z]/)) strength += 20;
            else messages.push('ตัวอักษรพิมพ์ใหญ่');
            
            if (password.match(/[0-9]/)) strength += 20;
            else messages.push('ตัวเลข');
            
            if (password.match(/[^a-zA-Z0-9]/)) strength += 20;
            else messages.push('อักขระพิเศษ');
            
            strengthBar.style.width = strength + '%';
            
            if (strength < 40) {
                strengthBar.className = 'bg-red-500 h-2 rounded-full transition-all duration-300';
                feedback.textContent = 'อ่อน: ' + messages.join(', ');
            } else if (strength < 80) {
                strengthBar.className = 'bg-yellow-500 h-2 rounded-full transition-all duration-300';
                feedback.textContent = 'ปานกลาง: ' + messages.join(', ');
            } else {
                strengthBar.className = 'bg-green-500 h-2 rounded-full transition-all duration-300';
                feedback.textContent = 'แข็งแกร่ง';
                feedback.className = 'text-xs text-green-600 mt-1';
            }
        }
        
        document.getElementById('new_password').addEventListener('input', checkPasswordStrength);
        
        // Password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.setCustomValidity('รหัสผ่านไม่ตรงกัน');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });
        <?php endif; ?>

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ กำลังดำเนินการ...';
            });
        });
    </script>
</body>
</html>