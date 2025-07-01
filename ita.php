<?php 
$page_title = "การดำเนินงานโครงการ Integrity and Transparency Assessment (MOPH ITA)";
require_once 'config/database.php';
include 'includes/header.php'; 
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    การดำเนินงานโครงการ ITA
                </h1>
                <p class="text-xl md:text-2xl mb-2">
                    Integrity and Transparency Assessment (MOPH ITA)
                </p>
                <p class="text-lg opacity-90">
                    ประจำปีงบประมาณ 2568
                </p>
                <div class="mt-8 inline-block bg-white/20 backdrop-blur-sm rounded-lg px-6 py-3">
                    <p class="text-sm">
                        💼 โครงการประเมินคุณธรรมและความโปร่งใสในการดำเนินงานของหน่วยงานภาครัฐ
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">
                        เกี่ยวกับ MOPH ITA
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-600">
                        <p class="mb-6">
                            <strong>MOPH ITA (Ministry of Public Health Integrity and Transparency Assessment)</strong> 
                            เป็นโครงการประเมินคุณธรรมและความโปร่งใสในการดำเนินงานของหน่วยงานในสังกัดกระทรวงสาธารณสุข 
                            เพื่อส่งเสริมการบริหารงานที่มีคุณธรรม โปร่งใส และตรวจสอบได้
                        </p>
                        <div class="grid md:grid-cols-3 gap-6 mt-8">
                            <div class="text-center">
                                <div class="text-4xl mb-3">🎯</div>
                                <h3 class="text-lg font-semibold mb-2">วัตถุประสงค์</h3>
                                <p class="text-sm">ยกระดับคุณธรรมและความโปร่งใสในการดำเนินงาน</p>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl mb-3">📊</div>
                                <h3 class="text-lg font-semibold mb-2">การประเมิน</h3>
                                <p class="text-sm">ประเมินตามเกณฑ์มาตรฐาน 22 ข้อ</p>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl mb-3">🏆</div>
                                <h3 class="text-lg font-semibold mb-2">เป้าหมาย</h3>
                                <p class="text-sm">พัฒนาองค์กรสู่ความเป็นเลิศ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MOIT Criteria Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">
                เกณฑ์การประเมิน MOIT (22 ข้อ)
            </h2>
            
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- MOIT 1-11 -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold mb-6 text-blue-600">📢 ข้อมูลพื้นฐานและการเผยแพร่</h3>
                    <div class="space-y-4">
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 1: ระบบเผยแพร่ข้อมูล</h4>
                            <p class="text-sm text-gray-600">การวางระบบเผยแพร่ข้อมูลต่อสาธารณะผ่านเว็บไซต์</p>
                            <a href="?page_id=1886" class="text-blue-600 hover:underline text-sm">📄 ดูรายละเอียด</a>
                        </div>
                        
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 2: ข้อมูลข่าวสารปัจจุบัน</h4>
                            <p class="text-sm text-gray-600">การเปิดเผยข้อมูลข่าวสารที่เป็นปัจจุบัน</p>
                            <div class="mt-2 space-y-1">
                                <a href="?page_id=1890" class="block text-blue-600 hover:underline text-sm">• ข้อมูลผู้บริหาร</a>
                                <a href="?page_id=1892" class="block text-blue-600 hover:underline text-sm">• นโยบายผู้บริหาร</a>
                                <a href="?page_id=1896" class="block text-blue-600 hover:underline text-sm">• โครงสร้างหน่วยงาน</a>
                                <a href="?page_id=1898" class="block text-blue-600 hover:underline text-sm">• หน้าที่และอำนาจ</a>
                            </div>
                        </div>

                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 3-5: การจัดซื้อจัดจ้าง</h4>
                            <p class="text-sm text-gray-600">รายงานและระบบจัดซื้อจัดจ้าง</p>
                            <a href="?page_id=1967" class="text-green-600 hover:underline text-sm">📊 ดูรายงาน</a>
                        </div>

                        <div class="border-l-4 border-purple-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 6-8: การบริหารทรัพยากรบุคคล</h4>
                            <p class="text-sm text-gray-600">นโยบายและการประเมินผลการปฏิบัติราชการ</p>
                        </div>

                        <div class="border-l-4 border-orange-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 9-11: การจัดการร้องเรียน</h4>
                            <p class="text-sm text-gray-600">แนวปฏิบัติและการมีส่วนร่วม</p>
                            <a href="?page_id=2176" class="text-orange-600 hover:underline text-sm">📋 คู่มือปฏิบัติ</a>
                        </div>
                    </div>
                </div>

                <!-- MOIT 12-22 -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold mb-6 text-red-600">🛡️ การป้องกันการทุจริตและจริยธรรม</h3>
                    <div class="space-y-4">
                        <div class="border-l-4 border-red-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 12: การป้องกันการรับสินบน</h4>
                            <p class="text-sm text-gray-600">มาตรการป้องกันการรับสินบนที่เป็นระบบ</p>
                            <a href="?page_id=2227" class="text-red-600 hover:underline text-sm">🚫 No Gift Policy</a>
                        </div>

                        <div class="border-l-4 border-red-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 13: จริยธรรมการจัดซื้อยา</h4>
                            <p class="text-sm text-gray-600">การประเมินเกณฑ์จริยธรรมการจัดซื้อยาและเวชภัณฑ์</p>
                            <a href="?page_id=2234" class="text-red-600 hover:underline text-sm">📈 รายงานประเมิน</a>
                        </div>

                        <div class="border-l-4 border-indigo-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 15-16: แผนป้องกันทุจริต</h4>
                            <p class="text-sm text-gray-600">แผนปฏิบัติการและรายงานผล</p>
                            <div class="mt-2 space-y-1">
                                <a href="?page_id=2241" class="block text-indigo-600 hover:underline text-sm">• แผนป้องกันทุจริต</a>
                                <a href="?page_id=2244" class="block text-indigo-600 hover:underline text-sm">• แผนส่งเสริมคุณธรรม</a>
                            </div>
                        </div>

                        <div class="border-l-4 border-yellow-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 17-18: บริหารความเสี่ยง</h4>
                            <p class="text-sm text-gray-600">การประเมินและควบคุมความเสี่ยงการทุจริต</p>
                            <a href="?page_id=2255" class="text-yellow-600 hover:underline text-sm">⚠️ แผนบริหารความเสี่ยง</a>
                        </div>

                        <div class="border-l-4 border-teal-500 pl-4">
                            <h4 class="font-semibold text-gray-800">MOIT 19-22: จริยธรรมและสิทธิมนุษยชน</h4>
                            <p class="text-sm text-gray-600">การส่งเสริมจริยธรรมและป้องกันการล่วงละเมิด</p>
                            <div class="mt-2 space-y-1">
                                <a href="?page_id=2267" class="block text-teal-600 hover:underline text-sm">• เจตนารมณ์ป้องกันทุจริต</a>
                                <a href="?page_id=2270" class="block text-teal-600 hover:underline text-sm">• คู่มือป้องกันการล่วงละเมิด</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Documents Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">
                เอกสารสำคัญ
            </h2>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- จริยธรรม -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-3">⚖️</div>
                        <h3 class="text-xl font-semibold text-gray-800">จริยธรรม</h3>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li><a href="?page_id=1913" class="text-blue-600 hover:underline">• พ.ร.บ.มาตรฐานทางจริยธรรม 2562</a></li>
                        <li><a href="?page_id=1915" class="text-blue-600 hover:underline">• ประมวลจริยธรรมข้าราชการ</a></li>
                        <li><a href="?page_id=1917" class="text-blue-600 hover:underline">• ข้อกำหนดจริยธรรมเจ้าหน้าที่</a></li>
                    </ul>
                </div>

                <!-- ยุทธศาสตร์ -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-3">🎯</div>
                        <h3 class="text-xl font-semibold text-gray-800">ยุทธศาสตร์</h3>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li><a href="?page_id=1919" class="text-blue-600 hover:underline">• ยุทธศาสตร์ชาติ 2561-2580</a></li>
                        <li><a href="?page_id=1942" class="text-blue-600 hover:underline">• นโยบายและยุทธศาสตร์หน่วยงาน</a></li>
                        <li><a href="?page_id=1944" class="text-blue-600 hover:underline">• แผนปฏิบัติการประจำปี</a></li>
                    </ul>
                </div>

                <!-- คู่มือ -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-3">📚</div>
                        <h3 class="text-xl font-semibold text-gray-800">คู่มือปฏิบัติ</h3>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li><a href="?page_id=1953" class="text-blue-600 hover:underline">• คู่มือการร้องเรียน</a></li>
                        <li><a href="?page_id=1955" class="text-blue-600 hover:underline">• คู่มือร้องเรียนทุจริต</a></li>
                        <li><a href="?page_id=1957" class="text-blue-600 hover:underline">• คู่มือภารกิจหลัก</a></li>
                    </ul>
                </div>

                <!-- การจัดซื้อ -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-3">🛒</div>
                        <h3 class="text-xl font-semibold text-gray-800">การจัดซื้อจัดจ้าง</h3>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li><a href="?page_id=1967" class="text-blue-600 hover:underline">• วิเคราะห์ผลการจัดซื้อ</a></li>
                        <li><a href="?page_id=1969" class="text-blue-600 hover:underline">• แผนจัดซื้อจัดจ้าง</a></li>
                        <li><a href="?page_id=1974" class="text-blue-600 hover:underline">• แนวทางปฏิบัติการตรวจสอบ</a></li>
                    </ul>
                </div>

                <!-- รายงาน -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-3">📊</div>
                        <h3 class="text-xl font-semibold text-gray-800">รายงานผล</h3>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li><a href="?page_id=2248" class="text-blue-600 hover:underline">• รายงานป้องกันทุจริต</a></li>
                        <li><a href="?page_id=2252" class="text-blue-600 hover:underline">• รายงานส่งเสริมคุณธรรม</a></li>
                        <li><a href="?page_id=2186" class="text-blue-600 hover:underline">• รายงานการร้องเรียน</a></li>
                    </ul>
                </div>

                <!-- ช่องทางติดต่อ -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-3">📞</div>
                        <h3 class="text-xl font-semibold text-gray-800">ช่องทางติดต่อ</h3>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li><a href="?page_id=2183" class="text-blue-600 hover:underline">• ช่องทางการร้องเรียน</a></li>
                        <li><a href="?page_id=1905" class="text-blue-600 hover:underline">• ข้อมูลการติดต่อ</a></li>
                        <li><a href="?page_id=1907" class="text-blue-600 hover:underline">• ช่องทางรับฟังความเห็น</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Progress Timeline -->
    <section class="py-16 bg-gradient-to-br from-blue-50 to-indigo-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">
                ความคืบหน้าการดำเนินงาน ปีงบประมาณ 2568
            </h2>

            <div class="max-w-4xl mx-auto">
                <div class="relative">
                    <!-- Timeline line -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-1 h-full bg-blue-300"></div>
                    
                    <!-- Timeline items -->
                    <div class="space-y-12">
                        <!-- Q1 -->
                        <div class="relative flex items-center">
                            <div class="flex-1 pr-8 text-right">
                                <div class="bg-white rounded-lg p-6 shadow-lg">
                                    <h3 class="text-lg font-semibold text-blue-600 mb-2">ไตรมาส 1</h3>
                                    <p class="text-sm text-gray-600">ตุลาคม 2567 - ธันวาคม 2567</p>
                                    <p class="text-sm mt-2">• จัดทำเอกสารพื้นฐาน<br>• เตรียมข้อมูลเผยแพร่</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 bg-blue-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative z-10">
                                <span class="text-white text-sm font-bold">1</span>
                            </div>
                            <div class="flex-1 pl-8"></div>
                        </div>

                        <!-- Q2 -->
                        <div class="relative flex items-center">
                            <div class="flex-1 pr-8"></div>
                            <div class="w-8 h-8 bg-green-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative z-10">
                                <span class="text-white text-sm font-bold">2</span>
                            </div>
                            <div class="flex-1 pl-8">
                                <div class="bg-white rounded-lg p-6 shadow-lg">
                                    <h3 class="text-lg font-semibold text-green-600 mb-2">ไตรมาส 2</h3>
                                    <p class="text-sm text-gray-600">มกราคม 2568 - มีนาคม 2568</p>
                                    <p class="text-sm mt-2">• รายงานผลการดำเนินงาน<br>• ประเมินความเสี่ยงการทุจริต</p>
                                </div>
                            </div>
                        </div>

                        <!-- Q3 -->
                        <div class="relative flex items-center">
                            <div class="flex-1 pr-8 text-right">
                                <div class="bg-white rounded-lg p-6 shadow-lg">
                                    <h3 class="text-lg font-semibold text-orange-600 mb-2">ไตรมาส 3</h3>
                                    <p class="text-sm text-gray-600">เมษายน 2568 - มิถุนายน 2568</p>
                                    <p class="text-sm mt-2">• ติดตามและประเมินผล<br>• ปรับปรุงการดำเนินงาน</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 bg-orange-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative z-10">
                                <span class="text-white text-sm font-bold">3</span>
                            </div>
                            <div class="flex-1 pl-8"></div>
                        </div>

                        <!-- Q4 -->
                        <div class="relative flex items-center">
                            <div class="flex-1 pr-8"></div>
                            <div class="w-8 h-8 bg-purple-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative z-10">
                                <span class="text-white text-sm font-bold">4</span>
                            </div>
                            <div class="flex-1 pl-8">
                                <div class="bg-white rounded-lg p-6 shadow-lg">
                                    <h3 class="text-lg font-semibold text-purple-600 mb-2">ไตรมาส 4</h3>
                                    <p class="text-sm text-gray-600">กรกฎาคม 2568 - กันยายน 2568</p>
                                    <p class="text-sm mt-2">• สรุปผลการดำเนินงาน<br>• จัดส่งรายงานประจำปี</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact and Feedback -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                        ติดต่อสอบถามและแสดงความคิดเห็น
                    </h2>
                    
                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-xl font-semibold mb-4 text-blue-600">📧 ติดต่อสอบถาม</h3>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-3">
                                    <div class="text-blue-500">📍</div>
                                    <div>
                                        <p class="font-medium">โรงพยาบาลทุ่งหัวช้าง</p>
                                        <p class="text-sm text-gray-600">123 ถนนหลัก ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน 51000</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-blue-500">📞</div>
                                    <div>
                                        <p class="font-medium">053-580-xxx</p>
                                        <p class="text-sm text-gray-600">เวลาทำการ: จันทร์-ศุกร์ 08:00-16:30</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-blue-500">📧</div>
                                    <div>
                                        <p class="font-medium">info@thchospital.go.th</p>
                                        <p class="text-sm text-gray-600">อีเมลสำหรับติดต่อสอบถาม</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold mb-4 text-green-600">💬 ช่องทางแสดงความคิดเห็น</h3>
                            <div class="space-y-3">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h4 class="font-medium mb-2">ช่องทางออนไลน์</h4>
                                    <p class="text-sm text-gray-600 mb-2">
                                        ท่านสามารถแสดงความคิดเห็นเกี่ยวกับการดำเนินงาน MOPH ITA ได้ผ่านช่องทางต่าง ๆ
                                    </p>
                                    <div class="space-y-1 text-sm">
                                        <p>• เว็บไซต์: www.thchospital.go.th</p>
                                        <p>• Facebook: โรงพยาบาลทุ่งหัวช้าง</p>
                                        <p>• Line Official Account</p>
                                    </div>
                                </div>
                                
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <h4 class="font-medium mb-2">การร้องเรียน</h4>
                                    <p class="text-sm text-gray-600 mb-2">
                                        หากพบการปฏิบัติที่ไม่เหมาะสมหรือต้องการร้องเรียน
                                    </p>
                                    <div class="space-y-1 text-sm">
                                        <p>• หมายเลขโทรศัพท์: 053-580-xxx</p>
                                        <p>• กล่องรับความคิดเห็น</p>
                                        <p>• ระบบรับเรื่องร้องเรียนออนไลน์</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-blue-800 mb-2">
                                🤝 ร่วมกันสร้างองค์กรที่มีคุณธรรมและความโปร่งใส
                            </h4>
                            <p class="text-gray-600">
                                ความคิดเห็นและข้อเสนอแนะของท่านมีความสำคัญต่อการพัฒนาการดำเนินงานของเรา
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">
                คำถามที่พบบ่อย (FAQ)
            </h2>

            <div class="max-w-4xl mx-auto">
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            ❓ MOPH ITA คืออะไร?
                        </h3>
                        <p class="text-gray-600">
                            MOPH ITA (Ministry of Public Health Integrity and Transparency Assessment) 
                            เป็นโครงการประเมินคุณธรรมและความโปร่งใสในการดำเนินงานของหน่วยงานใน
                            สังกัดกระทรวงสาธารณสุข เพื่อส่งเสริมการบริหารงานที่มีคุณธรรมและโปร่งใส
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            ❓ มีเกณฑ์การประเมินกี่ข้อ?
                        </h3>
                        <p class="text-gray-600">
                            การประเมิน MOPH ITA มีเกณฑ์การประเมินทั้งหมด 22 ข้อ (MOIT 1-22) 
                            ครอบคลุมด้านการเผยแพร่ข้อมูล การจัดซื้อจัดจ้าง การบริหารทรัพยากรบุคคล 
                            การป้องกันการทุจริต และการส่งเสริมจริยธรรม
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            ❓ ประชาชนสามารถเข้าถึงข้อมูลได้อย่างไร?
                        </h3>
                        <p class="text-gray-600">
                            ประชาชนสามารถเข้าถึงข้อมูลต่าง ๆ ได้ผ่านเว็บไซต์ของโรงพยาบาล 
                            ซึ่งมีการเผยแพร่ข้อมูลตามเกณฑ์ที่กำหนด รวมถึงข้อมูลการจัดซื้อจัดจ้าง 
                            แผนปฏิบัติการ รายงานผลการดำเนินงาน และช่องทางการติดต่อต่าง ๆ
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            ❓ หากมีข้อร้องเรียนจะติดต่ออย่างไร?
                        </h3>
                        <p class="text-gray-600">
                            ท่านสามารถร้องเรียนได้หลายช่องทาง เช่น โทรศัพท์ 053-580-xxx, 
                            อีเมล info@thchospital.go.th, เว็บไซต์ของโรงพยาบาล หรือกล่องรับความคิดเห็น 
                            โรงพยาบาลมีระบบการจัดการเรื่องร้องเรียนที่เป็นระบบและโปร่งใส
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            ❓ No Gift Policy คืออะไร?
                        </h3>
                        <p class="text-gray-600">
                            No Gift Policy เป็นนโยบายที่เจ้าหน้าที่ของรัฐทุกคนไม่รับของขวัญและของกำนัล
                            ทุกชนิดจากการปฏิบัติหน้าที่ เพื่อป้องกันการเกิดผลประโยชน์ทับซ้อนและสร้าง
                            ความโปร่งใสในการปฏิบัติงาน
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Dashboard -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">
                สถิติการดำเนินงาน MOPH ITA
            </h2>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-6 text-white text-center">
                    <div class="text-3xl font-bold mb-2">22</div>
                    <div class="text-sm opacity-90">เกณฑ์การประเมิน</div>
                    <div class="text-xs opacity-75 mt-1">MOIT 1-22</div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-6 text-white text-center">
                    <div class="text-3xl font-bold mb-2">4</div>
                    <div class="text-sm opacity-90">ไตรมาสรายงาน</div>
                    <div class="text-xs opacity-75 mt-1">ตลอดปีงบประมาณ</div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white text-center">
                    <div class="text-3xl font-bold mb-2">100%</div>
                    <div class="text-sm opacity-90">ความโปร่งใส</div>
                    <div class="text-xs opacity-75 mt-1">เป้าหมายการดำเนินงาน</div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-6 text-white text-center">
                    <div class="text-3xl font-bold mb-2">24/7</div>
                    <div class="text-sm opacity-90">ช่องทางติดต่อ</div>
                    <div class="text-xs opacity-75 mt-1">พร้อมให้บริการ</div>
                </div>
            </div>

            <!-- Progress Chart Placeholder -->
            <div class="mt-12">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h3 class="text-xl font-semibold text-center mb-6 text-gray-800">
                        ความคืบหน้าการดำเนินงานตามเกณฑ์ MOIT
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Progress bars -->
                        <div class="flex items-center space-x-4">
                            <div class="w-32 text-sm font-medium">ข้อมูลพื้นฐาน</div>
                            <div class="flex-1 bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full" style="width: 90%"></div>
                            </div>
                            <div class="w-12 text-sm text-gray-600">90%</div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="w-32 text-sm font-medium">การจัดซื้อจัดจ้าง</div>
                            <div class="flex-1 bg-gray-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full" style="width: 85%"></div>
                            </div>
                            <div class="w-12 text-sm text-gray-600">85%</div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="w-32 text-sm font-medium">บริหารบุคลากร</div>
                            <div class="flex-1 bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-500 h-3 rounded-full" style="width: 88%"></div>
                            </div>
                            <div class="w-12 text-sm text-gray-600">88%</div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="w-32 text-sm font-medium">ป้องกันทุจริต</div>
                            <div class="flex-1 bg-gray-200 rounded-full h-3">
                                <div class="bg-red-500 h-3 rounded-full" style="width: 92%"></div>
                            </div>
                            <div class="w-12 text-sm text-gray-600">92%</div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="w-32 text-sm font-medium">จริยธรรม</div>
                            <div class="flex-1 bg-gray-200 rounded-full h-3">
                                <div class="bg-indigo-500 h-3 rounded-full" style="width: 95%"></div>
                            </div>
                            <div class="w-12 text-sm text-gray-600">95%</div>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <div class="inline-flex items-center space-x-2 bg-green-100 text-green-800 px-4 py-2 rounded-full">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-sm font-medium">ความคืบหน้ารวม: 90%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">ร่วมสร้างองค์กรที่มีคุณธรรม</h2>
            <p class="text-xl mb-8 opacity-90">
                โรงพยาบาลทุ่งหัวช้างมุ่งมั่นดำเนินงานด้วยความโปร่งใสและมีคุณธรรม
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="contact.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    📞 ติดต่อสอบถาม
                </a>
                <a href="?page_id=2183" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">
                    💬 ช่องทางร้องเรียน
                </a>
            </div>
        </div>
    </section>
</main>

<style>
/* Additional styles for ITA page */
.prose {
    line-height: 1.7;
}

.hover\:shadow-xl:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Animation for progress bars */
@keyframes progressAnimation {
    from { width: 0%; }
}

.bg-blue-500, .bg-green-500, .bg-purple-500, .bg-red-500, .bg-indigo-500 {
    animation: progressAnimation 2s ease-out;
}

/* Timeline styles */
.timeline-line {
    background: linear-gradient(to bottom, #3B82F6, #10B981, #F59E0B, #8B5CF6);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .timeline-line {
        left: 2rem;
    }
    
    .timeline-item {
        padding-left: 4rem;
    }
}

/* Print styles */
@media print {
    .bg-gradient-to-r,
    .bg-gradient-to-br {
        background: #f8f9fa !important;
        color: #333 !important;
    }
    
    .shadow-lg {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>

<?php
// Helper function for formatting Thai dates (if not already defined)
if (!function_exists('formatThaiDate')) {
    function formatThaiDate($date) {
        if (!$date) return '';
        
        $thaiMonths = array(
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        );
        
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = $thaiMonths[(int)date('n', $timestamp)];
        $year = date('Y', $timestamp) + 543;
        
        return "$day $month $year";
    }
}

// Helper function for logging errors (if not already defined)
if (!function_exists('logError')) {
    function logError($message, $file, $line) {
        error_log("Error in $file at line $line: $message");
    }
}
?>

<?php include 'includes/footer.php'; ?>