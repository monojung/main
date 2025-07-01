<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin role
requireAdmin('../login.php');

$page_title = "จัดการผู้ใช้ระบบ";

// Handle actions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($action === 'add') {
            $username = sanitizeInput($_POST['username'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $first_name = sanitizeInput($_POST['first_name'] ?? '');
            $last_name = sanitizeInput($_POST['last_name'] ?? '');
            $role = sanitizeInput($_POST['role'] ?? 'staff');
            $phone = sanitizeInput($_POST['phone'] ?? '');
            
            if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
                $error = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
            } elseif (strlen($password) < 6) {
                $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
            } else {
                // Check if username or email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute(array($username, $email));
                if ($stmt->fetch()) {
                    $error = 'ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว';
                } else {
                    $password_hash = hashPassword($password);
                    
                    $stmt = $conn->prepare("
                        INSERT INTO users (username, email, password_hash, first_name, last_name, 
                                         role, phone, is_active, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
                    ");
                    
                    if ($stmt->execute(array(
                        $username, $email, $password_hash, $first_name, $last_name,
                        $role, $phone
                    ))) {
                        $user_id = $conn->lastInsertId();
                        logActivity($conn, $_SESSION['user_id'], 'user_created', 'users', $user_id, null, array(
                            'username' => $username,
                            'role' => $role
                        ));
                        $message = 'เพิ่มผู้ใช้ใหม่เรียบร้อยแล้ว';
                    } else {
                        $error = 'เกิดข้อผิดพลาดในการบันทึก';
                    }
                }
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $username = sanitizeInput($_POST['username'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $first_name = sanitizeInput($_POST['first_name'] ?? '');
            $last_name = sanitizeInput($_POST['last_name'] ?? '');
            $role = sanitizeInput($_POST['role'] ?? 'staff');
            $phone = sanitizeInput($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($email) || empty($first_name) || empty($last_name)) {
                $error = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
            } else {
                // Get old data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute(array($id));
                $old_data = $stmt->fetch();
                
                if ($old_data) {
                    // Check if username or email already exists (except current user)
                    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                    $stmt->execute(array($username, $email, $id));
                    if ($stmt->fetch()) {
                        $error = 'ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว';
                    } else {
                        $update_fields = array(
                            "username = ?", "email = ?", "first_name = ?", "last_name = ?",
                            "role = ?", "phone = ?", "updated_at = NOW()"
                        );
                        $params = array($username, $email, $first_name, $last_name, $role, $phone);
                        
                        // Update password if provided
                        if (!empty($password)) {
                            if (strlen($password) < 6) {
                                $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
                            } else {
                                $update_fields[] = "password_hash = ?";
                                $params[] = hashPassword($password);
                            }
                        }
                        
                        if (!$error) {
                            $params[] = $id;
                            $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            
                            if ($stmt->execute($params)) {
                                logActivity($conn, $_SESSION['user_id'], 'user_updated', 'users', $id, array(
                                    'username' => $old_data['username'],
                                    'role' => $old_data['role']
                                ), array(
                                    'username' => $username,
                                    'role' => $role
                                ));
                                $message = 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว';
                            } else {
                                $error = 'เกิดข้อผิดพลาดในการแก้ไข';
                            }
                        }
                    }
                } else {
                    $error = 'ไม่พบผู้ใช้นี้';
                }
            }
        } elseif ($action === 'toggle_status') {
            $id = (int)($_POST['id'] ?? 0);
            
            // Prevent disabling own account
            if ($id == $_SESSION['user_id']) {
                $error = 'ไม่สามารถปิดใช้งานบัญชีของตนเองได้';
            } else {
                // Get current status
                $stmt = $conn->prepare("SELECT is_active, username FROM users WHERE id = ?");
                $stmt->execute(array($id));
                $user_data = $stmt->fetch();
                
                if ($user_data) {
                    $new_status = $user_data['is_active'] ? 0 : 1;
                    $stmt = $conn->prepare("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?");
                    
                    if ($stmt->execute(array($new_status, $id))) {
                        $action_text = $new_status ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
                        logActivity($conn, $_SESSION['user_id'], 'user_status_changed', 'users', $id, array(
                            'old_status' => $user_data['is_active']
                        ), array(
                            'new_status' => $new_status
                        ));
                        $message = $action_text . 'บัญชี ' . $user_data['username'] . ' เรียบร้อยแล้ว';
                    } else {
                        $error = 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะ';
                    }
                } else {
                    $error = 'ไม่พบผู้ใช้นี้';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            
            // Prevent deleting own account
            if ($id == $_SESSION['user_id']) {
                $error = 'ไม่สามารถลบบัญชีของตนเองได้';
            } else {
                // Get user info before deletion
                $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $stmt->execute(array($id));
                $user_info = $stmt->fetch();
                
                if ($user_info) {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    if ($stmt->execute(array($id))) {
                        logActivity($conn, $_SESSION['user_id'], 'user_deleted', 'users', $id, array(
                            'username' => $user_info['username']
                        ), null);
                        $message = 'ลบผู้ใช้เรียบร้อยแล้ว';
                    } else {
                        $error = 'เกิดข้อผิดพลาดในการลบ';
                    }
                } else {
                    $error = 'ไม่พบผู้ใช้นี้';
                }
            }
        }
    } catch (Exception $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        logError($e->getMessage(), __FILE__, __LINE__);
    }
}

// Get filter parameters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Build WHERE clause
    $where_conditions = array("1=1");
    $params = array();
    
    if (!empty($role_filter)) {
        $where_conditions[] = "u.role = ?";
        $params[] = $role_filter;
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "u.is_active = ?";
        $params[] = ($status_filter === 'active') ? 1 : 0;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $search_term = '%' . $search . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM users u 
        WHERE $where_clause
    ";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_users = $stmt->fetch()['total'];
    
    // Get users list
    $list_params = $params;
    $list_params[] = $per_page;
    $list_params[] = $offset;
    
    $users_sql = "
        SELECT u.*
        FROM users u 
        WHERE $where_clause
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($users_sql);
    $stmt->execute($list_params);
    $users = $stmt->fetchAll();
    
    // Get statistics
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
            SUM(CASE WHEN role = 'doctor' THEN 1 ELSE 0 END) as doctor_count,
            SUM(CASE WHEN role = 'nurse' THEN 1 ELSE 0 END) as nurse_count,
            SUM(CASE WHEN role = 'staff' THEN 1 ELSE 0 END) as staff_count,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_count
        FROM users
    ";
    $stmt = $conn->prepare($stats_sql);
    $stmt->execute();
    $stats = $stmt->fetch();
    
} catch (Exception $e) {
    $error = "เกิดข้อผิดพลาดในการโหลดข้อมูล";
    logError($e->getMessage(), __FILE__, __LINE__);
    $users = array();
    $stats = array('total' => 0, 'admin_count' => 0, 'doctor_count' => 0, 'nurse_count' => 0, 'staff_count' => 0, 'active_count' => 0, 'inactive_count' => 0);
    $total_users = 0;
}

$total_pages = ceil($total_users / $per_page);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .hover-lift { transition: transform 0.2s ease; }
        .hover-lift:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Enhanced Navigation -->
    <nav class="bg-gradient-to-r from-blue-800 to-blue-900 text-white shadow-xl">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <span class="text-white font-bold text-lg">THC</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">ระบบจัดการโรงพยาบาลทุ่งหัวช้าง</h1>
                        <p class="text-blue-200 text-sm">ระบบจัดการข่าวสารและประกาศ</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p class="text-xs text-blue-200"><?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition duration-300 hover-lift">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex min-h-screen">
        <!-- Enhanced Sidebar -->
        <aside class="w-64 bg-white shadow-xl border-r border-gray-200">
            <div class="p-6">
                <div class="space-y-2">
                    <a href="dashboard.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">📊</span> แดชบอร์ด
                    </a>
                    <a href="news.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">📰</span> จัดการข่าวสาร
                    </a>
                    <a href="reports.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">📊</span> รายงาน
                    </a>
                    <a href="users.php" class="flex items-center py-3 px-4 text-blue-600 bg-blue-50 rounded-lg font-medium border-l-4 border-blue-600">
                        <span class="text-xl mr-3">👨‍💼</span> จัดการผู้ใช้
                    </a>
                    <a href="settings.php" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">⚙️</span> ตั้งค่าระบบ
                    </a>
                    <hr class="my-3">
                    <a href="../index.php" target="_blank" class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200">
                        <span class="text-xl mr-3">🌐</span> เว็บไซต์หลัก
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Enhanced Messages -->
            <?php if ($message): ?>
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 fade-in shadow-sm">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span><?php echo $message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 fade-in shadow-sm">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Enhanced Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-800 mb-2">จัดการผู้ใช้ระบบ</h2>
                        <p class="text-gray-600">จัดการบัญชีผู้ใช้และสิทธิ์การเข้าถึงระบบ</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">อัปเดตล่าสุด</p>
                        <p class="text-lg font-semibold text-gray-700"><?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['total']); ?></div>
                            <div class="text-blue-100">ทั้งหมด</div>
                        </div>
                        <div class="text-4xl opacity-80">👥</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['admin_count']); ?></div>
                            <div class="text-red-100">Admin</div>
                        </div>
                        <div class="text-4xl opacity-80">👑</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['doctor_count']); ?></div>
                            <div class="text-green-100">แพทย์</div>
                        </div>
                        <div class="text-4xl opacity-80">👨‍⚕️</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['nurse_count']); ?></div>
                            <div class="text-purple-100">พยาบาล</div>
                        </div>
                        <div class="text-4xl opacity-80">👩‍⚕️</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['staff_count']); ?></div>
                            <div class="text-orange-100">เจ้าหน้าที่</div>
                        </div>
                        <div class="text-4xl opacity-80">👨‍💼</div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl shadow-lg p-6 hover-lift">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold"><?php echo number_format($stats['active_count']); ?></div>
                            <div class="text-teal-100">ใช้งานได้</div>
                        </div>
                        <div class="text-4xl opacity-80">✅</div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Action Bar -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                    <button onclick="openAddModal()" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-300 hover-lift shadow-lg">
                        <span class="text-xl mr-2">➕</span> เพิ่มผู้ใช้ใหม่
                    </button>
                    
                    <!-- Enhanced Search and Filter -->
                    <form method="GET" class="flex flex-col md:flex-row gap-3">
                        <div class="relative">
                            <input type="text" name="search" placeholder="ค้นหาผู้ใช้..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                            <span class="absolute left-3 top-3 text-gray-400">🔍</span>
                        </div>
                        
                        <select name="role" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">ทุกบทบาท</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>👑 Admin</option>
                            <option value="doctor" <?php echo $role_filter === 'doctor' ? 'selected' : ''; ?>>👨‍⚕️ แพทย์</option>
                            <option value="nurse" <?php echo $role_filter === 'nurse' ? 'selected' : ''; ?>>👩‍⚕️ พยาบาล</option>
                            <option value="staff" <?php echo $role_filter === 'staff' ? 'selected' : ''; ?>>👨‍💼 เจ้าหน้าที่</option>
                        </select>
                        
                        <select name="status" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">ทุกสถานะ</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>✅ ใช้งานได้</option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>❌ ปิดใช้งาน</option>
                        </select>
                        
                        <button type="submit" 
                                class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition duration-300 hover-lift">
                            🔍 ค้นหา
                        </button>
                        
                        <?php if (!empty($search) || !empty($role_filter) || !empty($status_filter)): ?>
                        <a href="users.php" class="bg-gray-400 text-white px-6 py-3 rounded-lg hover:bg-gray-500 transition duration-300 hover-lift">
                            ✕ ล้าง
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Enhanced Users Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>👤</span>
                                        <span>ผู้ใช้</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>🎭</span>
                                        <span>บทบาท</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>📊</span>
                                        <span>สถานะ</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>🕐</span>
                                        <span>เข้าใช้ล่าสุด</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>🔧</span>
                                        <span>จัดการ</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                    <div class="text-6xl mb-4">👨‍💼</div>
                                    <div class="text-xl font-semibold mb-2">ไม่พบข้อมูลผู้ใช้</div>
                                    <div class="text-gray-400">ลองปรับเปลี่ยนคำค้นหาหรือเพิ่มผู้ใช้ใหม่</div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50 transition duration-200">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                            <span class="text-white font-bold text-lg">
                                                <?php echo mb_substr($user['first_name'], 0, 1) . mb_substr($user['last_name'], 0, 1); ?>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-sm font-semibold text-gray-900 mb-1">
                                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">👤 คุณ</span>
                                                <?php endif; ?>
                                            </h3>
                                            <p class="text-sm text-gray-600 mb-1">@<?php echo htmlspecialchars($user['username']); ?></p>
                                            <p class="text-xs text-gray-400">📧 <?php echo htmlspecialchars($user['email']); ?></p>
                                            <?php if ($user['phone']): ?>
                                            <p class="text-xs text-gray-400">📞 <?php echo htmlspecialchars($user['phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $role_configs = array(
                                        'admin' => ['name' => 'ผู้ดูแลระบบ', 'color' => 'bg-red-100 text-red-800', 'icon' => '👑'],
                                        'doctor' => ['name' => 'แพทย์', 'color' => 'bg-green-100 text-green-800', 'icon' => '👨‍⚕️'],
                                        'nurse' => ['name' => 'พยาบาล', 'color' => 'bg-purple-100 text-purple-800', 'icon' => '👩‍⚕️'],
                                        'staff' => ['name' => 'เจ้าหน้าที่', 'color' => 'bg-blue-100 text-blue-800', 'icon' => '👨‍💼']
                                    );
                                    $role_config = $role_configs[$user['role']] ?? ['name' => $user['role'], 'color' => 'bg-gray-100 text-gray-800', 'icon' => '👤'];
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $role_config['color']; ?>">
                                        <?php echo $role_config['icon']; ?> <?php echo $role_config['name']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <?php if ($user['is_active']): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            ✅ ใช้งานได้
                                        </span>
                                        <?php else: ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            ❌ ปิดใช้งาน
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <?php if ($user['last_login']): ?>
                                        <div class="text-gray-900 font-medium"><?php echo formatThaiDateTime($user['last_login']); ?></div>
                                        <?php else: ?>
                                        <span class="text-gray-400 italic">ยังไม่เคยเข้าใช้</span>
                                        <?php endif; ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            สร้าง: <?php echo formatThaiDate($user['created_at']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                                class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-3 py-1 rounded-lg transition duration-200 text-xs font-medium">
                                            ✏️ แก้ไข
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>', <?php echo $user['is_active']; ?>)" 
                                                class="bg-yellow-100 text-yellow-600 hover:bg-yellow-200 px-3 py-1 rounded-lg transition duration-200 text-xs font-medium">
                                            <?php echo $user['is_active'] ? '❌ ปิดใช้งาน' : '✅ เปิดใช้งาน'; ?>
                                        </button>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')" 
                                                class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg transition duration-200 text-xs font-medium">
                                            🗑️ ลบ
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ← ก่อนหน้า
                        </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ถัดไป →
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                แสดง <span class="font-medium"><?php echo number_format($offset + 1); ?></span> ถึง 
                                <span class="font-medium"><?php echo number_format($total_users); ?></span> รายการ
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                                   class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    ←
                                </a>
                                <?php endif; ?>
                                
                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                                   class="relative inline-flex items-center px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    →
                                </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Enhanced Add/Edit User Modal -->
    <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl fade-in">
                <form method="POST" id="userForm">
                    <!-- Modal Header -->
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900" id="modalTitle">เพิ่มผู้ใช้ใหม่</h3>
                                <p class="text-gray-600 mt-1">กรอกข้อมูลผู้ใช้และกำหนดสิทธิ์การเข้าถึง</p>
                            </div>
                            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition duration-200">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <input type="hidden" name="action" id="modalAction" value="add">
                        <input type="hidden" name="id" id="modalId">
                        
                        <!-- Personal Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <span class="text-2xl mr-2">👤</span>
                                <h4 class="text-lg font-semibold text-blue-800">ข้อมูลส่วนตัว</h4>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="modalFirstName" class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-1">👤</span> ชื่อ <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <input type="text" name="first_name" id="modalFirstName" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="modalLastName" class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-1">👤</span> นามสกุล <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <input type="text" name="last_name" id="modalLastName" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="mt-4 space-y-2">
                                <label for="modalPhone" class="flex items-center text-sm font-medium text-gray-700">
                                    <span class="text-lg mr-1">📞</span> เบอร์โทรศัพท์
                                </label>
                                <input type="tel" name="phone" id="modalPhone" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="08X-XXX-XXXX">
                            </div>
                        </div>
                        
                        <!-- Account Info -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <span class="text-2xl mr-2">🔐</span>
                                <h4 class="text-lg font-semibold text-green-800">บัญชีผู้ใช้</h4>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="modalUsername" class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-1">👤</span> ชื่อผู้ใช้ <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <input type="text" name="username" id="modalUsername" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="อย่างน้อย 3 ตัวอักษร">
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="modalEmail" class="flex items-center text-sm font-medium text-gray-700">
                                        <span class="text-lg mr-1">📧</span> อีเมล <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <input type="email" name="email" id="modalEmail" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="example@email.com">
                                </div>
                            </div>
                            
                            <div class="mt-4 space-y-2">
                                <label for="modalPassword" class="flex items-center text-sm font-medium text-gray-700">
                                    <span class="text-lg mr-1">🔑</span> รหัสผ่าน <span id="passwordRequired" class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="password" name="password" id="modalPassword" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="อย่างน้อย 6 ตัวอักษร">
                                <p class="text-xs text-gray-500" id="passwordHelp" style="display: none;">
                                    สำหรับการแก้ไข: เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน
                                </p>
                            </div>
                        </div>
                        
                        <!-- Role -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <span class="text-2xl mr-2">🎭</span>
                                <h4 class="text-lg font-semibold text-purple-800">บทบาท</h4>
                            </div>
                            <div class="space-y-2">
                                <label for="modalRole" class="flex items-center text-sm font-medium text-gray-700">
                                    <span class="text-lg mr-1">🎭</span> บทบาท <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="role" id="modalRole" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="staff">👨‍💼 เจ้าหน้าที่</option>
                                    <option value="nurse">👩‍⚕️ พยาบาล</option>
                                    <option value="doctor">👨‍⚕️ แพทย์</option>
                                    <option value="admin">👑 ผู้ดูแลระบบ</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between rounded-b-2xl">
                        <div class="text-sm text-gray-500">
                            <span class="font-medium">💡 เคล็ดลับ:</span> ใช้ Ctrl+S เพื่อบันทึกข้อมูล
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeModal()" 
                                    class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                                ❌ ยกเลิก
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-200 shadow-lg">
                                💾 บันทึก
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Enhanced Modal Functions
        function openAddModal() {
            document.getElementById('modalTitle').innerHTML = '<span class="text-2xl mr-2">➕</span>เพิ่มผู้ใช้ใหม่';
            document.getElementById('modalAction').value = 'add';
            document.getElementById('modalId').value = '';
            document.getElementById('userForm').reset();
            document.getElementById('modalPassword').required = true;
            document.getElementById('passwordRequired').textContent = '*';
            document.getElementById('passwordHelp').style.display = 'none';
            document.getElementById('userModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function editUser(user) {
            document.getElementById('modalTitle').innerHTML = '<span class="text-2xl mr-2">✏️</span>แก้ไขผู้ใช้';
            document.getElementById('modalAction').value = 'edit';
            document.getElementById('modalId').value = user.id;
            document.getElementById('modalFirstName').value = user.first_name;
            document.getElementById('modalLastName').value = user.last_name;
            document.getElementById('modalUsername').value = user.username;
            document.getElementById('modalEmail').value = user.email;
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalRole').value = user.role;
            document.getElementById('modalPhone').value = user.phone || '';
            document.getElementById('modalPassword').required = false;
            document.getElementById('passwordRequired').textContent = '';
            document.getElementById('passwordHelp').style.display = 'block';
            document.getElementById('userModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function toggleUserStatus(id, username, currentStatus) {
            const action = currentStatus ? 'ปิดใช้งาน' : 'เปิดใช้งาน';
            const icon = currentStatus ? '❌' : '✅';
            
            // Create custom confirmation modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4">
                    <div class="text-center">
                        <div class="text-6xl mb-4">${icon}</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">${action}บัญชี?</h3>
                        <p class="text-gray-600 mb-6">ต้องการ${action}บัญชี "<strong>${username}</strong>" หรือไม่?</p>
                        <div class="flex space-x-4">
                            <button onclick="cancelToggle()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-200">ยกเลิก</button>
                            <button onclick="confirmToggle(${id})" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">ยืนยัน</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            window.cancelToggle = () => modal.remove();
            window.confirmToggle = (id) => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            };
        }

        function deleteUser(id, username) {
            // Create custom confirmation modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4">
                    <div class="text-center">
                        <div class="text-6xl mb-4">🗑️</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">ลบผู้ใช้?</h3>
                        <p class="text-gray-600 mb-2">ต้องการลบบัญชี "<strong>${username}</strong>" หรือไม่?</p>
                        <p class="text-red-600 text-sm mb-6">⚠️ การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
                        <div class="flex space-x-4">
                            <button onclick="cancelDelete()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-200">ยกเลิก</button>
                            <button onclick="confirmDelete(${id})" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">ลบ</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            window.cancelDelete = () => modal.remove();
            window.confirmDelete = (id) => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            };
        }

        // Close modal when clicking outside
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Enhanced Username validation
        document.getElementById('modalUsername').addEventListener('input', function() {
            const username = this.value;
            const isValid = /^[a-zA-Z0-9_]+$/.test(username) && username.length >= 3;
            
            this.classList.remove('border-red-500', 'border-green-500');
            
            if (username && !isValid) {
                this.setCustomValidity('ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร และใช้ได้เฉพาะ a-z, A-Z, 0-9, _');
                this.classList.add('border-red-500');
            } else if (username && isValid) {
                this.setCustomValidity('');
                this.classList.add('border-green-500');
            } else {
                this.setCustomValidity('');
            }
        });

        // Enhanced Password validation
        document.getElementById('modalPassword').addEventListener('input', function() {
            const password = this.value;
            const isEdit = document.getElementById('modalAction').value === 'edit';
            
            this.classList.remove('border-red-500', 'border-green-500');
            
            if (!isEdit && password.length > 0 && password.length < 6) {
                this.setCustomValidity('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                this.classList.add('border-red-500');
            } else if (isEdit && password.length > 0 && password.length < 6) {
                this.setCustomValidity('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                this.classList.add('border-red-500');
            } else if (password.length >= 6) {
                this.setCustomValidity('');
                this.classList.add('border-green-500');
            } else {
                this.setCustomValidity('');
            }
        });

        // Enhanced Email validation
        document.getElementById('modalEmail').addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            this.classList.remove('border-red-500', 'border-green-500');
            
            if (email && !emailRegex.test(email)) {
                this.setCustomValidity('รูปแบบอีเมลไม่ถูกต้อง');
                this.classList.add('border-red-500');
            } else if (email && emailRegex.test(email)) {
                this.setCustomValidity('');
                this.classList.add('border-green-500');
            } else {
                this.setCustomValidity('');
            }
        });

        // Phone number formatting and validation
        document.getElementById('modalPhone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            // Format phone number
            if (value.length <= 10) {
                if (value.length >= 3) {
                    value = value.substring(0, 3) + '-' + value.substring(3);
                }
                if (value.length >= 7) {
                    value = value.substring(0, 7) + '-' + value.substring(7, 11);
                }
                this.value = value;
            }
        });

        // Enhanced form validation
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('input[required], select[required]');
            let isValid = true;
            let firstInvalidField = null;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500', 'bg-red-50');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = field;
                } else {
                    field.classList.remove('border-red-500', 'bg-red-50');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg z-50 fade-in';
                alert.innerHTML = `
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">❌</span>
                        <span>กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน</span>
                    </div>
                `;
                document.body.appendChild(alert);
                
                // Focus first invalid field
                if (firstInvalidField) {
                    firstInvalidField.focus();
                }
                
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + S to save form
            if (e.ctrlKey && e.key === 's' && !document.getElementById('userModal').classList.contains('hidden')) {
                e.preventDefault();
                document.getElementById('userForm').submit();
            }
            
            // Escape to close modal
            if (e.key === 'Escape' && !document.getElementById('userModal').classList.contains('hidden')) {
                closeModal();
            }
            
            // Ctrl + N to add new user
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                openAddModal();
            }
        });

        // Auto-hide messages with fade effect
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-50, .bg-red-50');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s, transform 0.5s';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    message.remove();
                }, 500);
            });
        }, 5000);

        // Add loading state to form submission
        document.getElementById('userForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span> กำลังบันทึก...';
            submitBtn.disabled = true;
            submitBtn.classList.add('cursor-not-allowed', 'opacity-75');
            
            // Re-enable after 5 seconds as fallback
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('cursor-not-allowed', 'opacity-75');
            }, 5000);
        });

        // Initialize enhanced UI features
        console.log('🎉 Enhanced Users Management UI loaded successfully!');
    </script>
</body>
</html>><?php echo number_format(min($offset + $per_page, $total_users)); ?></span> จาก 
                                <span class="font-medium"><?php echo number_format($total_users); ?></span> รายการ
                            </p>    