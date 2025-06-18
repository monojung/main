<?php 
$page_title = "นัดหมายแพทย์";
require_once 'config/database.php';

// Handle form submission
$success_message = '';
$error_message = '';

if ($_POST) {
    // Validate form data
    $required_fields = ['name', 'phone', 'department', 'date', 'time'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (empty($missing_fields)) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Sanitize input data
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'phone' => sanitizeInput($_POST['phone']),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'id_card' => sanitizeInput($_POST['id_card'] ?? ''),
                'age' => (int)($_POST['age'] ?? 0),
                'department' => sanitizeInput($_POST['department']),
                'date' => sanitizeInput($_POST['date']),
                'time' => sanitizeInput($_POST['time']),
                'symptoms' => sanitizeInput($_POST['symptoms'] ?? '')
            ];
            
            // Validate Thai ID card if provided
            if (!empty($data['id_card']) && !validateThaiID($data['id_card'])) {
                $error_message = 'เลขบัตรประชาชนไม่ถูกต้อง';
            } else {
                // Get department info
                $stmt = $conn->prepare("SELECT * FROM departments WHERE code = ? AND is_active = 1");
                $stmt->execute([$data['department']]);
                $department = $stmt->fetch();
                
                if (!$department) {
                    $error_message = 'ไม่พบแผนกที่เลือก';
                } else {
                    // Check if date is not in the past
                    if (strtotime($data['date']) < strtotime(date('Y-m-d'))) {
                        $error_message = 'ไม่สามารถนัดหมายย้อนหลังได้';
                    } else {
                        // Check if time slot is available
                        $stmt = $conn->prepare("
                            SELECT COUNT(*) as count 
                            FROM appointments 
                            WHERE department_id = ? 
                            AND appointment_date = ? 
                            AND appointment_time = ? 
                            AND status NOT IN ('cancelled')
                        ");
                        $stmt->execute([$department['id'], $data['date'], $data['time']]);
                        $existing = $stmt->fetch();
                        
                        $max_per_slot = (int)getSetting($conn, 'appointment_slots_per_hour', 4);
                        
                        if ($existing['count'] >= $max_per_slot) {
                            $error_message = 'ช่วงเวลานี้เต็มแล้ว กรุณาเลือกเวลาอื่น';
                        } else {
                            // Generate appointment number
                            $appointment_number = generateAppointmentNumber($conn, $department['id']);
                            
                            if (!$appointment_number) {
                                $error_message = 'ไม่สามารถสร้างหมายเลขนัดหมายได้';
                            } else {
                                // Insert appointment
                                $stmt = $conn->prepare("
                                    INSERT INTO appointments (
                                        appointment_number, department_id, appointment_date, appointment_time,
                                        patient_name, patient_phone, patient_email, patient_id_card, patient_age,
                                        symptoms, status, created_at
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                                ");
                                
                                $result = $stmt->execute([
                                    $appointment_number,
                                    $department['id'],
                                    $data['date'],
                                    $data['time'],
                                    $data['name'],
                                    $data['phone'],
                                    $data['email'],
                                    $data['id_card'],
                                    $data['age'],
                                    $data['symptoms']
                                ]);
                                
                                if ($result) {
                                    $appointment_id = $conn->lastInsertId();
                                    
                                    // Log activity
                                    logActivity($conn, null, 'appointment_created', 'appointments', $appointment_id, null, [
                                        'appointment_number' => $appointment_number,
                                        'patient_name' => $data['name'],
                                        'department' => $department['name']
                                    ]);
                                    
                                    $success_message = "การนัดหมายของคุณได้รับการบันทึกเรียบร้อยแล้ว<br>" .
                                                     "หมายเลขนัดหมาย: <strong>$appointment_number</strong><br>" .
                                                     "เราจะติดต่อกลับภายใน 24 ชั่วโมง";
                                    
                                    // Clear form data on success
                                    $_POST = [];
                                } else {
                                    $error_message = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $error_message = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
            logError($e->getMessage(), __FILE__, __LINE__);
        }
    } else {
        $error_message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
}

// Get departments for dropdown
try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM departments WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $departments = $stmt->fetchAll();
} catch (Exception $e) {
    $departments = [];
    logError($e->getMessage(), __FILE__, __LINE__);
}

include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-purple-600 to-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">นัดหมายแพทย์</h1>
            <p class="text-xl max-w-2xl mx-auto">จองคิวล่วงหน้า สะดวก รวดเร็ว</p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                
                <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-8">
                    <div class="flex items-center">
                        <span class="text-2xl mr-2">✅</span>
                        <span><?php echo $success_message; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8">
                    <div class="flex items-center">
                        <span class="text-2xl mr-2">❌</span>
                        <span><?php echo $error_message; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Appointment Form -->
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">📅 ฟอร์มนัดหมาย</h2>
                        
                        <form method="POST" class="space-y-6">
                            <!-- Personal Information -->
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อ-นามสกุล *</label>
                                    <input type="text" id="name" name="name" required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">เบอร์โทรศัพท์ *</label>
                                    <input type="tel" id="phone" name="phone" required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="id_card" class="block text-sm font-medium text-gray-700 mb-2">เลขบัตรประชาชน</label>
                                    <input type="text" id="id_card" name="id_card" maxlength="13"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo htmlspecialchars($_POST['id_card'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label for="age" class="block text-sm font-medium text-gray-700 mb-2">อายุ</label>
                                    <input type="number" id="age" name="age" min="0" max="120"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>">
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">อีเมล (ไม่บังคับ)</label>
                                <input type="email" id="email" name="email" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>

                            <!-- Department Selection -->
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">แผนกที่ต้องการ *</label>
                                <select id="department" name="department" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">เลือกแผนก</option>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['code']; ?>" 
                                            <?php echo ($_POST['department'] ?? '') == $dept['code'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Date and Time -->
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">วันที่ต้องการ *</label>
                                    <input type="date" id="date" name="date" required 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                           max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label for="time" class="block text-sm font-medium text-gray-700 mb-2">เวลาที่ต้องการ *</label>
                                    <select id="time" name="time" required 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">เลือกเวลา</option>
                                        <?php foreach ($appointment_times as $time): ?>
                                        <option value="<?php echo $time; ?>" 
                                                <?php echo ($_POST['time'] ?? '') == $time ? 'selected' : ''; ?>>
                                            <?php echo $time; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div>
                                <label for="symptoms" class="block text-sm font-medium text-gray-700 mb-2">อาการเบื้องต้น / หมายเหตุ</label>
                                <textarea id="symptoms" name="symptoms" rows="4" 
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="โปรดระบุอาการหรือหมายเหตุเพิ่มเติม"><?php echo htmlspecialchars($_POST['symptoms'] ?? ''); ?></textarea>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" 
                                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                                📅 ส่งการนัดหมาย
                            </button>
                        </form>
                    </div>

                    <!-- Information Panel -->
                    <div class="space-y-6">
                        <!-- Operating Hours -->
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">🕒 เวลาทำการ</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium">จันทร์ - ศุกร์:</span>
                                    <span>08:00 - 16:30</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">เสาร์ - อาทิตย์:</span>
                                    <span>08:00 - 12:00</span>
                                </div>
                                <div class="flex justify-between text-red-600">
                                    <span class="font-medium">ฉุกเฉิน:</span>
                                    <span>24 ชั่วโมง</span>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="bg-yellow-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">⚠️ หมายเหตุสำคัญ</h3>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2"></span>
                                    <span>การนัดหมายต้องทำล่วงหน้าอย่างน้อย 1 วัน</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2"></span>
                                    <span>โปรดมาถึงก่อนเวลานัดหมาย 30 นาที</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2"></span>
                                    <span>นำบัตรประชาชนมาด้วยทุกครั้ง</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2"></span>
                                    <span>หากไม่สามารถมาตามนัดได้ กรุณาโทรแจ้งล่วงหน้า</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-green-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">📞 ติดต่อสอบถาม</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center space-x-3">
                                    <span class="text-green-600">📞</span>
                                    <span>053-580-xxx (แผนกผู้ป่วยนอก)</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-green-600">📱</span>
                                    <span>Line ID: @thchospital</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-green-600">📧</span>
                                    <span>appointment@thchospital.go.th</span>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="bg-red-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">🚨 ฉุกเฉิน</h3>
                            <div class="space-y-2 text-sm">
                                <p class="font-medium text-red-600">กรณีฉุกเฉิน ไม่ต้องนัดหมาย</p>
                                <div class="flex items-center space-x-3">
                                    <span class="text-red-600">📞</span>
                                    <span class="font-semibold">053-580-xxx (ฉุกเฉิน 24 ชม.)</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-red-600">🚑</span>
                                    <span>บริการรถพยาบาลฉุกเฉิน</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">คำถามที่พบบ่อย</h2>
            <div class="max-w-3xl mx-auto space-y-6">
                <?php
                $faqs = [
                    [
                        'question' => 'ต้องนัดหมายล่วงหน้ากี่วัน?',
                        'answer' => 'ควรนัดหมายล่วงหน้าอย่างน้อย 1 วัน สำหรับแผนกพิเศษอาจต้องนัดล่วงหน้า 3-7 วัน'
                    ],
                    [
                        'question' => 'สามารถยกเลิกหรือเปลี่ยนแปลงการนัดหมายได้หรือไม่?',
                        'answer' => 'สามารถยกเลิกหรือเปลี่ยนแปลงได้ โดยต้องแจ้งล่วงหน้าอย่างน้อย 4 ชั่วโมงก่อนเวลานัด'
                    ],
                    [
                        'question' => 'ต้องเตรียมเอกสารอะไรบ้าง?',
                        'answer' => 'นำบัตรประชาชน บัตรประกันสังคม/ข้าราชการ (ถ้ามี) และประวัติการรักษาครั้งก่อน (ถ้ามี)'
                    ],
                    [
                        'question' => 'มาก่อนเวลานัดกี่นาที?',
                        'answer' => 'ควรมาถึงก่อนเวลานัดหมายอย่างน้อย 30 นาที เพื่อลงทะเบียนและเตรียมตัว'
                    ]
                ];
                
                foreach($faqs as $index => $faq): ?>
                <div class="bg-white rounded-lg shadow-lg">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 hover:bg-gray-50 transition duration-300 faq-toggle" data-target="faq-<?php echo $index; ?>">
                        <div class="flex justify-between items-center">
                            <span><?php echo $faq['question']; ?></span>
                            <svg class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div id="faq-<?php echo $index; ?>" class="hidden px-6 pb-4">
                        <p class="text-gray-600"><?php echo $faq['answer']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<script>
// FAQ Toggle functionality
document.querySelectorAll('.faq-toggle').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const targetDiv = document.getElementById(targetId);
        const icon = this.querySelector('svg');
        
        if (targetDiv.classList.contains('hidden')) {
            targetDiv.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            targetDiv.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['name', 'phone', 'department', 'date', 'time'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (!input.value.trim()) {
            input.classList.add('border-red-500');
            isValid = false;
        } else {
            input.classList.remove('border-red-500');
        }
    });
    
    // Validate ID card if provided
    const idCard = document.querySelector('[name="id_card"]').value.trim();
    if (idCard && !validateThaiID(idCard)) {
        document.querySelector('[name="id_card"]').classList.add('border-red-500');
        alert('เลขบัตรประชาชนไม่ถูกต้อง');
        e.preventDefault();
        return false;
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
    }
});

// Phone number formatting
document.querySelector('[name="phone"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        e.target.value = value;
    }
});

// ID Card formatting and validation
document.querySelector('[name="id_card"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 13) {
        e.target.value = value;
    }
    
    // Real-time validation
    if (value.length === 13) {
        if (validateThaiID(value)) {
            e.target.classList.remove('border-red-500');
            e.target.classList.add('border-green-500');
        } else {
            e.target.classList.remove('border-green-500');
            e.target.classList.add('border-red-500');
        }
    } else {
        e.target.classList.remove('border-red-500', 'border-green-500');
    }
});

// Thai ID validation function
function validateThaiID(id) {
    if (id.length !== 13) return false;
    if (!/^\d{13}$/.test(id)) return false;
    
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        sum += parseInt(id.charAt(i)) * (13 - i);
    }
    
    let check = (11 - (sum % 11)) % 10;
    return check === parseInt(id.charAt(12));
}

// Auto-focus on first field
document.querySelector('[name="name"]').focus();
</script>

<?php include 'includes/footer.php'; ?><?php 
$page_title = "นัดหมายแพทย์";
include 'includes/header.php'; 

// Handle form submission
$success_message = '';
$error_message = '';

if ($_POST) {
    // Validate form data
    $required_fields = ['name', 'phone', 'department', 'date', 'time'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (empty($missing_fields)) {
        // Process the appointment (in real app, save to database)
        $success_message = 'การนัดหมายของคุณได้รับการบันทึกเรียบร้อยแล้ว เราจะติดต่อกลับภายใน 24 ชั่วโมง';
    } else {
        $error_message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
}
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-purple-600 to-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">นัดหมายแพทย์</h1>
            <p class="text-xl max-w-2xl mx-auto">จองคิวล่วงหน้า สะดวก รวดเร็ว</p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                
                <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-8">
                    <div class="flex items-center">
                        <span class="text-2xl mr-2">✅</span>
                        <span><?php echo $success_message; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8">
                    <div class="flex items-center">
                        <span class="text-2xl mr-2">❌</span>
                        <span><?php echo $error_message; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Appointment Form -->
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">📅 ฟอร์มนัดหมาย</h2>
                        
                        <form method="POST" class="space-y-6">
                            <!-- Personal Information -->
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อ-นามสกุล *</label>
                                    <input type="text" id="name" name="name" required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo $_POST['name'] ?? ''; ?>">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">เบอร์โทรศัพท์ *</label>
                                    <input type="tel" id="phone" name="phone" required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo $_POST['phone'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="id_card" class="block text-sm font-medium text-gray-700 mb-2">เลขบัตรประชาชน</label>
                                    <input type="text" id="id_card" name="id_card" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo $_POST['id_card'] ?? ''; ?>">
                                </div>
                                <div>
                                    <label for="age" class="block text-sm font-medium text-gray-700 mb-2">อายุ</label>
                                    <input type="number" id="age" name="age" min="0" max="120"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo $_POST['age'] ?? ''; ?>">
                                </div>
                            </div>

                            <!-- Department Selection -->
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">แผนกที่ต้องการ *</label>
                                <select id="department" name="department" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">เลือกแผนก</option>
                                    <option value="general" <?php echo ($_POST['department'] ?? '') == 'general' ? 'selected' : ''; ?>>แพทย์ทั่วไป</option>
                                    <option value="pediatric" <?php echo ($_POST['department'] ?? '') == 'pediatric' ? 'selected' : ''; ?>>กุมารเวชกรรม</option>
                                    <option value="obstetric" <?php echo ($_POST['department'] ?? '') == 'obstetric' ? 'selected' : ''; ?>>สูติ-นรีเวชกรรม</option>
                                    <option value="surgery" <?php echo ($_POST['department'] ?? '') == 'surgery' ? 'selected' : ''; ?>>ศัลยกรรม</option>
                                    <option value="orthopedic" <?php echo ($_POST['department'] ?? '') == 'orthopedic' ? 'selected' : ''; ?>>ออร์โธปิดิกส์</option>
                                    <option value="dental" <?php echo ($_POST['department'] ?? '') == 'dental' ? 'selected' : ''; ?>>ทันตกรรม</option>
                                </select>
                            </div>

                            <!-- Date and Time -->
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">วันที่ต้องการ *</label>
                                    <input type="date" id="date" name="date" required 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo $_POST['date'] ?? ''; ?>">
                                </div>
                                <div>
                                    <label for="time" class="block text-sm font-medium text-gray-700 mb-2">เวลาที่ต้องการ *</label>
                                    <select id="time" name="time" required 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">เลือกเวลา</option>
                                        <option value="08:00" <?php echo ($_POST['time'] ?? '') == '08:00' ? 'selected' : ''; ?>>08:00</option>
                                        <option value="09:00" <?php echo ($_POST['time'] ?? '') == '09:00' ? 'selected' : ''; ?>>09:00</option>
                                        <option value="10:00" <?php echo ($_POST['time'] ?? '') == '10:00' ? 'selected' : ''; ?>>10:00</option>
                                        <option value="11:00" <?php echo ($_POST['time'] ?? '') == '11:00' ? 'selected' : ''; ?>>11:00</option>
                                        <option value="13:00" <?php echo ($_POST['time'] ?? '') == '13:00' ? 'selected' : ''; ?>>13:00</option>
                                        <option value="14:00" <?php echo ($_POST['time'] ?? '') == '14:00' ? 'selected' : ''; ?>>14:00</option>
                                        <option value="15:00" <?php echo ($_POST['time'] ?? '') == '15:00' ? 'selected' : ''; ?>>15:00</option>
                                        <option value="16:00" <?php echo ($_POST['time'] ?? '') == '16:00' ? 'selected' : ''; ?>>16:00</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div>
                                <label for="symptoms" class="block text-sm font-medium text-gray-700 mb-2">อาการเบื้องต้น / หมายเหตุ</label>
                                <textarea id="symptoms" name="symptoms" rows="4" 
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="โปรดระบุอาการหรือหมายเหตุเพิ่มเติม"><?php echo $_POST['symptoms'] ?? ''; ?></textarea>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" 
                                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                                📅 ส่งการนัดหมาย
                            </button>
                        </form>
                    </div>

                    <!-- Information Panel -->
                    <div class="space-y-6">
                        <!-- Operating Hours -->
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">🕒 เวลาทำการ</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium">จันทร์ - ศุกร์:</span>
                                    <span>08:00 - 16:30</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">เสาร์ - อาทิตย์:</span>
                                    <span>08:00 - 12:00</span>
                                </div>
                                <div class="flex justify-between text-red-600">
                                    <span class="font-medium">ฉุกเฉิน:</span>
                                    <span>24 ชั่วโมง</span>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="bg-yellow-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">⚠️ หมายเหตุสำคัญ</h3>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2"></span>
                                    <span>การนัดหมายต้องทำล่วงหน้าอย่างน้อย 1 วัน</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2"></span>
                                    <span>โปรดมาถึงก่อนเวลานัดหมาย 30 นาที</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2"></span>
                                    <span>นำบัตรประชาชนมาด้วยทุกครั้ง</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2"></span>
                                    <span>หากไม่สามารถมาตามนัดได้ กรุณาโทรแจ้งล่วงหน้า</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-green-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">📞 ติดต่อสอบถาม</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center space-x-3">
                                    <span class="text-green-600">📞</span>
                                    <span>053-580-xxx (แผนกผู้ป่วยนอก)</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-green-600">📱</span>
                                    <span>Line ID: @thchospital</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-green-600">📧</span>
                                    <span>appointment@thchospital.go.th</span>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="bg-red-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">🚨 ฉุกเฉิน</h3>
                            <div class="space-y-2 text-sm">
                                <p class="font-medium text-red-600">กรณีฉุกเฉิน ไม่ต้องนัดหมาย</p>
                                <div class="flex items-center space-x-3">
                                    <span class="text-red-600">📞</span>
                                    <span class="font-semibold">053-580-xxx (ฉุกเฉิน 24 ชม.)</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-red-600">🚑</span>
                                    <span>บริการรถพยาบาลฉุกเฉิน</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">คำถามที่พบบ่อย</h2>
            <div class="max-w-3xl mx-auto space-y-6">
                <?php
                $faqs = [
                    [
                        'question' => 'ต้องนัดหมายล่วงหน้ากี่วัน?',
                        'answer' => 'ควรนัดหมายล่วงหน้าอย่างน้อย 1 วัน สำหรับแผนกพิเศษอาจต้องนัดล่วงหน้า 3-7 วัน'
                    ],
                    [
                        'question' => 'สามารถยกเลิกหรือเปลี่ยนแปลงการนัดหมายได้หรือไม่?',
                        'answer' => 'สามารถยกเลิกหรือเปลี่ยนแปลงได้ โดยต้องแจ้งล่วงหน้าอย่างน้อย 4 ชั่วโมงก่อนเวลานัด'
                    ],
                    [
                        'question' => 'ต้องเตรียมเอกสารอะไรบ้าง?',
                        'answer' => 'นำบัตรประชาชน บัตรประกันสังคม/ข้าราชการ (ถ้ามี) และประวัติการรักษาครั้งก่อน (ถ้ามี)'
                    ],
                    [
                        'question' => 'มาก่อนเวลานัดกี่นาที?',
                        'answer' => 'ควรมาถึงก่อนเวลานัดหมายอย่างน้อย 30 นาที เพื่อลงทะเบียนและเตรียมตัว'
                    ]
                ];
                
                foreach($faqs as $index => $faq): ?>
                <div class="bg-white rounded-lg shadow-lg">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 hover:bg-gray-50 transition duration-300 faq-toggle" data-target="faq-<?php echo $index; ?>">
                        <div class="flex justify-between items-center">
                            <span><?php echo $faq['question']; ?></span>
                            <svg class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div id="faq-<?php echo $index; ?>" class="hidden px-6 pb-4">
                        <p class="text-gray-600"><?php echo $faq['answer']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<script>
// FAQ Toggle functionality
document.querySelectorAll('.faq-toggle').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const targetDiv = document.getElementById(targetId);
        const icon = this.querySelector('svg');
        
        if (targetDiv.classList.contains('hidden')) {
            targetDiv.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            targetDiv.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['name', 'phone', 'department', 'date', 'time'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (!input.value.trim()) {
            input.classList.add('border-red-500');
            isValid = false;
        } else {
            input.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
    }
});

// Phone number formatting
document.querySelector('[name="phone"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        e.target.value = value;
    }
});

// ID Card formatting
document.querySelector('[name="id_card"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 13) {
        e.target.value = value;
    }
});
</script>

<?php include 'includes/footer.php'; ?>