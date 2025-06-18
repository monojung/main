<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin role
requireAdmin('../login.php');

$page_title = "จัดการนัดหมาย";

// Handle actions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($action === 'update_status' && $appointment_id > 0) {
            $new_status = sanitizeInput($_POST['status'] ?? '');
            $notes = sanitizeInput($_POST['notes'] ?? '');
            
            $valid_statuses = array('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show');
            
            if (in_array($new_status, $valid_statuses)) {
                // Get old status for logging
                $stmt = $conn->prepare("SELECT status, patient_name FROM appointments WHERE id = ?");
                $stmt->execute(array($appointment_id));
                $old_data = $stmt->fetch();
                
                if ($old_data) {
                    // Update appointment
                    $update_fields = array();
                    $update_params = array();
                    
                    $update_fields[] = "status = ?";
                    $update_params[] = $new_status;
                    
                    if (!empty($notes)) {
                        $update_fields[] = "notes = ?";
                        $update_params[] = $notes;
                    }
                    
                    // Set timestamp fields based on status
                    if ($new_status === 'confirmed') {
                        $update_fields[] = "confirmed_by = ?, confirmed_at = NOW()";
                        $update_params[] = $_SESSION['user_id'];
                    } elseif ($new_status === 'in_progress') {
                        $update_fields[] = "checked_in_at = NOW()";
                    } elseif ($new_status === 'completed') {
                        $update_fields[] = "completed_at = NOW()";
                    }
                    
                    $update_fields[] = "updated_at = NOW()";
                    $update_params[] = $appointment_id;
                    
                    $sql = "UPDATE appointments SET " . implode(', ', $update_fields) . " WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt->execute($update_params)) {
                        // Log activity
                        logActivity($conn, $_SESSION['user_id'], 'appointment_status_updated', 'appointments', $appointment_id, 
                                  array('old_status' => $old_data['status']), 
                                  array('new_status' => $new_status, 'notes' => $notes));
                        
                        $message = "อัพเดทสถานะการนัดหมายของ " . htmlspecialchars($old_data['patient_name']) . " เรียบร้อยแล้ว";
                    } else {
                        $error = "เกิดข้อผิดพลาดในการอัพเดท";
                    }
                } else {
                    $error = "ไม่พบการนัดหมายนี้";
                }
            } else {
                $error = "สถานะไม่ถูกต้อง";
            }
        } elseif ($action === 'delete' && $appointment_id > 0) {
            // Get appointment info before deletion
            $stmt = $conn->prepare("SELECT patient_name FROM appointments WHERE id = ?");
            $stmt->execute(array($appointment_id));
            $appointment_info = $stmt->fetch();
            
            if ($appointment_info) {
                $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
                if ($stmt->execute(array($appointment_id))) {
                    logActivity($conn, $_SESSION['user_id'], 'appointment_deleted', 'appointments', $appointment_id, 
                              array('patient_name' => $appointment_info['patient_name']), null);
                    $message = "ลบการนัดหมายเรียบร้อยแล้ว";
                } else {
                    $error = "เกิดข้อผิดพลาดในการลบ";
                }
            } else {
                $error = "ไม่พบการนัดหมายนี้";
            }
        }
    } catch (Exception $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        logError($e->getMessage(), __FILE__, __LINE__);
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$department_filter = $_GET['department'] ?? '';
$date_filter = $_GET['date'] ?? '';
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
    
    if (!empty($status_filter)) {
        $where_conditions[] = "a.status = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($department_filter)) {
        $where_conditions[] = "a.department_id = ?";
        $params[] = $department_filter;
    }
    
    if (!empty($date_filter)) {
        $where_conditions[] = "a.appointment_date = ?";
        $params[] = $date_filter;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(a.patient_name LIKE ? OR a.patient_phone LIKE ? OR a.appointment_number LIKE ?)";
        $search_term = '%' . $search . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM appointments a 
        LEFT JOIN departments d ON a.department_id = d.id 
        WHERE $where_clause
    ";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_appointments = $stmt->fetch()['total'];
    
    // Get appointments
    $list_params = $params;
    $list_params[] = $per_page;
    $list_params[] = $offset;
    
    $appointments_sql = "
        SELECT a.*, d.name as department_name, 
               u.first_name as confirmed_by_name, u.last_name as confirmed_by_lastname
        FROM appointments a 
        LEFT JOIN departments d ON a.department_id = d.id 
        LEFT JOIN users u ON a.confirmed_by = u.id
        WHERE $where_clause
        ORDER BY a.appointment_date DESC, a.appointment_time DESC, a.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($appointments_sql);
    $stmt->execute($list_params);
    $appointments = $stmt->fetchAll();
    
    // Get departments for filter
    $stmt = $conn->prepare("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $departments = $stmt->fetchAll();
    
    // Get statistics
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN appointment_date = CURDATE() THEN 1 ELSE 0 END) as today
        FROM appointments
    ";
    $stmt = $conn->prepare($stats_sql);
    $stmt->execute();
    $stats = $stmt->fetch();
    
} catch (Exception $e) {
    $error = "เกิดข้อผิดพลาดในการโหลดข้อมูล";
    logError($e->getMessage(), __FILE__, __LINE__);
    $appointments = array();
    $departments = array();
    $stats = array('total' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'today' => 0);
    $total_appointments = 0;
}

$total_pages = ceil($total_appointments / $per_page);
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
                    <a href="appointments.php" class="block py-2 px-4 text-blue-600 bg-blue-50 rounded font-medium">
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
                    <a href="users.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
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
                <h2 class="text-3xl font-bold text-gray-800">จัดการนัดหมาย</h2>
                <p class="text-gray-600">จัดการและติดตามการนัดหมายของผู้ป่วย</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?></div>
                    <div class="text-sm text-gray-600">ทั้งหมด</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['pending']); ?></div>
                    <div class="text-sm text-gray-600">รอยืนยัน</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-green-600"><?php echo number_format($stats['confirmed']); ?></div>
                    <div class="text-sm text-gray-600">ยืนยันแล้ว</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['completed']); ?></div>
                    <div class="text-sm text-gray-600">เสร็จสิ้น</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-red-600"><?php echo number_format($stats['cancelled']); ?></div>
                    <div class="text-sm text-gray-600">ยกเลิก</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['today']); ?></div>
                    <div class="text-sm text-gray-600">วันนี้</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <form method="GET" class="grid md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ค้นหา</label>
                        <input type="text" name="search" placeholder="ชื่อ, โทรศัพท์, หมายเลขนัด" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">ทั้งหมด</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>รอยืนยัน</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>ยืนยันแล้ว</option>
                            <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>กำลังรักษา</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                            <option value="no_show" <?php echo $status_filter === 'no_show' ? 'selected' : ''; ?>>ไม่มาตามนัด</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">แผนก</label>
                        <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">ทั้งหมด</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo $department_filter == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">วันที่</label>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            🔍 กรอง
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($search) || !empty($status_filter) || !empty($department_filter) || !empty($date_filter)): ?>
                <div class="mt-4 pt-4 border-t">
                    <p class="text-sm text-gray-600">
                        ผลการค้นหา: <?php echo number_format($total_appointments); ?> รายการ
                        <a href="appointments.php" class="ml-4 text-blue-600 hover:text-blue-800">✕ ล้างการกรอง</a>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Appointments Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมายเลขนัด</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้ป่วย</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">แผนก</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่-เวลา</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-4xl mb-2">📅</div>
                                    ไม่พบข้อมูลการนัดหมาย
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($appointment['appointment_number']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        สร้าง: <?php echo formatThaiDateTime($appointment['created_at']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($appointment['patient_name']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        📞 <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                    </div>
                                    <?php if ($appointment['patient_email']): ?>
                                    <div class="text-xs text-gray-500">
                                        📧 <?php echo htmlspecialchars($appointment['patient_email']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($appointment['department_name'] ?? 'ไม่ระบุ'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo formatThaiDate($appointment['appointment_date']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        🕐 <?php echo $appointment['appointment_time']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_colors = array(
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-purple-100 text-purple-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'no_show' => 'bg-gray-100 text-gray-800'
                                    );
                                    $status_text = array(
                                        'pending' => 'รอยืนยัน',
                                        'confirmed' => 'ยืนยันแล้ว',
                                        'in_progress' => 'กำลังรักษา',
                                        'completed' => 'เสร็จสิ้น',
                                        'cancelled' => 'ยกเลิก',
                                        'no_show' => 'ไม่มาตามนัด'
                                    );
                                    $color = $status_colors[$appointment['status']] ?? 'bg-gray-100 text-gray-800';
                                    $text = $status_text[$appointment['status']] ?? $appointment['status'];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                        <?php echo $text; ?>
                                    </span>
                                    
                                    <?php if ($appointment['confirmed_by_name']): ?>
                                    <div class="text-xs text-gray-500 mt-1">
                                        ยืนยันโดย: <?php echo htmlspecialchars($appointment['confirmed_by_name'] . ' ' . $appointment['confirmed_by_lastname']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($appointment['confirmed_at']): ?>
                                    <div class="text-xs text-gray-500">
                                        เมื่อ: <?php echo formatThaiDateTime($appointment['confirmed_at']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="openStatusModal(<?php echo $appointment['id']; ?>, '<?php echo $appointment['status']; ?>', '<?php echo htmlspecialchars($appointment['patient_name'], ENT_QUOTES); ?>')" 
                                                class="text-blue-600 hover:text-blue-900">
                                            แก้ไข
                                        </button>
                                        <button onclick="viewDetails(<?php echo $appointment['id']; ?>)" 
                                                class="text-green-600 hover:text-green-900">
                                            ดู
                                        </button>
                                        <button onclick="deleteAppointment(<?php echo $appointment['id']; ?>, '<?php echo htmlspecialchars($appointment['patient_name'], ENT_QUOTES); ?>')" 
                                                class="text-red-600 hover:text-red-900">
                                            ลบ
                                        </button>
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
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ก่อนหน้า
                        </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
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
                                <span class="font-medium"><?php echo min($offset + $per_page, $total_appointments); ?></span> จาก 
                                <span class="font-medium"><?php echo number_format($total_appointments); ?></span> รายการ
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

    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="appointment_id" id="modal_appointment_id">
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-4">อัพเดทสถานะการนัดหมาย</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">ผู้ป่วย</label>
                        <p id="modal_patient_name" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="modal_status" class="block text-sm font-medium text-gray-700 mb-2">สถานะใหม่</label>
                        <select name="status" id="modal_status" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">เลือกสถานะ</option>
                            <option value="pending">รอยืนยัน</option>
                            <option value="confirmed">ยืนยันแล้ว</option>
                            <option value="in_progress">กำลังรักษา</option>
                            <option value="completed">เสร็จสิ้น</option>
                            <option value="cancelled">ยกเลิก</option>
                            <option value="no_show">ไม่มาตามนัด</option>
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label for="modal_notes" class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                        <textarea name="notes" id="modal_notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="หมายเหตุเพิ่มเติม (ไม่บังคับ)"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
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
        function openStatusModal(appointmentId, currentStatus, patientName) {
            document.getElementById('modal_appointment_id').value = appointmentId;
            document.getElementById('modal_patient_name').textContent = patientName;
            document.getElementById('modal_status').value = currentStatus;
            document.getElementById('modal_notes').value = '';
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }

        function viewDetails(appointmentId) {
            // Implementation for viewing appointment details
            alert('ฟีเจอร์ดูรายละเอียดจะพัฒนาต่อไป');
        }

        function deleteAppointment(appointmentId, patientName) {
            if (confirm(`ต้องการลบการนัดหมายของ "${patientName}" หรือไม่?\n\nการดำเนินการนี้ไม่สามารถยกเลิกได้`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="appointment_id" value="${appointmentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('statusModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Auto-refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>