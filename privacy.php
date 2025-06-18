<?php 
$page_title = "นโยบายความเป็นส่วนตัว";
include 'includes/header.php'; 
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">นโยบายความเป็นส่วนตัว</h1>
            <p class="text-xl max-w-2xl mx-auto">การปกป้องข้อมูลส่วนบุคคลของคุณ</p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                    <div class="flex items-center space-x-3">
                        <span class="text-blue-600 text-2xl">🔒</span>
                        <div>
                            <h2 class="text-lg font-semibold text-blue-800">ความมุ่งมั่นของเรา</h2>
                            <p class="text-blue-700">เราให้ความสำคัญกับการปกป้องข้อมูลส่วนบุคคลและความเป็นส่วนตัวของคุณ</p>
                        </div>
                    </div>
                </div>

                <div class="prose prose-lg max-w-none">
                    <p class="text-gray-600 mb-6">
                        <strong>วันที่มีผลบังคับใช้:</strong> 1 มกราคม 2568<br>
                        <strong>อัปเดตครั้งล่าสุด:</strong> <?php echo formatThaiDate(date('Y-m-d')); ?>
                    </p>

                    <div class="space-y-8">
                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">1. ข้อมูลที่เราเก็บรวบรวม</h2>
                            
                            <h3 class="text-xl font-semibold text-gray-700 mb-3">1.1 ข้อมูลส่วนบุคคล</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ชื่อ-นามสกุล</li>
                                <li>เลขบัตรประจำตัวประชาชน</li>
                                <li>วันเดือนปีเกิดและอายุ</li>
                                <li>เพศ</li>
                                <li>หมู่เลือด</li>
                                <li>ที่อยู่</li>
                                <li>หมายเลขโทรศัพท์</li>
                                <li>ที่อยู่อีเมล</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-6">1.2 ข้อมูลทางการแพทย์</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ประวัติการรักษา</li>
                                <li>อาการและการวินิจฉัยโรค</li>
                                <li>ข้อมูลการแพ้ยาและอาหาร</li>
                                <li>ประวัติการเจ็บป่วย</li>
                                <li>ผลการตรวจทางห้องปฏิบัติการ</li>
                                <li>ข้อมูลการใช้ยา</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-6">1.3 ข้อมูลการใช้งานเว็บไซต์</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ที่อยู่ IP</li>
                                <li>ประเภทเบราว์เซอร์</li>
                                <li>ระบบปฏิบัติการ</li>
                                <li>เวลาการเข้าใช้งาน</li>
                                <li>หน้าเว็บที่เยี่ยมชม</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">2. วัตถุประสงค์ในการใช้ข้อมูล</h2>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li><strong>ข้อมูลการรักษา:</strong> 5 ปี หลังการรักษาครั้งสุดท้าย</li>
                                <li><strong>ข้อมูลการนัดหมาย:</strong> 2 ปี หลังการนัดหมาย</li>
                                <li><strong>ข้อมูลการใช้งานเว็บไซต์:</strong> 1 ปี หลังการเข้าใช้งาน</li>
                                <li><strong>ข้อมูลสำหรับการติดต่อ:</strong> จนกว่าจะได้รับการร้องขอให้ลบ</li>
                            </ul>
                            <p class="text-gray-700 mt-4">
                                เมื่อครบกำหนดระยะเวลา เราจะลบหรือทำลายข้อมูลอย่างปลอดภัย 
                                ยกเว้นกรณีที่กฎหมายกำหนดให้เก็บรักษาไว้เป็นเวลานาน
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">7. คุกกี้และเทคโนโลยีติดตาม</h2>
                            <p class="text-gray-700 mb-4">เว็บไซต์ของเราใช้คุกกี้เพื่อ:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li><strong>คุกกี้ที่จำเป็น:</strong> เพื่อการทำงานของเว็บไซต์และการรักษาความปลอดภัย</li>
                                <li><strong>คุกกี้การวิเคราะห์:</strong> เพื่อเข้าใจการใช้งานและปรับปรุงเว็บไซต์</li>
                                <li><strong>คุกกี้ประสบการณ์:</strong> เพื่อจดจำการตั้งค่าและปรับแต่งประสบการณ์</li>
                            </ul>
                            <p class="text-gray-700 mt-4">
                                คุณสามารถปฏิเสธหรือลบคุกกี้ผ่านการตั้งค่าเบราว์เซอร์ของคุณได้
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">8. การโอนย้ายข้อมูลข้ามประเทศ</h2>
                            <p class="text-gray-700">
                                ข้อมูลส่วนบุคคลของคุณจะถูกเก็บรักษาและประมวลผลในประเทศไทย 
                                หากมีความจำเป็นต้องโอนย้ายข้อมูลไปต่างประเทศ 
                                เราจะดำเนินการตามมาตรฐานการปกป้องข้อมูลที่เหมาะสม 
                                และปฏิบัติตามกฎหมายคุ้มครองข้อมูลส่วนบุคคลอย่างเคร่งครัด
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">9. การแจ้งเหตุการณ์การละเมิดข้อมูล</h2>
                            <p class="text-gray-700 mb-4">
                                ในกรณีที่เกิดการละเมิดความปลอดภัยของข้อมูลส่วนบุคคล เราจะ:
                            </p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ดำเนินการแก้ไขและควบคุมเหตุการณ์ทันที</li>
                                <li>ประเมินความเสี่ยงและผลกระทบ</li>
                                <li>แจ้งต่อสำนักงานคณะกรรมการคุ้มครองข้อมูลส่วนบุคคลแห่งชาติ</li>
                                <li>แจ้งให้ผู้ที่ได้รับผลกระทบทราบในกรณีที่มีความเสี่ยงสูง</li>
                                <li>ดำเนินการป้องกันไม่ให้เกิดเหตุการณ์ซ้ำ</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">10. การเปลี่ยนแปลงนโยบาย</h2>
                            <p class="text-gray-700">
                                เราอาจปรับปรุงนโยบายความเป็นส่วนตัวนี้เป็นครั้งคราว 
                                การเปลี่ยนแปลงที่สำคัญจะมีการแจ้งให้ทราบล่วงหน้า 
                                ผ่านเว็บไซต์หรือช่องทางติดต่ออื่นๆ 
                                การใช้บริการต่อไปหลังจากการเปลี่ยนแปลง 
                                ถือว่าคุณยอมรับนโยบายที่ปรับปรุงแล้ว
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">11. การติดต่อเรา</h2>
                            <p class="text-gray-700 mb-4">
                                หากคุณมีคำถาม ข้อกังวล หรือต้องการใช้สิทธิเกี่ยวกับข้อมูลส่วนบุคคล 
                                กรุณาติดต่อเราผ่าน:
                            </p>
                            
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">เจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล</h3>
                                <div class="space-y-2 text-gray-700">
                                    <p><strong>โรงพยาบาลทุ่งหัวช้าง</strong></p>
                                    <p>📧 <strong>อีเมล:</strong> privacy@thchospital.go.th</p>
                                    <p>📞 <strong>โทรศัพท์:</strong> 053-580-xxx ต่อ 108</p>
                                    <p>📠 <strong>โทรสาร:</strong> 053-580-xxx</p>
                                    <p>📍 <strong>ที่อยู่:</strong> 123 ถนนหลัก ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน 51000</p>
                                </div>
                                
                                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                                    <p class="text-blue-800 text-sm">
                                        <strong>เวลาทำการ:</strong> จันทร์-ศุกร์ 08:00-16:30 น.<br>
                                        เราจะตอบกลับภายใน 30 วันหลังจากได้รับการติดต่อ
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">12. กฎหมายที่เกี่ยวข้อง</h2>
                            <p class="text-gray-700">
                                นโยบายนี้จัดทำขึ้นตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 
                                และกฎหมายอื่นๆ ที่เกี่ยวข้อง รวมถึง:
                            </p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700 mt-4">
                                <li>พระราชบัญญัติการประกอบโรคศิลปะ พ.ศ. 2542</li>
                                <li>พระราชบัญญัติสถานพยาบาล พ.ศ. 2541</li>
                                <li>กฎกระทรวงและประกาศที่เกี่ยวข้อง</li>
                            </ul>
                        </section>
                    </div>

                    <div class="mt-12 p-6 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-start space-x-3">
                            <span class="text-green-600 text-2xl">✅</span>
                            <div>
                                <h3 class="text-lg font-semibold text-green-800 mb-2">ความมั่นใจของคุณคือสิ่งสำคัญ</h3>
                                <p class="text-green-700">
                                    เราตระหนักถึงความสำคัญของข้อมูลส่วนบุคคลของคุณ 
                                    และมุ่งมั่นที่จะปกป้องและใช้ข้อมูลของคุณอย่างรับผิดชอบ 
                                    เพื่อให้คุณได้รับบริการทางการแพทย์ที่มีคุณภาพและปลอดภัย
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        <p class="text-gray-500 text-sm">
                            นโยบายความเป็นส่วนตัวนี้มีผลบังคับใช้ตั้งแต่วันที่ 1 มกราคม พ.ศ. 2568<br>
                            อัปเดตครั้งล่าสุด: <?php echo formatThaiDate(date('Y-m-d')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold text-center mb-8 text-gray-800">เอกสารที่เกี่ยวข้อง</h2>
            <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <a href="terms.php" class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <div class="text-3xl mb-3">📋</div>
                    <h3 class="font-semibold text-gray-800 mb-2">ข้อกำหนดการใช้งาน</h3>
                    <p class="text-sm text-gray-600">เงื่อนไขและข้อกำหนดในการใช้บริการ</p>
                </a>
                
                <a href="contact.php" class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <div class="text-3xl mb-3">📞</div>
                    <h3 class="font-semibold text-gray-800 mb-2">ติดต่อเรา</h3>
                    <p class="text-sm text-gray-600">ช่องทางการติดต่อและสอบถาม</p>
                </a>
                
                <a href="about.php" class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <div class="text-3xl mb-3">🏥</div>
                    <h3 class="font-semibold text-gray-800 mb-2">เกี่ยวกับเรา</h3>
                    <p class="text-sm text-gray-600">ข้อมูลโรงพยาบาลและบริการ</p>
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>การให้บริการทางการแพทย์:</strong> เพื่อการวินิจฉัย รักษา และติดตามผลการรักษา</li>
                                <li><strong>การนัดหมาย:</strong> เพื่อจัดการและติดต่อเกี่ยวกับการนัดหมาย</li>
                                <li><strong>การติดต่อสื่อสาร:</strong> เพื่อส่งข้อมูลที่เกี่ยวข้องกับการรักษาและบริการ</li>
                                <li><strong>การปรับปรุงบริการ:</strong> เพื่อพัฒนาและปรับปรุงคุณภาพการบริการ</li>
                                <li><strong>การปฏิบัติตามกฎหมาย:</strong> เพื่อปฏิบัติตามข้อกำหนดทางกฎหมายและระเบียบ</li>
                                <li><strong>การรายงานทางสถิติ:</strong> เพื่อจัดทำรายงานทางสถิติโดยไม่เปิดเผยตัวตน</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">3. การเปิดเผยข้อมูล</h2>
                            <p class="text-gray-700 mb-4">เราจะเปิดเผยข้อมูลส่วนบุคคลของคุณเฉพาะในกรณีต่อไปนี้:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li><strong>การรักษาต่อเนื่อง:</strong> กับแพทย์และเจ้าหน้าที่ทางการแพทย์ที่เกี่ยวข้องกับการรักษา</li>
                                <li><strong>การส่งต่อผู้ป่วย:</strong> กับสถานพยาบาลอื่นที่มีการส่งต่อผู้ป่วย</li>
                                <li><strong>ตามกฎหมาย:</strong> เมื่อมีข้อกำหนดทางกฎหมายหรือคำสั่งศาล</li>
                                <li><strong>กรณีฉุกเฉิน:</strong> เพื่อปกป้องชีวิตและความปลอดภัย</li>
                                <li><strong>การได้รับความยินยอม:</strong> เมื่อได้รับความยินยอมจากคุณ</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">4. การรักษาความปลอดภัยข้อมูล</h2>
                            
                            <h3 class="text-xl font-semibold text-gray-700 mb-3">4.1 มาตรการทางเทคนิค</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>การเข้ารหัสข้อมูล (SSL/TLS)</li>
                                <li>ระบบยืนยันตัวตนหลายชั้น</li>
                                <li>การสำรองข้อมูลอย่างปลอดภัย</li>
                                <li>การอัปเดตระบบความปลอดภัยอย่างสม่ำเสมอ</li>
                                <li>การตรวจสอบการเข้าถึงข้อมูล</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-6">4.2 มาตรการทางการบริหาร</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>การอบรมเจ้าหน้าที่เรื่องการปกป้องข้อมูล</li>
                                <li>การกำหนดสิทธิ์การเข้าถึงข้อมูลตามหน้าที่</li>
                                <li>การตรวจสอบการใช้งานระบบ</li>
                                <li>นโยบายการใช้งานที่ชัดเจน</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">5. สิทธิของเจ้าของข้อมูล</h2>
                            <p class="text-gray-700 mb-4">คุณมีสิทธิในการ:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li><strong>เข้าถึงข้อมูล:</strong> ขอดูข้อมูลส่วนบุคคลที่เราเก็บรวบรวม</li>
                                <li><strong>แก้ไขข้อมูล:</strong> ขอแก้ไขข้อมูลที่ไม่ถูกต้องหรือไม่เป็นปัจจุบัน</li>
                                <li><strong>ลบข้อมูล:</strong> ขอลบข้อมูลในกรณีที่กฎหมายอนุญาต</li>
                                <li><strong>จำกัดการใช้:</strong> ขอจำกัดการใช้ข้อมูลในบางกรณี</li>
                                <li><strong>คัดค้านการใช้:</strong> คัดค้านการใช้ข้อมูลเพื่อวัตถุประสงค์บางอย่าง</li>
                                <li><strong>ถอนความยินยอม:</strong> ถอนความยินยอมที่เคยให้ไว้</li>
                                <li><strong>ร้องเรียน:</strong> ร้องเรียนต่อหน่วยงานกำกับดูแล</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">6. การเก็บรักษาข้อมูล</h2>
                            <p class="text-gray-700 mb-4">เราเก็บรักษาข้อมูลของคุณตามระยะเวลาที่กำหนดไว้ดังนี้:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li><strong>ข้อมูลการรักษา:</strong> 5 ปี หลังการรักษาครั้งสุดท้าย</li>
                                <li><strong>ข้อมูลการนัดหมาย:</strong> 2 ปี หลังการนัดหมาย</li>
                                <li><strong>