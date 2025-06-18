<?php 
$page_title = "ข้อกำหนดการใช้งาน";
include 'includes/header.php'; 
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-purple-600 to-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">ข้อกำหนดการใช้งาน</h1>
            <p class="text-xl max-w-2xl mx-auto">เงื่อนไขและข้อตกลงในการใช้บริการ</p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-8">
                    <div class="flex items-center space-x-3">
                        <span class="text-purple-600 text-2xl">📋</span>
                        <div>
                            <h2 class="text-lg font-semibold text-purple-800">ข้อตกลงการใช้บริการ</h2>
                            <p class="text-purple-700">กรุณาอ่านข้อกำหนดเหล่านี้อย่างละเอียดก่อนใช้บริการ</p>
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
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">1. การยอมรับข้อกำหนด</h2>
                            <p class="text-gray-700">
                                การเข้าใช้งานเว็บไซต์และบริการของโรงพยาบาลทุ่งหัวช้าง 
                                ถือว่าคุณได้อ่าน เข้าใจ และยอมรับข้อกำหนดการใช้งานนี้แล้ว 
                                หากคุณไม่เห็นด้วยกับข้อกำหนดใดๆ กรุณาหยุดการใช้งานเว็บไซต์ทันที
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">2. คำจำกัดความ</h2>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li><strong>"เรา" "เจ้าของ" "โรงพยาบาล"</strong> หมายถึง โรงพยาบาลทุ่งหัวช้าง</li>
                                <li><strong>"คุณ" "ผู้ใช้"</strong> หมายถึง บุคคลที่เข้าใช้งานเว็บไซต์และบริการ</li>
                                <li><strong>"บริการ"</strong> หมายถึง บริการทางการแพทย์และการใช้งานเว็บไซต์</li>
                                <li><strong>"เว็บไซต์"</strong> หมายถึง www.thchospital.go.th และเว็บไซต์ที่เกี่ยวข้อง</li>
                                <li><strong>"ข้อมูล"</strong> หมายถึง ข้อมูลส่วนบุคคลและข้อมูลทางการแพทย์</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">3. บริการที่ให้</h2>
                            
                            <h3 class="text-xl font-semibold text-gray-700 mb-3">3.1 บริการออนไลน์</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ระบบนัดหมายแพทย์ออนไลน์</li>
                                <li>ข้อมูลแผนกและบริการ</li>
                                <li>ข่าวสารและประกาศ</li>
                                <li>ข้อมูลการติดต่อ</li>
                                <li>ข้อมูลสุขภาพและการดูแลตนเอง</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-6">3.2 บริการทางการแพทย์</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>การตรวจวินิจฉัยและรักษา</li>
                                <li>บริการผู้ป่วยนอกและผู้ป่วยใน</li>
                                <li>บริการฉุกเฉิน 24 ชั่วโมง</li>
                                <li>ตรวจทางห้องปฏิบัติการ</li>
                                <li>บริการทันตกรรม</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">4. หน้าที่และความรับผิดชอบของผู้ใช้</h2>
                            
                            <h3 class="text-xl font-semibold text-gray-700 mb-3">4.1 การให้ข้อมูล</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ให้ข้อมูลที่ถูกต้อง ครบถ้วน และเป็นปัจจุบัน</li>
                                <li>แจ้งเปลี่ยนแปลงข้อมูลเมื่อมีการเปลี่ยนแปลง</li>
                                <li>ให้ความร่วมมือในการตรวจสอบข้อมูล</li>
                                <li>รักษาความลับของข้อมูลการเข้าใช้งาน</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-6">4.2 การใช้งานเว็บไซต์</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ใช้งานเพื่อวัตถุประสงค์ที่ถูกต้องตามกฎหมาย</li>
                                <li>ไม่นำข้อมูลไปใช้ในทางที่ผิด</li>
                                <li>ไม่รบกวนการทำงานของระบบ</li>
                                <li>ปฏิบัติตามคำแนะนำและข้อกำหนด</li>
                                <li>รายงานปัญหาหรือข้อผิดพลาดที่พบ</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-6">4.3 การเข้ารับบริการ</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>มาตามเวลานัดหมายที่กำหนด</li>
                                <li>แจ้งล่วงหน้าหากไม่สามารถมาตามนัดได้</li>
                                <li>ปฏิบัติตามคำแนะนำของแพทย์และเจ้าหน้าที่</li>
                                <li>ชำระค่าบริการตามที่กำหนด</li>
                                <li>ปฏิบัติตามระเบียบของโรงพยาบาล</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">5. ข้อจำกัดการใช้งาน</h2>
                            <p class="text-gray-700 mb-4">คุณตกลงที่จะไม่:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ใช้เว็บไซต์เพื่อการที่ผิดกฎหมาย หรือไม่เหมาะสม</li>
                                <li>แทรกแซง หรือทำลายการทำงานของเว็บไซต์</li>
                                <li>พยายามเข้าถึงข้อมูลโดยไม่ได้รับอนุญาต</li>
                                <li>ส่งข้อมูลที่มีไวรัส หรือโค้ดที่เป็นอันตราย</li>
                                <li>ละเมิดสิทธิทางปัญญาของเรา หรือบุคคลอื่น</li>
                                <li>เผยแพร่ข้อมูลเท็จ หรือทำให้เกิดความเข้าใจผิด</li>
                                <li>ใช้ข้อมูลจากเว็บไซต์เพื่อการค้าโดยไม่ได้รับอนุญาต</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">6. ทรัพย์สินทางปัญญา</h2>
                            <p class="text-gray-700 mb-4">
                                เนื้อหาทั้งหมดในเว็บไซต์ รวมถึง ข้อความ รูปภาพ กราฟิก โลโก้ ไอคอน 
                                ซอฟต์แวร์ และข้อมูลอื่นๆ เป็นทรัพย์สินของโรงพยาบาลทุ่งหัวช้าง 
                                หรือเจ้าของลิขสิทธิ์ที่เกี่ยวข้อง
                            </p>
                            <p class="text-gray-700">
                                คุณสามารถดู ดาวน์โหลด และพิมพ์เนื้อหาเพื่อการใช้งานส่วนบุคคลเท่านั้น 
                                การนำไปใช้เพื่อการค้า หรือเผยแพร่ต่อ ต้องได้รับอนุญาตเป็นลายลักษณ์อักษร
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">7. ความรับผิดชอบและข้อจำกัด</h2>
                            
                            <h3 class="text-xl font-semibold text-gray-700 mb-3">7.1 ข้อจำกัดความรับผิดชอบ</h3>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ข้อมูลในเว็บไซต์มีไว้เพื่อการศึกษาและข้อมูลทั่วไป</li>
                                <li>ไม่ทดแทนคำแนะนำทางการแพทย์โดยตรง</li>
                                <li>ควรปรึกษาแพทย์สำหรับปัญหาสุขภาพเฉพาะเจาะจง</li>
                                <li>เราไม่รับผิดชอบต่อความเสียหายจากการใช้ข้อมูล</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-6">7.2 การรับประกัน</h3>
                            <p class="text-gray-700">
                                เราพยายามให้ข้อมูลที่ถูกต้องและเป็นปัจจุบัน แต่ไม่รับประกันความถูกต้อง 
                                ครบถ้วน หรือความเหมาะสมของข้อมูล เราอาจเปลี่ยนแปลงหรือปรับปรุง
                                เนื้อหาได้ตลอดเวลาโดยไม่ต้องแจ้งล่วงหน้า
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">8. การยกเลิกและระงับบริการ</h2>
                            
                            <h3 class="text-xl font-semibold text-gray-700 mb-3">8.1 การยกเลิกโดยผู้ใช้</h3>
                            <p class="text-gray-700 mb-4">คุณสามารถหยุดใช้บริการได้ตลอดเวลา โดย:</p>
                            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                                <li>ยกเลิกการนัดหมายที่มีอยู่</li>
                                <li>แจ้งให้เ