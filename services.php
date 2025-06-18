<?php 
$page_title = "บริการ";
include 'includes/header.php'; 
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-green-600 to-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">บริการของเรา</h1>
            <p class="text-xl max-w-2xl mx-auto">ครบครันด้วยบริการทางการแพทย์ที่ทันสมัย</p>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <!-- Outpatient Services -->
            <div id="outpatient" class="mb-16">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">แผนกผู้ป่วยนอก</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php
                    $outpatient_services = [
                        [
                            'name' => 'แพทย์ทั่วไป',
                            'icon' => '👩‍⚕️',
                            'time' => 'จันทร์-ศุกร์ 08:00-16:30',
                            'desc' => 'บริการตรวจรักษาโรคทั่วไป การดูแลสุขภาพเบื้องต้น'
                        ],
                        [
                            'name' => 'กุมารเวชกรรม',
                            'icon' => '👶',
                            'time' => 'จันทร์-ศุกร์ 08:00-16:30',
                            'desc' => 'บริการตรวจรักษาเด็กและวัยรุ่น การฉีดวัคซีน'
                        ],
                        [
                            'name' => 'สูติ-นรีเวชกรรม',
                            'icon' => '🤱',
                            'time' => 'จันทร์-ศุกร์ 08:00-16:30',
                            'desc' => 'การดูแลสุขภาพสตรี การฝากครรภ์ การคลอด'
                        ],
                        [
                            'name' => 'ศัลยกรรม',
                            'icon' => '🏥',
                            'time' => 'จันทร์-ศุกร์ 08:00-16:30',
                            'desc' => 'การผ่าตัดทั่วไป การรักษาบาดแผล'
                        ],
                        [
                            'name' => 'ออร์โธปิดิกส์',
                            'icon' => '🦴',
                            'time' => 'จันทร์-ศุกร์ 08:00-16:30',
                            'desc' => 'การรักษากระดูกและข้อ การฟื้นฟูสมรรถภาพ'
                        ],
                        [
                            'name' => 'ทันตกรรม',
                            'icon' => '🦷',
                            'time' => 'จันทร์-ศุกร์ 08:00-16:30',
                            'desc' => 'การรักษาฟัน การขูดหินปูน การถอนฟัน'
                        ]
                    ];
                    
                    foreach($outpatient_services as $service): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                        <div class="text-4xl mb-4 text-center"><?php echo $service['icon']; ?></div>
                        <h3 class="text-xl font-semibold mb-2 text-gray-800 text-center"><?php echo $service['name']; ?></h3>
                        <p class="text-gray-600 mb-3 text-center"><?php echo $service['desc']; ?></p>
                        <div class="text-sm text-blue-600 font-medium text-center">
                            <span>🕒 <?php echo $service['time']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Inpatient Services -->
            <div id="inpatient" class="mb-16">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">แผนกผู้ป่วยใน</h2>
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-2xl font-semibold mb-4 text-gray-800">🏨 ห้องพักผู้ป่วย</h3>
                            <ul class="space-y-3">
                                <li class="flex items-center space-x-3">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    <span>ห้องพิเศษ (VIP) - 1 เตียง</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    <span>ห้องเตียงเดี่ยว - 1 เตียง</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    <span>ห้องเตียงคู่ - 2 เตียง</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    <span>ห้องรวม - 4-6 เตียง</span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-2xl font-semibold mb-4 text-gray-800">🩺 การดูแลพิเศษ</h3>
                            <ul class="space-y-3">
                                <li class="flex items-center space-x-3">
                                    <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                    <span>พยาบาลเวร 24 ชั่วโมง</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                    <span>แพทย์เวรตลอดเวลา</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                    <span>ระบบเรียกพยาบาล</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                    <span>อาหารตามแผนการรักษา</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Services -->
            <div id="emergency" class="mb-16">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">แผนกฉุกเฉิน</h2>
                <div class="bg-red-50 rounded-lg p-8">
                    <div class="text-center mb-8">
                        <div class="text-6xl mb-4">🚨</div>
                        <h3 class="text-2xl font-bold text-red-600 mb-2">เปิดบริการ 24 ชั่วโมง</h3>
                        <p class="text-gray-700">พร้อมให้บริการฉุกเฉินตลอดเวลา</p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h4 class="font-semibold mb-3 text-gray-800">🚑 รถพยาบาล</h4>
                            <p class="text-gray-600 text-sm">บริการรถพยาบาลฉุกเฉิน พร้อมอุปกรณ์แพทย์ครบครัน</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h4 class="font-semibold mb-3 text-gray-800">⚡ ห้องช็อค</h4>
                            <p class="text-gray-600 text-sm">ห้องปฐมพยาบาลฉุกเฉิน พร้อมอุปกรณ์กู้ชีพ</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h4 class="font-semibold mb-3 text-gray-800">🩺 แพทย์เวร</h4>
                            <p class="text-gray-600 text-sm">แพทย์และพยาบาลเวรตลอด 24 ชั่วโมง</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laboratory Services -->
            <div id="lab" class="mb-16">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">ห้องปฏิบัติการ</h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">🔬 การตรวจทางห้องแล็บ</h3>
                        <ul class="space-y-2">
                            <li>• ตรวจเลือด (CBC, Chemistry)</li>
                            <li>• ตรวจปัสสาวะ</li>
                            <li>• ตรวจอุจจาระ</li>
                            <li>• ตรวจระดับน้ำตาล</li>
                            <li>• ตรวจไขมันในเลือด</li>
                            <li>• ตรวจการทำงานของตับ ไต</li>
                        </ul>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">📊 การตรวจพิเศษ</h3>
                        <ul class="space-y-2">
                            <li>• เอกซเรย์ (X-Ray)</li>
                            <li>• อัลตราซาวน์ (Ultrasound)</li>
                            <li>• คลื่นไฟฟ้าหัวใจ (ECG)</li>
                            <li>• การตรวจสายตา</li>
                            <li>• การตรวจการได้ยิน</li>
                            <li>• การตรวจมะเร็งปากมดลูก</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Additional Services -->
            <div class="bg-blue-50 rounded-lg p-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">บริการอื่นๆ</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl mb-2">💊</div>
                        <h4 class="font-semibold mb-1">ร้านยา</h4>
                        <p class="text-sm text-gray-600">จ่ายยาตามใบสั่งแพทย์</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl mb-2">🩺</div>
                        <h4 class="font-semibold mb-1">ตรวจสุขภาพ</h4>
                        <p class="text-sm text-gray-600">แพ็กเกจตรวจสุขภาพ</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl mb-2">💉</div>
                        <h4 class="font-semibold mb-1">ฉีดวัคซีน</h4>
                        <p class="text-sm text-gray-600">วัคซีนเด็กและผู้ใหญ่</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl mb-2">🏃‍♂️</div>
                        <h4 class="font-semibold mb-1">ฟื้นฟูสมรรถภาพ</h4>
                        <p class="text-sm text-gray-600">กายภาพบำบัด</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">ต้องการนัดหมายแพทย์?</h2>
            <p class="text-xl mb-8">สะดวก รวดเร็ว ผ่านระบบออนไลน์</p>
            <a href="appointment.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">นัดหมายเลย</a>
        </div>
    </section>
</main>

<?php include 'includes/footer.php';