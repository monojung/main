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
                                                     "แผนก: <strong>{$department['name']}</strong><br>" .
                                                     "วันที่: <strong>" . formatThaiDate($data['date']) . "</strong><br>" .
                                                     "เวลา: <strong>{$data['time']}</strong><br>" .
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
                <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-8">
                    <div class="flex items-start">
                        <span class="text-2xl mr-3 mt-1">✅</span>
                        <div>
                            <h3 class="font-bold text-lg mb-2">การนัดหมายสำเร็จ!</h3>
                            <div><?php echo $success_message; ?></div>
                            <div class="mt-4 p-3 bg-green-50 rounded border">
                                <h4 class="font-semibold mb-2">📋 ข้อมูลสำคัญ:</h4>
                                <ul class="text-sm space-y-1">
                                    <li>• กรุณามาถึงก่อนเวลานัดหมาย 30 นาที</li>
                                    <li>• นำบัตรประชาชนมาด้วยทุกครั้ง</li>
                                    <li>• หากไม่สามารถมาได้ กรุณาโทรแจ้งล่วงหน้า</li>
                                    <li>• สอบถามเพิ่มเติม: 053-580-100</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-8">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">❌</span>
                        <div>
                            <h3 class="font-bold">เกิดข้อผิดพลาด</h3>
                            <p><?php echo $error_message; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Appointment Form -->
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">📅 ฟอร์มนัดหมาย</h2>
                        
                        <form method="POST" class="space-y-6" id="appointmentForm">
                            <!-- Personal Information -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold mb-4 text-gray-700">ข้อมูลส่วนตัว</h3>
                                
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                            ชื่อ-นามสกุล <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="name" name="name" required 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                               placeholder="กรอกชื่อและนามสกุล">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                            เบอร์โทรศัพท์ <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" id="phone" name="phone" required 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                               placeholder="08x-xxx-xxxx">
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="id_card" class="block text-sm font-medium text-gray-700 mb-2">
                                            เลขบัตรประชาชน
                                        </label>
                                        <input type="text" id="id_card" name="id_card" maxlength="13"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               value="<?php echo htmlspecialchars($_POST['id_card'] ?? ''); ?>"
                                               placeholder="x-xxxx-xxxxx-xx-x">
                                    </div>
                                    <div>
                                        <label for="age" class="block text-sm font-medium text-gray-700 mb-2">
                                            อายุ
                                        </label>
                                        <input type="number" id="age" name="age" min="0" max="120"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>"
                                               placeholder="ปี">
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        อีเมล (ไม่บังคับ)
                                    </label>
                                    <input type="email" id="email" name="email" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                           placeholder="example@email.com">
                                </div>
                            </div>

                            <!-- Appointment Details -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold mb-4 text-gray-700">รายละเอียดการนัดหมาย</h3>
                                
                                <!-- Department Selection -->
                                <div class="mb-4">
                                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                                        แผนกที่ต้องการ <span class="text-red-500">*</span>
                                    </label>
                                    <select id="department" name="department" required 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">เลือกแผนก</option>
                                        <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['code']; ?>" 
                                                <?php echo ($_POST['department'] ?? '') == $dept['code'] ? 'selected' : ''; ?>
                                                data-description="<?php echo htmlspecialchars($dept['description'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div id="department-info" class="mt-2 text-sm text-gray-600 hidden"></div>
                                </div>

                                <!-- Date and Time -->
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                                            วันที่ต้องการ <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" id="date" name="date" required 
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                               max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>">
                                    </div>
                                    <div>
                                        <label for="time" class="block text-sm font-medium text-gray-700 mb-2">
                                            เวลาที่ต้องการ <span class="text-red-500">*</span>
                                        </label>
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
                            </div>

                            <!-- Additional Information -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4 text-gray-700">ข้อมูลเพิ่มเติม</h3>
                                
                                <div>
                                    <label for="symptoms" class="block text-sm font-medium text-gray-700 mb-2">
                                        อาการเบื้องต้น / หมายเหตุ
                                    </label>
                                    <textarea id="symptoms" name="symptoms" rows="4" 
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                              placeholder="โปรดระบุอาการหรือหมายเหตุเพิ่มเติม (ไม่บังคับ)"><?php echo htmlspecialchars($_POST['symptoms'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-6">
                                <button type="submit" id="submitBtn"
                                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition duration-300 transform hover:scale-105">
                                    📅 ส่งการนัดหมาย
                                </button>
                                <p class="text-xs text-gray-500 text-center mt-2">
                                    กดส่งแล้ว ระบบจะส่ง SMS ยืนยันให้ภายใน 24 ชั่วโมง
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Information Panel -->
                    <div class="space-y-6">
                        <!-- Operating Hours -->
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">🕒 เวลาทำการ</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center p-2 bg-white rounded">
                                    <span class="font-medium">จันทร์ - ศุกร์:</span>
                                    <span class="text-blue-600 font-semibold">08:00 - 16:30</span>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-white rounded">
                                    <span class="font-medium">เสาร์ - อาทิตย์:</span>
                                    <span class="text-blue-600 font-semibold">08:00 - 12:00</span>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-200">
                                    <span class="font-medium text-red-700">ฉุกเฉิน:</span>
                                    <span class="text-red-600 font-semibold">24 ชั่วโมง</span>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-200">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">⚠️ หมายเหตุสำคัญ</h3>
                            <ul class="space-y-3 text-sm">
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span>การนัดหมายต้องทำล่วงหน้าอย่างน้อย 1 วัน</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span>โปรดมาถึงก่อนเวลานัดหมาย 30 นาที</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span>นำบัตรประชาชนมาด้วยทุกครั้ง</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span>หากไม่สามารถมาตามนัดได้ กรุณาโทรแจ้งล่วงหน้า</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <span class="w-2 h-2 bg-yellow-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span>ระบบจะส่ง SMS ยืนยันการนัดหมายให้</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">📞 ติดต่อสอบถาม</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center space-x-3 p-2 bg-white rounded">
                                    <span class="text-green-600 text-lg">📞</span>
                                    <div>
                                        <div class="font-medium">แผนกผู้ป่วยนอก</div>
                                        <div class="text-green-600">053-580-100</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-2 bg-white rounded">
                                    <span class="text-green-600 text-lg">📱</span>
                                    <div>
                                        <div class="font-medium">Line Official</div>
                                        <div class="text-green-600">@thchospital</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-2 bg-white rounded">
                                    <span class="text-green-600 text-lg">📧</span>
                                    <div>
                                        <div class="font-medium">อีเมล</div>
                                        <div class="text-green-600">appointment@thchospital.go.th</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="bg-red-50 rounded-lg p-6 border border-red-200">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">🚨 ฉุกเฉิน</h3>
                            <div class="space-y-3 text-sm">
                                <p class="font-medium text-red-700 mb-3">กรณีฉุกเฉิน ไม่ต้องนัดหมาย</p>
                                <div class="flex items-center space-x-3 p-3 bg-red-100 rounded border border-red-300">
                                    <span class="text-red-600 text-xl">📞</span>
                                    <div>
                                        <div class="font-bold text-red-700">053-580-999</div>
                                        <div class="text-red-600">ฉุกเฉิน 24 ชั่วโมง</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-2 bg-white rounded">
                                    <span class="text-red-600 text-lg">🚑</span>
                                    <div>
                                        <div class="font-medium">รถพยาบาลฉุกเฉิน</div>
                                        <div class="text-red-600">บริการ 24 ชั่วโมง</div>
                                    </div>
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
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">❓ คำถามที่พบบ่อย</h2>
            <div class="max-w-3xl mx-auto space-y-4">
                <?php
                $faqs = [
                    [
                        'question' => 'ต้องนัดหมายล่วงหน้ากี่วัน?',
                        'answer' => 'ควรนัดหมายล่วงหน้าอย่างน้อย 1 วัน สำหรับแผนกพิเศษ เช่น ออร์โธปิดิกส์ อาจต้องนัดล่วงหน้า 3-7 วัน'
                    ],
                    [
                        'question' => 'สามารถยกเลิกหรือเปลี่ยนแปลงการนัดหมายได้หรือไม่?',
                        'answer' => 'สามารถยกเลิกหรือเปลี่ยนแปลงได้ โดยต้องแจ้งล่วงหน้าอย่างน้อย 4 ชั่วโมงก่อนเวลานัด ติดต่อ 053-580-100'
                    ],
                    [
                        'question' => 'ต้องเตรียมเอกสารอะไรบ้าง?',
                        'answer' => 'นำบัตรประชาชน บัตรประกันสังคม/ข้าราชการ (ถ้ามี) และประวัติการรักษาครั้งก่อน (ถ้ามี)'
                    ],
                    [
                        'question' => 'มาก่อนเวลานัดกี่นาที?',
                        'answer' => 'ควรมาถึงก่อนเวลานัดหมายอย่างน้อย 30 นาที เพื่อลงทะเบียนและเตรียมตัว'
                    ],
                    [
                        'question' => 'ค่าบริการเท่าไหร่?',
                        'answer' => 'ค่าตรวจ 50-100 บาท ขึ้นกับแผนก สำหรับผู้มีประกันสังคม/ข้าราชการ จ่ายตามสิทธิ์'
                    ],
                    [
                        'question' => 'หากมาไม่ได้ตามเวลานัด จะต้องทำอย่างไร?',
                        'answer' => 'กรุณาโทรแจ้งล่วงหน้า หรือสามารถนัดหมายใหม่ผ่านระบบออนไลน์ได้'
                    ]
                ];
                
                foreach($faqs as $index => $faq): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 hover:bg-gray-50 transition duration-300 faq-toggle flex justify-between items-center" 
                            data-target="faq-<?php echo $index; ?>">
                        <span><?php echo $faq['question']; ?></span>
                        <svg class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
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
        
        // Close all other FAQs
        document.querySelectorAll('.faq-toggle').forEach(otherButton => {
            if (otherButton !== this) {
                const otherTargetId = otherButton.getAttribute('data-target');
                const otherTargetDiv = document.getElementById(otherTargetId);
                const otherIcon = otherButton.querySelector('svg');
                otherTargetDiv.classList.add('hidden');
                otherIcon.style.transform = 'rotate(0deg)';
            }
        });
        
        // Toggle current FAQ
        if (targetDiv.classList.contains('hidden')) {
            targetDiv.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            targetDiv.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    });
});

// Department selection handler
document.getElementById('department').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const description = selectedOption.getAttribute('data-description');
    const infoDiv = document.getElementById('department-info');
    
    if (description && description.trim() !== '') {
        infoDiv.textContent = description;
        infoDiv.classList.remove('hidden');
    } else {
        infoDiv.classList.add('hidden');
    }
});

// Form validation
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    const requiredFields = ['name', 'phone', 'department', 'date', 'time'];
    let isValid = true;
    let firstErrorField = null;
    
    // Reset previous error states
    document.querySelectorAll('.border-red-500').forEach(field => {
        field.classList.remove('border-red-500');
        field.classList.add('border-gray-300');
    });
    
    requiredFields.forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field.value.trim()) {
            field.classList.remove('border-gray-300');
            field.classList.add('border-red-500');
            isValid = false;
            if (!firstErrorField) firstErrorField = field;
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
    
    // Validate phone number
    const phone = document.querySelector('[name="phone"]').value.trim();
    if (phone && !/^[0-9]{9,10}$/.test(phone.replace(/[-\s]/g, ''))) {
        document.querySelector('[name="phone"]').classList.add('border-red-500');
        alert('หมายเลขโทรศัพท์ไม่ถูกต้อง');
        e.preventDefault();
        return false;
    }
    
    // Validate date
    const selectedDate = new Date(document.querySelector('[name="date"]').value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate <= today) {
        document.querySelector('[name="date"]').classList.add('border-red-500');
        alert('กรุณาเลือกวันที่ในอนาคต');
        e.preventDefault();
        return false;
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
        if (firstErrorField) {
            firstErrorField.focus();
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '⏳ กำลังส่งการนัดหมาย...';
    submitBtn.disabled = true;
});

// Phone number formatting
document.querySelector('[name="phone"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        // Format as xxx-xxx-xxxx for display
        if (value.length >= 6) {
            value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
        } else if (value.length >= 3) {
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
        e.target.value = value;
    } else {
        e.target.value = value.substring(0, 10);
    }
});

// ID Card formatting and validation
document.querySelector('[name="id_card"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 13) {
        // Format as x-xxxx-xxxxx-xx-x for display
        if (value.length >= 10) {
            value = value.substring(0, 1) + '-' + value.substring(1, 5) + '-' + value.substring(5, 10) + '-' + value.substring(10, 12) + '-' + value.substring(12);
        } else if (value.length >= 5) {
            value = value.substring(0, 1) + '-' + value.substring(1, 5) + '-' + value.substring(5);
        } else if (value.length >= 1) {
            value = value.substring(0, 1) + '-' + value.substring(1);
        }
        e.target.value = value;
    }
    
    // Real-time validation
    const cleanValue = value.replace(/\D/g, '');
    if (cleanValue.length === 13) {
        if (validateThaiID(cleanValue)) {
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

// Prevent form resubmission on page reload
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Real-time field validation
document.querySelectorAll('input[required], select[required]').forEach(field => {
    field.addEventListener('blur', function() {
        if (this.value.trim()) {
            this.classList.remove('border-red-500');
            this.classList.add('border-green-500');
        } else {
            this.classList.remove('border-green-500');
            this.classList.add('border-gray-300');
        }
    });
    
    field.addEventListener('input', function() {
        if (this.classList.contains('border-red-500') && this.value.trim()) {
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-300');
        }
    });
});

// Smooth scroll for form errors
function scrollToError(element) {
    element.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });
}

// Date picker restrictions
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    const today = new Date();
    
    // Set minimum date to tomorrow
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    dateInput.min = tomorrow.toISOString().split('T')[0];
    
    // Set maximum date to 30 days from now
    const maxDate = new Date(today);
    maxDate.setDate(maxDate.getDate() + 30);
    dateInput.max = maxDate.toISOString().split('T')[0];
});
</script>

<?php include 'includes/footer.php'; ?>