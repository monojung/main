<?php
session_start();
require_once 'config.php';

// ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ฟังก์ชันสำหรับการจัดการแผนก
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'add':
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $manager_id = $_POST['manager_id'] ?? null;
            
            if (!empty($name)) {
                $stmt = $pdo->prepare("INSERT INTO departments (name, description, manager_id, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$name, $description, $manager_id]);
                $success_message = "เพิ่มแผนกเรียบร้อยแล้ว";
            } else {
                $error_message = "กรุณาใส่ชื่อแผนก";
            }
            break;
            
        case 'edit':
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $manager_id = $_POST['manager_id'] ?? null;
            
            if (!empty($name)) {
                $stmt = $pdo->prepare("UPDATE departments SET name = ?, description = ?, manager_id = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $description, $manager_id, $id]);
                $success_message = "อัปเดตแผนกเรียบร้อยแล้ว";
            } else {
                $error_message = "กรุณาใส่ชื่อแผนก";
            }
            break;
            
        case 'delete':
            $id = $_POST['id'];
            
            // ตรวจสอบว่ามีพนักงานในแผนกนี้หรือไม่
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE department_id = ?");
            $stmt->execute([$id]);
            $employee_count = $stmt->fetchColumn();
            
            if ($employee_count > 0) {
                $error_message = "ไม่สามารถลบแผนกได้ เนื่องจากมีพนักงานในแผนกนี้";
            } else {
                $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = "ลบแผนกเรียบร้อยแล้ว";
            }
            break;
    }
}

// ดึงข้อมูลแผนกทั้งหมด
$departments_query = "
    SELECT d.*, 
           CONCAT(e.first_name, ' ', e.last_name) as manager_name,
           (SELECT COUNT(*) FROM employees WHERE department_id = d.id) as employee_count
    FROM departments d 
    LEFT JOIN employees e ON d.manager_id = e.id 
    ORDER BY d.name
";
$departments = $pdo->query($departments_query)->fetchAll();

// ดึงข้อมูลพนักงานสำหรับเลือกเป็นหัวหน้าแผนก
$employees = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM employees ORDER BY first_name, last_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการแผนก - ระบบจัดการองค์กร</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .content-wrapper {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }
        .badge {
            font-size: 0.85em;
        }
        .modal-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-building me-2"></i>
                        ระบบจัดการ
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link text-white mb-2" href="dashboard.php">
                            <i class="fas fa-home me-2"></i> หน้าแรก
                        </a>
                        <a class="nav-link text-white mb-2" href="employees.php">
                            <i class="fas fa-users me-2"></i> จัดการพนักงาน
                        </a>
                        <a class="nav-link text-white mb-2 active bg-white bg-opacity-25 rounded" href="departments.php">
                            <i class="fas fa-building me-2"></i> จัดการแผนก
                        </a>
                        <a class="nav-link text-white mb-2" href="positions.php">
                            <i class="fas fa-briefcase me-2"></i> จัดการตำแหน่ง
                        </a>
                        <a class="nav-link text-white" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content-wrapper">
                <div class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-1">จัดการแผนก</h2>
                            <p class="text-muted">จัดการข้อมูลแผนกต่างๆ ในองค์กร</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                            <i class="fas fa-plus me-2"></i>เพิ่มแผนกใหม่
                        </button>
                    </div>

                    <!-- Alert Messages -->
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-2x mb-3"></i>
                                    <h3><?php echo count($departments); ?></h3>
                                    <p class="mb-0">จำนวนแผนกทั้งหมด</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-tie fa-2x mb-3"></i>
                                    <h3><?php echo count(array_filter($departments, function($d) { return !empty($d['manager_id']); })); ?></h3>
                                    <p class="mb-0">แผนกที่มีหัวหน้า</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <h3><?php echo array_sum(array_column($departments, 'employee_count')); ?></h3>
                                    <p class="mb-0">พนักงานทั้งหมด</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Departments Table -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>รายการแผนก
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ชื่อแผนก</th>
                                            <th>คำอธิบาย</th>
                                            <th>หัวหน้าแผนก</th>
                                            <th>จำนวนพนักงาน</th>
                                            <th>วันที่สร้าง</th>
                                            <th>การจัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departments as $dept): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($dept['name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($dept['description'] ?: '-'); ?>
                                            </td>
                                            <td>
                                                <?php if ($dept['manager_name']): ?>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-user-tie me-1"></i>
                                                        <?php echo htmlspecialchars($dept['manager_name']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">ไม่มีหัวหน้า</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $dept['employee_count']; ?> คน
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($dept['created_at'])); ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                        onclick="editDepartment(<?php echo htmlspecialchars(json_encode($dept)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['name']); ?>', <?php echo $dept['employee_count']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>เพิ่มแผนกใหม่
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="addName" class="form-label">ชื่อแผนก *</label>
                            <input type="text" class="form-control" id="addName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="addDescription" class="form-label">คำอธิบาย</label>
                            <textarea class="form-control" id="addDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="addManager" class="form-label">หัวหน้าแผนก</label>
                            <select class="form-select" id="addManager" name="manager_id">
                                <option value="">เลือกหัวหน้าแผนก</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>แก้ไขแผนก
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">ชื่อแผนก *</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">คำอธิบาย</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editManager" class="form-label">หัวหน้าแผนก</label>
                            <select class="form-select" id="editManager" name="manager_id">
                                <option value="">เลือกหัวหน้าแผนก</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>ยืนยันการลบ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">ลบแผนก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function editDepartment(dept) {
            document.getElementById('editId').value = dept.id;
            document.getElementById('editName').value = dept.name;
            document.getElementById('editDescription').value = dept.description || '';
            document.getElementById('editManager').value = dept.manager_id || '';
            
            new bootstrap.Modal(document.getElementById('editDepartmentModal')).show();
        }

        function deleteDepartment(id, name, employeeCount) {
            const message = employeeCount > 0 
                ? `ไม่สามารถลบแผนก "${name}" ได้ เนื่องจากมีพนักงาน ${employeeCount} คนในแผนกนี้`
                : `คุณแน่ใจหรือไม่ที่จะลบแผนก "${name}"?`;
            
            document.getElementById('deleteMessage').textContent = message;
            document.getElementById('deleteId').value = id;
            
            // ซ่อน/แสดงปุ่มลบตามจำนวนพนักงาน
            const deleteButton = document.querySelector('#deleteModal .btn-danger');
            const form = deleteButton.closest('form');
            
            if (employeeCount > 0) {
                form.style.display = 'none';
            } else {
                form.style.display = 'inline';
            }
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>