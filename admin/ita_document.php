<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "จัดการเอกสาร ITA";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_document':
                    $stmt = $conn->prepare("
                        INSERT INTO ita_documents (moit_number, title, description, file_path, status, created_by, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $_POST['moit_number'],
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['file_path'],
                        $_POST['status'],
                        $_SESSION['user_id']
                    ]);
                    $success_message = "เพิ่มเอกสารสำเร็จ";
                    break;
                    
                case 'update_document':
                    $stmt = $conn->prepare("
                        UPDATE ita_documents 
                        SET title = ?, description = ?, file_path = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['file_path'],
                        $_POST['status'],
                        $_POST['document_id']
                    ]);
                    $success_message = "อัปเดตเอกสารสำเร็จ";
                    break;
                    
                case 'delete_document':
                    $stmt = $conn->prepare("UPDATE ita_documents SET is_active = 0 WHERE id = ?");
                    $stmt->execute([$_POST['document_id']]);
                    $success_message = "ลบเอกสารสำเร็จ";
                    break;
            }
        }
    }
    
    // Get filters
    $moit_filter = $_GET['moit'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build query
    $where_conditions = ["is_active = 1"];
    $params = [];
    
    if ($moit_filter) {
        $where_conditions[] = "moit_number = ?";
        $params[] = $moit_filter;
    }
    
    if ($status_filter) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    if ($search) {
        $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get documents
    $stmt = $conn->prepare("
        SELECT d.*, u.full_name as created_by_name
        FROM ita_documents d
        LEFT JOIN users u ON d.created_by = u.id
        WHERE $where_clause
        ORDER BY d.moit_number, d.created_at DESC
    ");
    $stmt->execute($params);
    $documents = $stmt->fetchAll();
    
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
                    <a href="ita_dashboard.php" class="text-blue-600 hover:text-blue-800 mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">จัดการเอกสาร ITA</h1>
                        <p class="text-sm text-gray-600">จัดการเอกสารและไฟล์สำหรับเกณฑ์ MOIT ทั้ง 22 ข้อ</p>
                    </div>
                </div>
                <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-plus mr-2"></i>เพิ่มเอกสาร
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($success_message)): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">เกณฑ์ MOIT</label>
                        <select name="moit" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">ทั้งหมด</option>
                            <?php for ($i = 1; $i <= 22; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $moit_filter == $i ? 'selected' : ''; ?>>
                                MOIT <?php echo $i; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">ทั้งหมด</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                            <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                            <option value="needs_review" <?php echo $status_filter == 'needs_review' ? 'selected' : ''; ?>>ต้องตรวจสอบ</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ค้นหา</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="ชื่อเอกสารหรือรายละเอียด..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition duration-300">
                            <i class="fas fa-search mr-2"></i>ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เกณฑ์</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อเอกสาร</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้สร้าง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($documents as $doc): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">MOIT <?php echo $doc['moit_number']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo getMoitName($doc['moit_number']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></div>
                                <?php if ($doc['description']): ?>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($doc['description'], 0, 60)) . (strlen($doc['description']) > 60 ? '...' : ''); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status_colors = [
                                    'pending' => 'bg-gray-100 text-gray-800',
                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'needs_review' => 'bg-red-100 text-red-800'
                                ];
                                $status_names = [
                                    'pending' => 'รอดำเนินการ',
                                    'in_progress' => 'กำลังดำเนินการ',
                                    'completed' => 'เสร็จสิ้น',
                                    'needs_review' => 'ต้องตรวจสอบ'
                                ];
                                ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_colors[$doc['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $status_names[$doc['status']] ?? $doc['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($doc['created_by_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($doc['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <?php if ($doc['file_path']): ?>
                                    <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="editDocument(<?php echo htmlspecialchars(json_encode($doc)); ?>)" 
                                            class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteDocument(<?php echo $doc['id']; ?>)" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                ไม่พบเอกสารที่ตรงกับเงื่อนไขการค้นหา
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Document Modal -->
<div id="documentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">เพิ่มเอกสาร</h3>
            <form id="documentForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add_document">
                <input type="hidden" name="document_id" id="documentId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">เกณฑ์ MOIT *</label>
                        <select name="moit_number" id="moitNumber" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">เลือกเกณฑ์ MOIT</option>
                            <?php for ($i = 1; $i <= 22; $i++): ?>
                            <option value="<?php echo $i; ?>">MOIT <?php echo $i; ?>: <?php echo getMoitName($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ *</label>
                        <select name="status" id="documentStatus" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending">รอดำเนินการ</option>
                            <option value="in_progress">กำลังดำเนินการ</option>
                            <option value="completed">เสร็จสิ้น</option>
                            <option value="needs_review">ต้องตรวจสอบ</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อเอกสาร *</label>
                    <input type="text" name="title" id="documentTitle" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ชื่อเอกสารหรือรายการ">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">รายละเอียด</label>
                    <textarea name="description" id="documentDescription" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="รายละเอียดเพิ่มเติม"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ไฟล์หรือลิงก์</label>
                    <input type="text" name="file_path" id="filePath"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="URL ของไฟล์หรือหน้าเว็บ">
                    <p class="text-sm text-gray-500 mt-1">ใส่ URL ของไฟล์ หรือลิงก์ไปยังหน้าเว็บที่เกี่ยวข้อง</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition duration-300">
                        ยกเลิก
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition duration-300">
                        บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">ยืนยันการลบ</h3>
            <p class="text-sm text-gray-500 mt-2">คุณต้องการลบเอกสารนี้หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้</p>
            
            <form id="deleteForm" method="POST" class="mt-4">
                <input type="hidden" name="action" value="delete_document">
                <input type="hidden" name="document_id" id="deleteDocumentId">
                
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="closeDeleteModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition duration-300">
                        ยกเลิก
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition duration-300">
                        ลบ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มเอกสาร';
    document.getElementById('formAction').value = 'add_document';
    document.getElementById('documentForm').reset();
    document.getElementById('documentModal').classList.remove('hidden');
}

function editDocument(doc) {
    document.getElementById('modalTitle').textContent = 'แก้ไขเอกสาร';
    document.getElementById('formAction').value = 'update_document';
    document.getElementById('documentId').value = doc.id;
    document.getElementById('moitNumber').value = doc.moit_number;
    document.getElementById('documentTitle').value = doc.title;
    document.getElementById('documentDescription').value = doc.description || '';
    document.getElementById('filePath').value = doc.file_path || '';
    document.getElementById('documentStatus').value = doc.status;
    document.getElementById('documentModal').classList.remove('hidden');
}

function deleteDocument(id) {
    document.getElementById('deleteDocumentId').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('documentModal').classList.add('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modals when clicking outside
window.onclick = function(event) {
    const documentModal = document.getElementById('documentModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === documentModal) {
        closeModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
}
</script>

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

<?php include 'includes/admin_footer.php'; ?>