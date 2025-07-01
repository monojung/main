<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "ITA Dashboard - ระบบจัดการ MOPH ITA";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get ITA statistics
    $stats = [];
    
    // Count total documents
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ita_documents WHERE is_active = 1");
    $stmt->execute();
    $stats['total_documents'] = $stmt->fetch()['count'];
    
    // Count documents by status
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM ita_documents WHERE is_active = 1 GROUP BY status");
    $stmt->execute();
    $status_counts = $stmt->fetchAll();
    foreach ($status_counts as $row) {
        $stats['status_' . $row['status']] = $row['count'];
    }
    
    // Get recent activities
    $stmt = $conn->prepare("
        SELECT a.*, u.full_name as user_name, i.title as document_title
        FROM ita_activities a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN ita_documents i ON a.document_id = i.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
    
    // Get MOIT completion status
    $stmt = $conn->prepare("
        SELECT moit_number, 
               COUNT(*) as total_items,
               SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_items
        FROM ita_documents 
        WHERE is_active = 1 
        GROUP BY moit_number 
        ORDER BY moit_number
    ");
    $stmt->execute();
    $moit_status = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}

include 'includes/admin_header.php';
?>

<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-gray-900">ITA Management System</h1>
                        <p class="text-sm text-gray-600">ระบบจัดการ MOPH ITA - โรงพยาบาลทุ่งหัวช้าง</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            <i class="fas fa-plus mr-2"></i>เพิ่มเอกสาร
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <img class="h-8 w-8 rounded-full" src="https://via.placeholder.com/32" alt="User">
                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-alt text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">เอกสารทั้งหมด</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_documents'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">เสร็จสิ้น</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['status_completed'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">กำลังดำเนินการ</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['status_in_progress'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">ยังไม่เริ่ม</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['status_pending'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- MOIT Progress -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">ความคืบหน้า MOIT (22 เกณฑ์)</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($moit_status as $moit): ?>
                            <?php 
                                $progress = $moit['total_items'] > 0 ? ($moit['completed_items'] / $moit['total_items']) * 100 : 0;
                                $moit_name = getMoitName($moit['moit_number']);
                            ?>
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex justify-between text-sm">
                                        <span class="font-medium text-gray-900">MOIT <?php echo $moit['moit_number']; ?>: <?php echo $moit_name; ?></span>
                                        <span class="text-gray-600"><?php echo $moit['completed_items']; ?>/<?php echo $moit['total_items']; ?></span>
                                    </div>
                                    <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                             style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                </div>
                                <div class="ml-4 text-sm font-medium text-gray-900">
                                    <?php echo number_format($progress, 1); ?>%
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">กิจกรรมล่าสุด</h3>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <?php foreach ($recent_activities as $index => $activity): ?>
                            <li>
                                <div class="relative pb-8">
                                    <?php if ($index < count($recent_activities) - 1): ?>
                                    <span class="absolute top-4 left-2 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <?php endif; ?>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-4 w-4 bg-blue-500 rounded-full flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-circle text-xs text-white"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($activity['user_name']); ?></span>
                                                    <?php echo htmlspecialchars($activity['action']); ?>
                                                    <?php if ($activity['document_title']): ?>
                                                    <span class="font-medium"><?php echo htmlspecialchars($activity['document_title']); ?></span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">การดำเนินการด่วน</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="ita_documents.php" class="group relative rounded-lg p-6 bg-gray-50 hover:bg-gray-100 transition duration-300">
                            <div>
                                <span class="rounded-lg inline-flex p-3 bg-blue-600 text-white group-hover:bg-blue-700">
                                    <i class="fas fa-file-alt"></i>
                                </span>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">จัดการเอกสาร</h3>
                                <p class="mt-2 text-sm text-gray-500">เพิ่ม แก้ไข และจัดการเอกสาร ITA</p>
                            </div>
                        </a>

                        <a href="ita_reports.php" class="group relative rounded-lg p-6 bg-gray-50 hover:bg-gray-100 transition duration-300">
                            <div>
                                <span class="rounded-lg inline-flex p-3 bg-green-600 text-white group-hover:bg-green-700">
                                    <i class="fas fa-chart-bar"></i>
                                </span>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">รายงาน</h3>
                                <p class="mt-2 text-sm text-gray-500">ดูรายงานความคืบหน้าและสถิติ</p>
                            </div>
                        </a>

                        <a href="ita_calendar.php" class="group relative rounded-lg p-6 bg-gray-50 hover:bg-gray-100 transition duration-300">
                            <div>
                                <span class="rounded-lg inline-flex p-3 bg-yellow-600 text-white group-hover:bg-yellow-700">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">ปCalendar</h3>
                                <p class="mt-2 text-sm text-gray-500">จัดการกำหนดการและเดดไลน์</p>
                            </div>
                        </a>

                        <a href="ita_settings.php" class="group relative rounded-lg p-6 bg-gray-50 hover:bg-gray-100 transition duration-300">
                            <div>
                                <span class="rounded-lg inline-flex p-3 bg-purple-600 text-white group-hover:bg-purple-700">
                                    <i class="fas fa-cog"></i>
                                </span>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">ตั้งค่า</h3>
                                <p class="mt-2 text-sm text-gray-500">ตั้งค่าระบบและการแจ้งเตือน</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
function getMoitName($number) {
    $moit_names = [
        1 => 'ระบบเผยแพร่ข้อมูล',
        2 => 'ข้อมูลข่าวสารปัจจุบัน',
        3 => 'รายงานการจัดซื้อจัดจ้าง',
        4 => 'ระบบจัดซื้อจัดจ้าง',
        5 => 'สรุปผลการจัดซื้อรายเดือน',
        6 => 'นโยบายบริหารทรัพยากรบุคคล',
        7 => 'การประเมินผลการปฏิบัติราชการ',
        8 => 'การอบรมจริยธรรม',
        9 => 'แนวปฏิบัติการร้องเรียน',
        10 => 'สรุปผลการร้องเรียน',
        11 => 'การมีส่วนร่วม',
        12 => 'การป้องกันการรับสินบน',
        13 => 'จริยธรรมการจัดซื้อยา',
        14 => 'การใช้ทรัพย์สินราชการ',
        15 => 'แผนป้องกันทุจริต',
        16 => 'รายงานผลการป้องกันทุจริต',
        17 => 'ประเมินความเสี่ยงการทุจริต',
        18 => 'มาตรการป้องกันการทุจริต',
        19 => 'รายงานการปฏิบัติตามจริยธรรม',
        20 => 'การอบรมผลประโยชน์ทับซ้อน',
        21 => 'เจตจำนงสุจริต',
        22 => 'สิทธิมนุษยชนและศักดิ์ศรี'
    ];
    
    return $moit_names[$number] ?? 'ไม่ระบุ';
}
?>

<script>
// Auto refresh every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Add any initialization code here
});
</script>

<?php include 'includes/admin_footer.php'; ?>