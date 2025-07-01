<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Require admin role
requireAdmin('../login.php');

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'ita';
$page_title = "จัดการ ITA Requests";

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'update_status':
                $request_id = (int)($_POST['request_id'] ?? 0);
                $new_status = sanitizeInput($_POST['status'] ?? '');
                $admin_notes = sanitizeInput($_POST['admin_notes'] ?? '');
                
                if ($request_id && in_array($new_status, ['pending', 'in_progress', 'completed', 'rejected'])) {
                    $stmt = $conn->prepare("
                        UPDATE ita_requests 
                        SET status = ?, admin_notes = ?, updated_by = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    
                    if ($stmt->execute([$new_status, $admin_notes, $_SESSION['user_id'], $request_id])) {
                        logActivity($conn, $_SESSION['user_id'], 'ita_status_updated', 'ita_requests', $request_id);
                        $message = "อัพเดทสถานะเรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถอัพเดทสถานะได้";
                    }
                }
                break;
                
            case 'delete':
                $request_id = (int)($_POST['request_id'] ?? 0);
                if ($request_id) {
                    $stmt = $conn->prepare("UPDATE ita_requests SET status = 'deleted' WHERE id = ?");
                    if ($stmt->execute([$request_id])) {
                        logActivity($conn, $_SESSION['user_id'], 'ita_deleted', 'ita_requests', $request_id);
                        $message = "ลบคำขอเรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถลบคำขอได้";
                    }
                }
                break;
        }
    } catch (Exception $e) {
        logError($e->getMessage(), __FILE__, __LINE__);
        $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filter options
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$search = sanitizeInput($_GET['search'] ?? '');

// Build query
$where_conditions = ["status != 'deleted'"];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($priority_filter) {
    $where_conditions[] = "priority = ?";
    $params[] = $priority_filter;
}

if ($search) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ? OR requester_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_query = "SELECT COUNT(*) FROM ita_requests WHERE $where_clause";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();

// Get records
$query = "
    SELECT ir.*, u.first_name, u.last_name 
    FROM ita_requests ir
    LEFT JOIN users u ON ir.updated_by = u.id
    WHERE $where_clause
    ORDER BY ir.created_at DESC
    LIMIT $per_page OFFSET $offset
";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$pagination = getPagination($total_records, $per_page, $page);

// Get statistics
$stats = [
    'total' => getTotalITA($conn),
    'pending' => getPendingITA($conn),
    'in_progress' => 0,
    'completed' => 0,
    'rejected' => 0
];

try {
    foreach (['in_progress', 'completed', 'rejected'] as $status) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ita_requests WHERE status = ?");
        $stmt->execute([$status]);
        $stats[$status] = $stmt->fetchColumn() ?? 0;
    }
} catch (Exception $e) {
    // Keep default values
}

// Priority colors
$priority_colors = [
    'low' => 'bg-green-100 text-green-800',
    'medium' => 'bg-yellow-100 text-yellow-800',
    'high' => 'bg-orange-100 text-orange-800',
    'urgent' => 'bg-red-100 text-red-800'
];

// Status colors
$status_colors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'in_progress' => 'bg-blue-100 text-blue-800',
    'completed' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800'
];
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
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-purple-700 text-white shadow-2xl sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                        <span class="text-white font-bold text-xl">🔧</span>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold">จัดการ ITA Requests</h1>
                        <p class="text-blue-200 text-sm">ระบบจัดการคำขอ IT Support</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-blue-200"><?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl transition duration-300 shadow-lg">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex min-h-screen">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 lg:p-8 overflow-x-hidden">
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 px-4 py-3 rounded-lg fade-in">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span><?php echo $message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg fade-in">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-8 fade-in">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">🔧 จัดการ ITA Requests</h2>
                <p class="text-gray-200">จัดการคำขอและปัญหาด้าน IT Support</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 lg:gap-6 mb-8">
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?></div>
                        <div class="text-sm text-gray-600">ทั้งหมด</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-yellow-600"><?php echo number_format($stats['pending']); ?></div>
                        <div class="text-sm text-gray-600">รอดำเนินการ</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-blue-600"><?php echo number_format($stats['in_progress']); ?></div>
                        <div class="text-sm text-gray-600">กำลังดำเนินการ</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-green-600"><?php echo number_format($stats['completed']); ?></div>
                        <div class="text-sm text-gray-600">เสร็จสิ้น</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-red-600"><?php echo number_format($stats['rejected']); ?></div>
                        <div class="text-sm text-gray-600">ปฏิเสธ</div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="glass-card rounded-2xl p-6 mb-8 fade-in">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🔍 ค้นหา</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="ชื่อ, หัวข้อ, รายละเอียด..." 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📊 สถานะ</label>
                        <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">ทั้งหมด</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                            <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>ปฏิเสธ</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">⚡ ความสำคัญ</label>
                        <select name="priority" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">ทั้งหมด</option>
                            <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>ต่ำ</option>
                            <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>ปานกลาง</option>
                            <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>สูง</option>
                            <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>ด่วนมาก</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300 font-medium">
                            🔍 ค้นหา
                        </button>
                    </div>
                </form>
            </div>

            <!-- Requests Table -->
            <div class="glass-card rounded-2xl overflow-hidden shadow-xl fade-in">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">📋 รายการคำขอ ITA</h3>
                        <div class="text-sm text-gray-600">
                            แสดง <?php echo number_format(count($requests)); ?> จาก <?php echo number_format($total_records); ?> รายการ
                        </div>
                    </div>
                </div>

                <?php if (empty($requests)): ?>
                <div class="p-12 text-center">
                    <div class="text-6xl mb-4">🔧</div>
                    <p class="text-gray-500 text-lg font-medium">ไม่มีคำขอ ITA</p>
                    <p class="text-gray-400 text-sm">คำขอจะแสดงที่นี่เมื่อมีการส่งคำขอ</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">คำขอ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้ขอ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ความสำคัญ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($requests as $request): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($request['title'] ?? ''); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo truncateText($request['description'] ?? '', 100); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['requester_name'] ?? ''); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['department'] ?? ''); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $priority_colors[$request['priority']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                        <?php 
                                        $priority_labels = ['low' => 'ต่ำ', 'medium' => 'ปานกลาง', 'high' => 'สูง', 'urgent' => 'ด่วนมาก'];
                                        echo $priority_labels[$request['priority']] ?? $request['priority']; 
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $status_colors[$request['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                        <?php 
                                        $status_labels = ['pending' => 'รอดำเนินการ', 'in_progress' => 'กำลังดำเนินการ', 'completed' => 'เสร็จสิ้น', 'rejected' => 'ปฏิเสธ'];
                                        echo $status_labels[$request['status']] ?? $request['status']; 
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo safeFormatThaiDateTime($request['created_at'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <button onclick="showUpdateModal(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['status'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($request['admin_notes'] ?? '', ENT_QUOTES); ?>')"
                                                class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            📝 อัพเดท
                                        </button>
                                        <button onclick="showDetailModal(<?php echo $request['id']; ?>)"
                                                class="bg-green-100 text-green-600 hover:bg-green-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            👁️ ดู
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $request['id']; ?>)"
                                                class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            🗑️ ลบ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            แสดงรายการ <?php echo number_format(($page - 1) * $per_page + 1); ?> - <?php echo number_format(min($page * $per_page, $total_records)); ?> 
                            จากทั้งหมด <?php echo number_format($total_records); ?> รายการ
                        </div>
                        <div class="flex space-x-2">
                            <?php if ($pagination['has_prev']): ?>
                            <a href="?page=<?php echo $pagination['prev_page']; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="bg-white border border-gray-300 text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                                ← ก่อนหน้า
                            </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="<?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-500 hover:text-gray-700'; ?> px-4 py-2 rounded-lg transition duration-200">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?php echo $pagination['next_page']; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="bg-white border border-gray-300 text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                                ถัดไป →
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Update Status Modal -->
    <div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">📝 อัพเดทสถานะ</h3>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="request_id" id="updateRequestId">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                            <select name="status" id="updateStatus" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="pending">รอดำเนินการ</option>
                                <option value="in_progress">กำลังดำเนินการ</option>
                                <option value="completed">เสร็จสิ้น</option>
                                <option value="rejected">ปฏิเสธ</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                            <textarea name="admin_notes" id="updateNotes" rows="4" 
                                     class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                     placeholder="หมายเหตุสำหรับการอัพเดทสถานะ..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeUpdateModal()" 
                                class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 rounded-xl transition duration-200">
                            ยกเลิก
                        </button>
                        <button type="submit" 
                                class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-xl transition duration-200">
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">👁️ รายละเอียดคำขอ</h3>
                </div>
                <div class="p-6" id="detailContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="p-6 border-t border-gray-200">
                    <button onclick="closeDetailModal()" 
                            class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 rounded-xl transition duration-200">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function showUpdateModal(requestId, currentStatus, currentNotes) {
            document.getElementById('updateRequestId').value = requestId;
            document.getElementById('updateStatus').value = currentStatus;
            document.getElementById('updateNotes').value = currentNotes || '';
            document.getElementById('updateModal').classList.remove('hidden');
        }

        function closeUpdateModal() {
            document.getElementById('updateModal').classList.add('hidden');
        }

        function showDetailModal(requestId) {
            // Load request details via AJAX
            fetch(`get_ita_detail.php?id=${requestId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('detailContent').innerHTML = html;
                    document.getElementById('detailModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('ไม่สามารถโหลดรายละเอียดได้');
                });
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        function confirmDelete(requestId) {
            if (confirm('คุณต้องการลบคำขอนี้หรือไม่?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="request_id" value="${requestId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.id === 'updateModal') {
                closeUpdateModal();
            }
            if (e.target.id === 'detailModal') {
                closeDetailModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUpdateModal();
                closeDetailModal();
            }
        });

        console.log('🔧 ITA Management system loaded successfully!');
    </script>
</body>
</html>