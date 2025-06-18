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
            $first_name = sanitizeInput($_POST['first_name'] ?? '');
            $last_name = sanitizeInput($_POST['last_name'] ?? '');
            $role = sanitizeInput($_POST['role'] ?? 'staff');
            $department_id = $_POST['department_id'] ? (int)$_POST['department_id'] : null;
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
                            "role = ?", "department_id = ?", "phone = ?", "updated_at = NOW()"
                        );
                        $params = array($username, $email, $first_name, $last_name, $role, $department_id, $phone);
                        
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
$department_filter = $_GET['department'] ?? '';
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
    
    if (!empty($department_filter)) {
        $where_conditions[] = "u.department_id = ?";
        $params[] = $department_filter;
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
        LEFT JOIN departments d ON u.department_id = d.id 
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
        SELECT u.*, d.name as department_name
        FROM users u 
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE $where_clause
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($users_sql);
    $stmt->execute($list_params);
    $users = $stmt->fetchAll();
    
    // Get departments for dropdown
    $stmt = $conn->prepare("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $departments = $stmt->fetchAll();
    
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
    $departments = array();
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
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">THC</span>
                    </div>
                    <h1 class="text-xl font-bold">ระบบจัดการโรงพยาบาลทุ่งหัวช้าง</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span>สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition duration-300">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-6">
                <div class="space-y-2">
                    <a href="dashboard.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📊 แดชบอร์ด
                    </a>
                    <a href="appointments.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📅 จัดการนัดหมาย
                    </a>
                    <a href="patients.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👥 ข้อมูลผู้ป่วย
                    </a>
                    <a href="doctors.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👨‍⚕️ จัดการแพทย์
                    </a>
                    <a href="departments.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        🏥 จัดการแผนก
                    </a>
                    <a href="news.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📰 จัดการข่าวสาร
                    </a>
                    <a href="users.php" class="block py-2 px-4 text-blue-600 bg-blue-50 rounded font-medium">
                        👨‍💼 จัดการผู้ใช้
                    </a>
                    <a href="reports.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        📊 รายงาน
                    </a>
                    <a href="settings.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        ⚙️ ตั้งค่าระบบ
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                ✅ <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                ❌ <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800">จัดการผู้ใช้ระบบ</h2>
                <p class="text-gray-600">จัดการบัญชีผู้ใช้และสิทธิ์การเข้าถึงระบบ</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?></div>
                    <div class="text-sm text-gray-600">ทั้งหมด</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-red-600"><?php echo number_format($stats['admin_count']); ?></div>
                    <div class="text-sm text-gray-600">Admin</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-green-600"><?php echo number_format($stats['doctor_count']); ?></div>
                    <div class="text-sm text-gray-600">แพทย์</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['nurse_count']); ?></div>
                    <div class="text-sm text-gray-600">พยาบาล</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-orange-600"><?php echo number_format($stats['staff_count']); ?></div>
                    <div class="text-sm text-gray-600">เจ้าหน้าที่</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-green-600"><?php echo number_format($stats['active_count']); ?></div>
                    <div class="text-sm text-gray-600">ใช้งานได้</div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <button onclick="openAddModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300 mb-4 md:mb-0">
                    ➕ เพิ่มผู้ใช้ใหม่
                </button>
                
                <!-- Search and Filter -->
                <form method="GET" class="flex flex-col md:flex-row gap-2">
                    <input type="text" name="search" placeholder="ค้นหาผู้ใช้..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    
                    <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">ทุกบทบาท</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="doctor" <?php echo $role_filter === 'doctor' ? 'selected' : ''; ?>>แพทย์</option>
                        <option value="nurse" <?php echo $role_filter === 'nurse' ? 'selected' : ''; ?>>พยาบาล</option>
                        <option value="staff" <?php echo $role_filter === 'staff' ? 'selected' : ''; ?>>เจ้าหน้าที่</option>
                    </select>
                    
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">ทุกสถานะ</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>ใช้งานได้</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>ปิดใช้งาน</option>
                    </select>
                    
                    <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        🔍 ค้นหา
                    </button>
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้ใช้</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">บทบาท</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">แผนก</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เข้าใช้ล่าสุด</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-4xl mb-2">👨‍💼</div>
                                    ไม่พบข้อมูลผู้ใช้
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-gray-600 font-semibold">
                                                <?php echo mb_substr($user['first_name'], 0, 1) . mb_substr($user['last_name'], 0, 1); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">คุณ</span>
                                                <?php endif; ?>
                                            </h3>
                                            <p class="text-sm text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></p>
                                            <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                                            <?php if ($user['phone']): ?>
                                            <p class="text-xs text-gray-400">📞 <?php echo htmlspecialchars($user['phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $role_colors = array(
                                        'admin' => 'bg-red-100 text-red-800',
                                        'doctor' => 'bg-green-100 text-green-800',
                                        'nurse' => 'bg-purple-100 text-purple-800',
                                        'staff' => 'bg-blue-100 text-blue-800'
                                    );
                                    $role_names = array(
                                        'admin' => 'ผู้ดูแลระบบ',
                                        'doctor' => 'แพทย์',
                                        'nurse' => 'พยาบาล',
                                        'staff' => 'เจ้าหน้าที่'
                                    );
                                    $role_color = $role_colors[$user['role']] ?? 'bg-gray-100 text-gray-800';
                                    $role_name = $role_names[$user['role']] ?? $user['role'];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $role_color; ?>">
                                        <?php echo $role_name; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($user['department_name'] ?? 'ไม่ระบุ'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($user['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ใช้งานได้
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        ปิดใช้งาน
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($user['locked_until'] && strtotime($user['locked_until']) > time()): ?>
                                    <div class="text-xs text-red-600 mt-1">
                                        🔒 ล็อค
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($user['last_login']): ?>
                                    <div><?php echo formatThaiDateTime($user['last_login']); ?></div>
                                    <?php else: ?>
                                    <span class="text-gray-400">ยังไม่เคยเข้าใช้</span>
                                    <?php endif; ?>
                                    <div class="text-xs text-gray-400">
                                        สร้าง: <?php echo formatThaiDate($user['created_at']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            แก้ไข
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>', <?php echo $user['is_active']; ?>)" 
                                                class="text-yellow-600 hover:text-yellow-900">
                                            <?php echo $user['is_active'] ? 'ปิดใช้งาน' : 'เปิดใช้งาน'; ?>
                                        </button>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')" 
                                                class="text-red-600 hover:text-red-900">
                                            ลบ
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

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ถัดไป
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                แสดง <span class="font-medium"><?php echo $offset + 1; ?></span> ถึง 
                                <span class="font-medium"><?php echo min($offset + $per_page, $total_users); ?></span> จาก 
                                <span class="font-medium"><?php echo number_format($total_users); ?></span> รายการ
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
                <form method="POST" id="userForm">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900" id="modalTitle">เพิ่มผู้ใช้ใหม่</h3>
                        <button type="button" onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                            <span class="sr-only">ปิด</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <input type="hidden" name="action" id="modalAction" value="add">
                        <input type="hidden" name="id" id="modalId">
                        
                        <!-- Personal Info -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="modalFirstName" class="block text-sm font-medium text-gray-700 mb-2">ชื่อ *</label>
                                <input type="text" name="first_name" id="modalFirstName" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="modalLastName" class="block text-sm font-medium text-gray-700 mb-2">นามสกุล *</label>
                                <input type="text" name="last_name" id="modalLastName" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <!-- Account Info -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="modalUsername" class="block text-sm font-medium text-gray-700 mb-2">ชื่อผู้ใช้ *</label>
                                <input type="text" name="username" id="modalUsername" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="modalEmail" class="block text-sm font-medium text-gray-700 mb-2">อีเมล *</label>
                                <input type="email" name="email" id="modalEmail" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <!-- Password -->
                        <div>
                            <label for="modalPassword" class="block text-sm font-medium text-gray-700 mb-2">
                                รหัสผ่าน <span id="passwordRequired">*</span>
                            </label>
                            <input type="password" name="password" id="modalPassword" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="อย่างน้อย 6 ตัวอักษร">
                            <p class="text-xs text-gray-500 mt-1" id="passwordHelp">
                                สำหรับการแก้ไข: เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน
                            </p>
                        </div>
                        
                        <!-- Role and Department -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="modalRole" class="block text-sm font-medium text-gray-700 mb-2">บทบาท *</label>
                                <select name="role" id="modalRole" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="staff">เจ้าหน้าที่</option>
                                    <option value="nurse">พยาบาล</option>
                                    <option value="doctor">แพทย์</option>
                                    <option value="admin">ผู้ดูแลระบบ</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="modalDepartment" class="block text-sm font-medium text-gray-700 mb-2">แผนก</label>
                                <select name="department_id" id="modalDepartment" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">ไม่ระบุ</option>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>">
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Phone -->
                        <div>
                            <label for="modalPhone" class="block text-sm font-medium text-gray-700 mb-2">เบอร์โทรศัพท์</label>
                            <input type="tel" name="phone" id="modalPhone" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 text-right space-x-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            ยกเลิก
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'เพิ่มผู้ใช้ใหม่';
            document.getElementById('modalAction').value = 'add';
            document.getElementById('modalId').value = '';
            document.getElementById('userForm').reset();
            document.getElementById('modalPassword').required = true;
            document.getElementById('passwordRequired').textContent = '*';
            document.getElementById('passwordHelp').style.display = 'none';
            document.getElementById('userModal').classList.remove('hidden');
        }

        function editUser(user) {
            document.getElementById('modalTitle').textContent = 'แก้ไขผู้ใช้';
            document.getElementById('modalAction').value = 'edit';
            document.getElementById('modalId').value = user.id;
            document.getElementById('modalFirstName').value = user.first_name;
            document.getElementById('modalLastName').value = user.last_name;
            document.getElementById('modalUsername').value = user.username;
            document.getElementById('modalEmail').value = user.email;
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalRole').value = user.role;
            document.getElementById('modalDepartment').value = user.department_id || '';
            document.getElementById('modalPhone').value = user.phone || '';
            document.getElementById('modalPassword').required = false;
            document.getElementById('passwordRequired').textContent = '';
            document.getElementById('passwordHelp').style.display = 'block';
            document.getElementById('userModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        function toggleUserStatus(id, username, currentStatus) {
            const action = currentStatus ? 'ปิดใช้งาน' : 'เปิดใช้งาน';
            if (confirm(`ต้องการ${action}บัญชี "${username}" หรือไม่?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteUser(id, username) {
            if (confirm(`ต้องการลบบัญชี "${username}" หรือไม่?\n\nการดำเนินการนี้ไม่สามารถยกเลิกได้`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Username validation
        document.getElementById('modalUsername').addEventListener('input', function() {
            const username = this.value;
            const isValid = /^[a-zA-Z0-9_]+$/.test(username) && username.length >= 3;
            
            if (username && !isValid) {
                this.setCustomValidity('ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร และใช้ได้เฉพาะ a-z, A-Z, 0-9, _');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });

        // Password validation
        document.getElementById('modalPassword').addEventListener('input', function() {
            const password = this.value;
            const isEdit = document.getElementById('modalAction').value === 'edit';
            
            if (!isEdit && password.length > 0 && password.length < 6) {
                this.setCustomValidity('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                this.classList.add('border-red-500');
            } else if (isEdit && password.length > 0 && password.length < 6) {
                this.setCustomValidity('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });

        // Email validation
        document.getElementById('modalEmail').addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.setCustomValidity('รูปแบบอีเมลไม่ถูกต้อง');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });

        // Phone number formatting
        document.getElementById('modalPhone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length <= 10) {
                this.value = value;
            }
        });
    </script>
</body>
</html>page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ก่อนหน้า
                        </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $Input($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $first_name = sanitizeInput($_POST['first_name'] ?? '');
            $last_name = sanitizeInput($_POST['last_name'] ?? '');
            $role = sanitizeInput($_POST['role'] ?? 'staff');
            $department_id = $_POST['department_id'] ? (int)$_POST['department_id'] : null;
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
                                         role, department_id, phone, is_active, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
                    ");
                    
                    if ($stmt->execute(array(
                        $username, $email, $password_hash, $first_name, $last_name,
                        $role, $department_id, $phone
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
            $email = sanitize