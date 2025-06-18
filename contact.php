<?php 
$page_title = "ติดต่อเรา";
include 'includes/header.php'; 
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-teal-600 to-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">ติดต่อเรา</h1>
            <p class="text-xl max-w-2xl mx-auto">พร้อมให้บริการและตอบคำถามของคุณ</p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Contact Information -->
                <div>
                    <h2 class="text-3xl font-bold mb-8 text-gray-800">📍 ข้อมูลการติดต่อ</h2>
                    
                    <!-- Hospital Address -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">🏥 ที่อยู่โรงพยาบาล</h3>
                        <div class="space-y-3">
                            <p class="text-gray-700">
                                <strong>โรงพยาบาลทุ่งหัวช้าง</strong><br>
                                123 ถนนหลัก ตำบลทุ่งหัวช้าง<br>
                                อำเภอเมือง จังหวัดลำพูน 51000
                            </p>
                        </div>
                    </div>

                    <!-- Contact Methods -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">📞 ช่องทางติดต่อ</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600">📞</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">โทรศัพท์</p>
                                    <p class="text-gray-600">053-580-xxx (แผนกต่างๆ)</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <span class="text-red-600">🚨</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">ฉุกเฉิน 24 ชม.</p>
                                    <p class="text-gray-600">053-580-xxx</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600">📠</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">โทรสาร</p>
                                    <p class="text-gray-600">053-580-xxx</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-600">📧</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">อีเมล</p>
                                    <p class="text-gray-600">info@thchospital.go.th</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600">💬</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Line Official</p>
                                    <p class="text-gray-600">@thchospital</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Department Phone Numbers -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">🏥 เบอร์โทรตรง</h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <?php
                            $departments = [
                                ['name' => 'แผนกผู้ป่วยนอก', 'ext' => '101'],
                                ['name' => 'แผนกผู้ป่วยใน', 'ext' => '102'],
                                ['name' => 'แผนกฉุกเฉิน', 'ext' => '103'],
                                ['name' => 'ห้องปฏิบัติการ', 'ext' => '104'],
                                ['name' => 'ร้านยา', 'ext' => '105'],
                                ['name' => 'การเงิน', 'ext' => '106'],
                                ['name' => 'ทันตกรรม', 'ext' => '107'],
                                ['name' => 'ผู้บริหาร', 'ext' => '108']
                            ];
                            
                            foreach($departments as $dept): ?>
                            <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                <span class="text-gray-700"><?php echo $dept['name']; ?></span>
                                <span class="text-blue-600 font-medium">ต่อ <?php echo $dept['ext']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Contact Form & Map -->
                <div>
                    <!-- Contact Form -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">✉️ ส่งข้อความถึงเรา</h3>
                        <form class="space-y-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อ-นามสกุล</label>
                                    <input type="text" id="contact_name" name="contact_name" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทรศัพท์</label>
                                    <input type="tel" id="contact_phone" name="contact_phone" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                                <input type="email" id="contact_email" name="contact_email" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="contact_subject" class="block text-sm font-medium text-gray-700 mb-1">เรื่อง</label>
                                <select id="contact_subject" name="contact_subject" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">เลือกหัวข้อ</option>
                                    <option value="appointment">นัดหมายแพทย์</option>
                                    <option value="service">สอบถามบริการ</option>
                                    <option value="complaint">ร้องเรียน/แสดงความคิดเห็น</option>
                                    <option value="suggestion">ข้อเสนอแนะ</option>
                                    <option value="other">อื่นๆ</option>
                                </select>
                            </div>

                            <div>
                                <label for="contact_message" class="block text-sm font-medium text-gray-700 mb-1">ข้อความ</label>
                                <textarea id="contact_message" name="contact_message" rows="5" required 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="กรุณาใส่ข้อความที่ต้องการสื่อสาร"></textarea>
                            </div>

                            <button type="submit" 
                                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                                📤 ส่งข้อความ
                            </button>
                        </form>
                    </div>

                    <!-- Map Placeholder -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">🗺️ แผนที่</h3>
                        <div class="bg-gray-200 rounded-lg h-64 flex items-center justify-center">
                            <div class="text-center text-gray-600">
                                <div class="text-4xl mb-2">📍</div>
                                <p class="font-medium">แผนที่โรงพยาบาลทุ่งหัวช้าง</p>
                                <p class="text-sm">123 ถนนหลัก ตำบลทุ่งหัวช้าง</p>
                                <p class="text-sm">อำเภอเมือง จังหวัดลำพูน 51000</p>
                                <button class="mt-3 bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition duration-300">
                                    เปิดใน Google Maps
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Operating Hours -->
    <section class="py-16 bg-blue-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">🕒 เวลาทำการ</h2>
            <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <!-- Regular Hours -->
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-4xl mb-4">🏥</div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">เวลาทำการปกติ</h3>
                    <div class="space-y-2 text-gray-600">
                        <p><strong>จันทร์ - ศุกร์</strong></p>
                        <p>08:00 - 16:30</p>
                        <p><strong>เสาร์ - อาทิตย์</strong></p>
                        <p>08:00 - 12:00</p>
                    </div>
                </div>

                <!-- Emergency Hours -->
                <div class="bg-red-100 rounded-lg shadow-lg p-6 text-center border-2 border-red-200">
                    <div class="text-4xl mb-4">🚨</div>
                    <h3 class="text-xl font-semibold mb-3 text-red-800">แผนกฉุกเฉิน</h3>
                    <div class="space-y-2 text-red-700">
                        <p><strong>เปิดตลอด 24 ชั่วโมง</strong></p>
                        <p>ทุกวัน รวมวันหยุด</p>
                        <p class="text-sm">พร้อมบริการฉุกเฉิน</p>
                    </div>
                </div>

                <!-- Pharmacy Hours -->
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-4xl mb-4">💊</div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">ร้านยา</h3>
                    <div class="space-y-2 text-gray-600">
                        <p><strong>จันทร์ - ศุกร์</strong></p>
                        <p>08:00 - 16:30</p>
                        <p><strong>เสาร์ - อาทิตย์</strong></p>
                        <p>08:00 - 12:00</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Transportation -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">🚗 การเดินทาง</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl mb-3">🚗</div>
                    <h4 class="font-semibold mb-2">รถยนต์ส่วนตัว</h4>
                    <p class="text-sm text-gray-600">มีที่จอดรถฟรี<br>เปิด 24 ชั่วโมง</p>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl mb-3">🚌</div>
                    <h4 class="font-semibold mb-2">รถประจำทาง</h4>
                    <p class="text-sm text-gray-600">สาย 101, 102<br>ลงป้ายโรงพยาบาล</p>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl mb-3">🏍️</div>
                    <h4 class="font-semibold mb-2">รถจักรยานยนต์รับจ้าง</h4>
                    <p class="text-sm text-gray-600">มีจุดรอหน้าโรงพยาบาล</p>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl mb-3">🚑</div>
                    <h4 class="font-semibold mb-2">รถพยาบาล</h4>
                    <p class="text-sm text-gray-600">บริการฉุกเฉิน<br>โทร 053-580-xxx</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>