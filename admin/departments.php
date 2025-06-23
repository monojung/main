<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require admin role
requireAdmin('../login.php');

$page_title = "จัดการแผนก";

// Handle actions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        if ($action === 'add') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $manager_id = $_POST['manager_id'] ? (int)$_POST['manager_id'] : null;
            
            if (empty($name)) {
                $error = 'กรุณาใส่ชื่อแผนก';
            } else {
                // Check if department name already exists
                $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
                $stmt->execute(array($name));
                if ($stmt->fetch()) {
                    $error = 'ชื่อแผนกนี้มีอยู่ในระบบแล้ว';
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO departments (name, description, manager_id, is_active, created_at) 
                        VALUES (?, ?, ?, 1, NOW())
                    ");
                    
                    if ($stmt->execute(array($name, $description, $manager_id))) {
                        $dept_id = $conn->lastInsertId();
                        logActivity($conn, $_SESSION['user_id'], 'department_created', 'departments', $dept_id, null, array(
                            'name' => $name
                        ));
                        $message = "เพิ่มแผนกเรียบร้อยแล้ว";
                    } else {
                        $error = "เกิดข้อผิดพลาดในการบันทึก";
                    }
                }
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $name = sanitizeInput($_POST['name'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $manager_id = $_POST['manager_id'] ? (int)$_POST['manager_id'] : null;
            
            if (empty($name)) {
                $error = 'กรุณาใส่ชื่อแผนก';
            } else {
                // Get old data
                $stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
                $stmt->execute(array($id));
                $old_data = $stmt->fetch();
                
                if ($old_data) {
                    // Check if department name already exists (except current)
                    $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ? AND id != ?");
                    $stmt->execute(array($name, $id));
                    if ($stmt->fetch()) {
                        $error = 'ชื่อแผนกนี้มีอยู่ในระบบแล้ว';
                    } else {
                        $stmt = $conn->prepare("
                            UPDATE departments 
                            SET name = ?, description = ?, manager_id = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        
                        if ($stmt->execute(array($name, $description, $manager_id, $id))) {
                            logActivity($conn, $_SESSION['user_id'], 'department_updated', 'departments', $id, array(
                                'name' => $old_data['name']
                            ), array(
                                'name' => $name
                            ));
                            $message = "อัปเดตแผนกเรียบร้อยแล้ว";
                        } else {
                            $error = "เกิดข้อผิดพลาดในการแก้ไข";
                        }
                    }
                } else {
                    $error = "ไม่พบแผนกนี้";
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            
            // Get department info
            $stmt = $conn->prepare("SELECT name FROM departments WHERE id = ?");
            $stmt->execute(array($id));
            $dept_info = $stmt->fetch();
            
            if ($dept_info) {
                // Check if there are users in this department
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE department_id = ?");
                $stmt->execute(array($id));
                $user_count = $stmt->fetch()['count'];
                
                if ($user_count > 0) {
                    $error = "ไม่สามารถลบแผนกได้ เนื่องจากมีผู้ใช้ {$user_count} คนในแผนกนี้";
                } else {
                    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
                    if ($stmt->execute(array($id))) {
                        logActivity($conn, $_SESSION['user_id'], 'department_deleted', 'departments', $id, array(
                            'name' => $dept_info['name']
                        ), null);
                        $message = "ลบแผนกเรียบร้อยแล้ว";
                    } else {
                        $error = "เกิดข้อผิดพลาดในการลบ";
                    }
                }
            } else {
                $error = "ไม่พบแผนกนี้";
            }
        }
    } catch (Exception $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        logError($e->getMessage(), __FILE__, __LINE__);
    }
}

// Get departments with statistics
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Create departments table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            manager_id INT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_name (name),
            INDEX idx_manager (manager_id),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Get departments with manager info and user count
    $departments_query = "
        SELECT d.*, 
               CONCAT(COALESCE(m.first_name, ''), ' ', COALESCE(m.last_name, '')) as manager_name,
               (SELECT COUNT(*) FROM users WHERE department_id = d.id AND is_active = 1) as user_count
        FROM departments d 
        LEFT JOIN users m ON d.manager_id = m.id 
        WHERE d.is_active = 1
        ORDER BY d.name
    ";
    $stmt = $conn->prepare($departments_query);
    $stmt->execute();
    $departments = $stmt->fetchAll();
    
    // Get users for manager selection (only active users)
    $users_query = "
        SELECT id, CONCAT(first_name, ' ', last_name) as full_name, role
        FROM users 
        WHERE is_active = 1 
        ORDER BY first_name, last_name
    ";
    $stmt = $conn->prepare($users_query);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "เกิดข้อผิดพลาดในการโหลดข้อมูล: " . $e->getMessage();
    logError($e->getMessage(), __FILE__, __LINE__);
    $departments = array();
    $users = array();
}
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
                    <span>สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
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
                    <a href="patients.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👥 ข้อมูลผู้ป่วย
                    </a>
                    <a href="visits.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        🏥 การรักษา
                    </a>
                    <a href="doctors.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-50 rounded">
                        👨‍⚕️ จัดการแพทย์
                    </a>
                    <a href="departments.php" class="block py-2 px-4 text-blue-600 bg-blue-50 rounded font-medium">
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
                <h2 class="text-3xl font-bold text-gray-800">จัดการแผนก</h2>
                <p class="text-gray-600">จัดการข้อมูลแผนกต่างๆ ในโรงพยาบาล</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-blue-600 mr-4">🏥</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo count($departments); ?></h3>
                            <p class="text-gray-600">แผนกทั้งหมด</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-green-600 mr-4">👨‍💼</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">
                                <?php echo count(array_filter($departments, function($d) { return !empty(trim($d['manager_name'])); })); ?>
                            </h3>
                            <p class="text-gray-600">แผนกที่มีหัวหน้า</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-3xl text-purple-600 mr-4">👥</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">
                                <?php echo array_sum(array_column($departments, 'user_count')); ?>
                            </h3>
                            <p class="text-gray-600">ผู้ใช้ทั้งหมด</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="flex justify-between items-center mb-6">
                <button onclick="openAddModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    ➕ เพิ่มแผนกใหม่
                </button>
            </div>

            <!-- Departments Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อแผนก</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">คำอธิบาย</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หัวหน้าแผนก</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จำนวนผู้ใช้</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($departments)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-4xl mb-2">🏥</div>
                                    ไม่มีข้อมูลแผนก
                                    <div class="mt-4">
                                        <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                            เพิ่มแผนกแรก
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($departments as $dept): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($dept['description'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty(trim($dept['manager_name']))): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        👨‍💼 <?php echo htmlspecialchars(trim($dept['manager_name'])); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-gray-400 text-sm">ไม่มีหัวหน้า</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo $dept['user_count']; ?> คน
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo formatThaiDate($dept['created_at']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editDepartment(<?php echo htmlspecialchars(json_encode($dept)); ?>)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            แก้ไข
                                        </button>
                                        <button onclick="deleteDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['name'], ENT_QUOTES); ?>', <?php echo $dept['user_count']; ?>)" 
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
            </div>
        </main>
    </div>

    <!-- Add/Edit Department Modal -->
    <div id="departmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <form method="POST" id="departmentForm">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900" id="modalTitle">เพิ่มแผนกใหม่</h3>
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
                        
                        <div>
                            <label for="modalName" class="block text-sm font-medium text-gray-700 mb-2">ชื่อแผนก *</label>
                            <input type="text" name="name" id="modalName" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="เช่น แผนกอายุรกรรม">
                        </div>
                        
                        <div>
                            <label for="modalDescription" class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย</label>
                            <textarea name="description" id="modalDescription" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="คำอธิบายเกี่ยวกับแผนก"></textarea>
                        </div>
                        
                        <div>
                            <label for="modalManager" class="block text-sm font-medium text-gray-700 mb-2">หัวหน้าแผนก</label>
                            <select name="manager_id" id="modalManager" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">เลือกหัวหน้าแผนก</option>
                                <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['full_name'] . ' (' . $user['role'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
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
            document.getElementById('modalTitle').textContent = 'เพิ่มแผนกใหม่';
            document.getElementById('modalAction').value = 'add';
            document.getElementById('modalId').value = '';
            document.getElementById('departmentForm').reset();
            document.getElementById('departmentModal').classList.remove('hidden');
        }

        function editDepartment(dept) {
            document.getElementById('modalTitle').textContent = 'แก้ไขแผนก';
            document.getElementById('modalAction').value = 'edit';
            document.getElementById('modalId').value = dept.id;
            document.getElementById('modalName').value = dept.name;
            document.getElementById('modalDescription').value = dept.description || '';
            document.getElementById('modalManager').value = dept.manager_id || '';
            document.getElementById('departmentModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('departmentModal').classList.add('hidden');
        }

        function deleteDepartment(id, name, userCount) {
            if (userCount > 0) {
                alert(`ไม่สามารถลบแผนก "${name}" ได้ เนื่องจากมีผู้ใช้ ${userCount} คนในแผนกนี้`);
                return;
            }
            
            if (confirm(`ต้องการลบแผนก "${name}" หรือไม่?\n\nการดำเนินการนี้ไม่สามารถยกเลิกได้`)) {
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
        document.getElementById('departmentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Form validation
        document.getElementById('modalName').addEventListener('input', function() {
            const name = this.value.trim();
            if (name.length > 100) {
                this.setCustomValidity('ชื่อแผนกต้องไม่เกิน 100 ตัวอักษร');
            } else {
                this.setCustomValidity('');
            }
        });

        // Auto-resize textarea
        document.getElementById('modalDescription').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>
</body>
</html>