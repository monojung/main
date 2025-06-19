<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โรงพยาบาลทุ่งหัวช้าง - จังหวัดลำพูน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .fade-in { animation: fadeIn 0.6s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .parallax { background-attachment: fixed; background-position: center; background-repeat: no-repeat; background-size: cover; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xl">THC</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">โรงพยาบาลทุ่งหัวช้าง</h1>
                        <p class="text-sm text-gray-600">จังหวัดลำพูน</p>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">หน้าหลัก</a>
                    <a href="#about" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">เกี่ยวกับเรา</a>
                    <a href="#services" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">บริการ</a>
                    <a href="#doctors" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">แพทย์</a>
                    <a href="#news" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">ข่าวสาร</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">ติดต่อ</a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden">
                    <button id="mobile-menu-btn" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="lg:hidden hidden pb-4">
                <a href="#home" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">หน้าหลัก</a>
                <a href="#about" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">เกี่ยวกับเรา</a>
                <a href="#services" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">บริการ</a>
                <a href="#doctors" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">แพทย์</a>
                <a href="#news" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">ข่าวสาร</a>
                <a href="#contact" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">ติดต่อ</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="gradient-bg text-white py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
            <div class="fade-in">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">โรงพยาบาลทุ่งหัวช้าง</h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">จังหวัดลำพูน</p>
                <p class="text-lg mb-8 max-w-2xl mx-auto opacity-80">ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน มีเทคโนลジีทางการแพทย์ที่ทันสมัย</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#services" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transform hover:scale-105 transition duration-300">ดูบริการของเรา</a>
                    <a href="#contact" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transform hover:scale-105 transition duration-300">ติดต่อเรา</a>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-20 h-20 bg-white opacity-10 rounded-full animate-bounce"></div>
        <div class="absolute bottom-20 right-10 w-16 h-16 bg-white opacity-10 rounded-full animate-bounce" style="animation-delay: 1s;"></div>
    </section>

    <!-- Quick Info Cards -->
    <section class="py-16 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-blue-600 text-5xl mb-4">🏥</div>
                    <h3 class="text-xl font-semibold mb-3">เวลาทำการ</h3>
                    <p class="text-gray-600 mb-2">จันทร์ - ศุกร์: 08:00 - 16:30</p>
                    <p class="text-gray-600">เสาร์ - อาทิตย์: 08:00 - 12:00</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-green-600 text-5xl mb-4">📞</div>
                    <h3 class="text-xl font-semibold mb-3">ติดต่อเรา</h3>
                    <p class="text-gray-600 mb-2">053-580-100 (สายหลัก)</p>
                    <p class="text-gray-600">053-580-999 (ฉุกเฉิน)</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-red-600 text-5xl mb-4">🚨</div>
                    <h3 class="text-xl font-semibold mb-3">ฉุกเฉิน 24 ชม.</h3>
                    <p class="text-gray-600 mb-2">แผนกอุบัติเหตุและฉุกเฉิน</p>
                    <p class="text-gray-600">เปิดบริการตลอด 24 ชั่วโมง</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">เกี่ยวกับเรา</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">โรงพยาบาลที่มุ่งมั่นในการให้บริการด้านสุขภาพที่มีคุณภาพ</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-2xl font-bold mb-6 text-gray-800">ประวัติโรงพยาบาล</h3>
                    <div class="space-y-4 text-gray-700">
                        <p>โรงพยาบาลทุ่งหัวช้าง เป็นโรงพยาบาลประจำตำบลที่ให้บริการด้านการแพทย์และสาธารณสุขแก่ประชาชนในพื้นที่ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน</p>
                        
                        <p>ก่อตั้งขึ้นเมื่อปี พ.ศ. 2540 ด้วยเจตนารมณ์ในการยกระดับคุณภาพการบริการสุขภาพให้แก่ประชาชนในพื้นที่ให้ได้รับการดูแลรักษาที่มีคุณภาพ สะดวก และเข้าถึงได้</p>

                        <p>ปัจจุบันเรามีทีมแพทย์และเจ้าหน้าที่ที่มีความเชี่ยวชาญในสาขาต่างๆ พร้อมด้วยเครื่องมือทางการแพทย์ที่ทันสมัย เพื่อให้บริการที่ดีที่สุดแก่ผู้ป่วย</p>
                    </div>
                </div>
                <div class="bg-gray-200 rounded-xl h-96 flex items-center justify-center">
                    <div class="text-center text-gray-600">
                        <div class="text-6xl mb-4">🏥</div>
                        <p class="text-xl font-semibold">โรงพยาบาลทุ่งหัวช้าง</p>
                        <p class="text-sm">อาคารโรงพยาบาล</p>
                    </div>
                </div>
            </div>

            <!-- Values -->
            <div class="mt-20">
                <h3 class="text-3xl font-bold text-center mb-12 text-gray-800">คุณค่าองค์กร</h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div class="text-center p-6 bg-blue-50 rounded-xl card-hover">
                        <div class="text-4xl mb-4">💚</div>
                        <h4 class="font-semibold mb-2">ใส่ใจ</h4>
                        <p class="text-sm text-gray-600">ใส่ใจในทุกการบริการ</p>
                    </div>
                    <div class="text-center p-6 bg-green-50 rounded-xl card-hover">
                        <div class="text-4xl mb-4">🤝</div>
                        <h4 class="font-semibold mb-2">ไว้วางใจ</h4>
                        <p class="text-sm text-gray-600">สร้างความไว้วางใจ</p>
                    </div>
                    <div class="text-center p-6 bg-yellow-50 rounded-xl card-hover">
                        <div class="text-4xl mb-4">⚡</div>
                        <h4 class="font-semibold mb-2">รวดเร็ว</h4>
                        <p class="text-sm text-gray-600">บริการที่รวดเร็ว</p>
                    </div>
                    <div class="text-center p-6 bg-purple-50 rounded-xl card-hover">
                        <div class="text-4xl mb-4">✨</div>
                        <h4 class="font-semibold mb-2">คุณภาพ</h4>
                        <p class="text-sm text-gray-600">มาตรฐานสูง</p>
                    </div>
                    <div class="text-center p-6 bg-red-50 rounded-xl card-hover">
                        <div class="text-4xl mb-4">🎯</div>
                        <h4 class="font-semibold mb-2">มุ่งมั่น</h4>
                        <p class="text-sm text-gray-600">มุ่งมั่นพัฒนา</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-blue-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">บริการของเรา</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">บริการทางการแพทย์ที่ครอบคลุมและมีคุณภาพ</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-5xl mb-4">👩‍⚕️</div>
                    <h3 class="text-xl font-semibold mb-3">แพทย์ทั่วไป</h3>
                    <p class="text-gray-600 mb-4">บริการตรวจรักษาโรคทั่วไป</p>
                    <p class="text-xs text-gray-500">📍 ชั้น 1 อาคารผู้ป่วยนอก</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-5xl mb-4">👶</div>
                    <h3 class="text-xl font-semibold mb-3">กุมารเวชกรรม</h3>
                    <p class="text-gray-600 mb-4">บริการตรวจรักษาเด็กและวัยรุ่น</p>
                    <p class="text-xs text-gray-500">📍 ชั้น 1 อาคารผู้ป่วยนอก</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-5xl mb-4">🤱</div>
                    <h3 class="text-xl font-semibold mb-3">สูติ-นรีเวชกรรม</h3>
                    <p class="text-gray-600 mb-4">การดูแลสุขภาพสตรี การฝากครรภ์</p>
                    <p class="text-xs text-gray-500">📍 ชั้น 2 อาคารผู้ป่วยใน</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-5xl mb-4">🦴</div>
                    <h3 class="text-xl font-semibold mb-3">ออร์โธปิดิกส์</h3>
                    <p class="text-gray-600 mb-4">การรักษากระดูกและข้อ</p>
                    <p class="text-xs text-gray-500">📍 ชั้น 2 อาคารผู้ป่วยนอก</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-5xl mb-4">🦷</div>
                    <h3 class="text-xl font-semibold mb-3">ทันตกรรม</h3>
                    <p class="text-gray-600 mb-4">การรักษาฟัน การขูดหินปูน</p>
                    <p class="text-xs text-gray-500">📍 ชั้น 1 อาคารผู้ป่วยนอก</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-5xl mb-4">🚑</div>
                    <h3 class="text-xl font-semibold mb-3">แผนกฉุกเฉิน</h3>
                    <p class="text-gray-600 mb-4">บริการฉุกเฉินตลอด 24 ชั่วโมง</p>
                    <p class="text-xs text-gray-500">📍 ชั้น 1 อาคารฉุกเฉิน</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-5xl mb-4">🔬</div>
                    <h3 class="text-xl font-semibold mb-3">ห้องปฏิบัติการ</h3>
                    <p class="text-gray-600 mb-4">ตรวจวิเคราะห์ทางห้องปฏิบัติการ</p>
                    <p class="text-xs text-gray-500">📍 ชั้น 1 อาคารบริการ</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="text-5xl mb-4">💊</div>
                    <h3 class="text-xl font-semibold mb-3">ร้านยา</h3>
                    <p class="text-gray-600 mb-4">บริการจ่ายยาและให้คำปรึกษา</p>
                    <p class="text-xs text-gray-500">📍 ชั้น 1 อาคารบริการ</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section id="doctors" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">ทีมแพทย์</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">ทีมแพทย์ผู้เชี่ยวชาญและเจ้าหน้าที่มืออาชีพ</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="w-24 h-24 bg-blue-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">👨‍⚕️</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">นพ.สมชาย ใจดี</h3>
                    <p class="text-blue-600 font-medium mb-2">อายุรศาสตร์ทั่วไป</p>
                    <p class="text-sm text-gray-600">แพทย์ประจำแผนกอายุรกรรม</p>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="w-24 h-24 bg-green-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">👨‍⚕️</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">นพ.วิชัย รักษาดี</h3>
                    <p class="text-green-600 font-medium mb-2">กุมารเวชกรรม</p>
                    <p class="text-sm text-gray-600">แพทย์ประจำแผนกเด็ก</p>
                </div>

                <div class="bg-gradient-to-br from-pink-50 to-rose-100 p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="w-24 h-24 bg-pink-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">👩‍⚕️</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">พญ.สุนีย์ เอาใจใส่</h3>
                    <p class="text-pink-600 font-medium mb-2">สูติ-นรีเวชกรรม</p>
                    <p class="text-sm text-gray-600">แพทย์ประจำแผนกสูติ-นรีเวช</p>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-violet-100 p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="w-24 h-24 bg-purple-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">👨‍⚕️</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">นพ.ประชา ช่วยเหลือ</h3>
                    <p class="text-purple-600 font-medium mb-2">ศัลยกรรมทั่วไป</p>
                    <p class="text-sm text-gray-600">แพทย์ประจำแผนกศัลยกรรม</p>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-amber-100 p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="w-24 h-24 bg-orange-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">👨‍⚕️</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">นพ.อานนท์ บำบัดดี</h3>
                    <p class="text-orange-600 font-medium mb-2">ออร์โธปิดิกส์</p>
                    <p class="text-sm text-gray-600">แพทย์ประจำแผนกกระดูก</p>
                </div>

                <div class="bg-gradient-to-br from-teal-50 to-cyan-100 p-8 rounded-xl shadow-lg text-center card-hover">
                    <div class="w-24 h-24 bg-teal-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-3xl">👩‍⚕️</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">ทพ.มาลัย ยิ้มสวย</h3>
                    <p class="text-teal-600 font-medium mb-2">ทันตกรรมทั่วไป</p>
                    <p class="text-sm text-gray-600">แพทย์ประจำแผนกทันตกรรม</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-20 gradient-bg text-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">ตัวเลขของเรา</h2>
                <p class="text-xl opacity-90">ผลงานที่เราภาคภูมิใจ</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-5xl mb-4">🎯</div>
                    <div class="text-4xl font-bold mb-2">25+</div>
                    <div class="text-lg opacity-90">ปีแห่งการให้บริการ</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl mb-4">👨‍⚕️</div>
                    <div class="text-4xl font-bold mb-2">50+</div>
                    <div class="text-lg opacity-90">บุคลากรทางการแพทย์</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl mb-4">👥</div>
                    <div class="text-4xl font-bold mb-2">10,000+</div>
                    <div class="text-lg opacity-90">ผู้ป่วยต่อปี</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl mb-4">🏥</div>
                    <div class="text-4xl font-bold mb-2">8</div>
                    <div class="text-lg opacity-90">แผนกบริการ</div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">ข่าวสารและประกาศ</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">ติดตามข่าวสารและความเคลื่อนไหวของโรงพยาบาล</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="text-sm text-blue-600 font-semibold mb-2">ประกาศ</div>
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">ประกาศจัดซื้อระบบ IPD Paperless</h3>
                        <p class="text-gray-600 text-sm mb-4">ประกาศผู้ชนะการเสนอราคาจัดซื้อระบบบริหารจัดการผู้ป่วยใน</p>
                        <p class="text-gray-500 text-xs">15 มิถุนายน 2568</p>
                        <div class="mt-4">
                            <button class="text-blue-600 hover:text-blue-800 font-medium text-sm">อ่านเพิ่มเติม →</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="text-sm text-green-600 font-semibold mb-2">บริการ</div>
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">บริการฉีดวัคซีนไข้หวัดใหญ่</h3>
                        <p class="text-gray-600 text-sm mb-4">เปิดให้บริการฉีดวัคซีนป้องกันไข้หวัดใหญ่ ประจำปี 2568</p>
                        <p class="text-gray-500 text-xs">1 มิถุนายน 2568</p>
                        <div class="mt-4">
                            <button class="text-blue-600 hover:text-blue-800 font-medium text-sm">อ่านเพิ่มเติม →</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="text-sm text-purple-600 font-semibold mb-2">จัดซื้อจัดจ้าง</div>
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">แผนจัดซื้อจัดจ้าง ปี 2568</h3>
                        <p class="text-gray-600 text-sm mb-4">เผยแพร่แผนจัดซื้อจัดจ้าง ประจำปีงบประมาณ 2568</p>
                        <p class="text-gray-500 text-xs">10 มิถุนายน 2568</p>
                        <div class="mt-4">
                            <button class="text-blue-600 hover:text-blue-800 font-medium text-sm">อ่านเพิ่มเติม →</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="text-sm text-red-600 font-semibold mb-2">สุขภาพ</div>
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">คำแนะนำการป้องกันโรคในหน้าฝน</h3>
                        <p class="text-gray-600 text-sm mb-4">แนวทางการดูแลสุขภาพและป้องกันโรคติดเชื้อในช่วงฝน</p>
                        <p class="text-gray-500 text-xs">5 มิถุนายน 2568</p>
                        <div class="mt-4">
                            <button class="text-blue-600 hover:text-blue-800 font-medium text-sm">อ่านเพิ่มเติม →</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="text-sm text-orange-600 font-semibold mb-2">กิจกรรม</div>
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">โครงการตรวจสุขภาพฟรี</h3>
                        <p class="text-gray-600 text-sm mb-4">จัดกิจกรรมตรวจสุขภาพฟรีสำหรับผู้สูงอายุในชุมชน</p>
                        <p class="text-gray-500 text-xs">25 พฤษภาคม 2568</p>
                        <div class="mt-4">
                            <button class="text-blue-600 hover:text-blue-800 font-medium text-sm">อ่านเพิ่มเติม →</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="text-sm text-indigo-600 font-semibold mb-2">ทั่วไป</div>
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">อัพเดทเวลาทำการช่วงเทศกาล</h3>
                        <p class="text-gray-600 text-sm mb-4">ประกาศปรับเวลาทำการในช่วงวันหยุดยาวเทศกาลสงกรานต์</p>
                        <p class="text-gray-500 text-xs">10 เมษายน 2568</p>
                        <div class="mt-4">
                            <button class="text-blue-600 hover:text-blue-800 font-medium text-sm">อ่านเพิ่มเติม →</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12">
                <button class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transform hover:scale-105 transition duration-300">ดูข่าวสารทั้งหมด</button>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">ติดต่อเรา</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">พร้อมให้บริการและตอบคำถามของคุณ</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Contact Information -->
                <div>
                    <h3 class="text-2xl font-bold mb-8 text-gray-800">ข้อมูลการติดต่อ</h3>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-6">
                        <h4 class="text-xl font-semibold mb-4 text-gray-800">🏥 ที่อยู่โรงพยาบาล</h4>
                        <p class="text-gray-700">
                            <strong>โรงพยาบาลทุ่งหัวช้าง</strong><br>
                            123 ถนนหลัก ตำบลทุ่งหัวช้าง<br>
                            อำเภอเมือง จังหวัดลำพูน 51000
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 text-xl">📞</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">โทรศัพท์หลัก</p>
                                <p class="text-gray-600">053-580-100</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4 p-4 bg-red-50 rounded-lg">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-600 text-xl">🚨</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">ฉุกเฉิน 24 ชม.</p>
                                <p class="text-gray-600">053-580-999</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="text-green-600 text-xl">📠</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">โทรสาร</p>
                                <p class="text-gray-600">053-580-110</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-purple-600 text-xl">📧</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">อีเมล</p>
                                <p class="text-gray-600">info@thchospital.go.th</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="text-green-600 text-xl">💬</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Line Official</p>
                                <p class="text-gray-600">@thchospital</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-8">
                        <h3 class="text-xl font-semibold mb-6 text-gray-800">✉️ ส่งข้อความถึงเรา</h3>
                        <form class="space-y-4" id="contactForm">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อ-นามสกุล</label>
                                    <input type="text" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">เบอร์โทรศัพท์</label>
                                    <input type="tel" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">อีเมล</label>
                                <input type="email" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">เรื่อง</label>
                                <select required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300">
                                    <option value="">เลือกหัวข้อ</option>
                                    <option value="general">สอบถามทั่วไป</option>
                                    <option value="service">สอบถามบริการ</option>
                                    <option value="complaint">ร้องเรียน/แสดงความคิดเห็น</option>
                                    <option value="suggestion">ข้อเสนอแนะ</option>
                                    <option value="other">อื่นๆ</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ข้อความ</label>
                                <textarea rows="5" required 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                                          placeholder="กรุณาใส่ข้อความที่ต้องการสื่อสาร"></textarea>
                            </div>

                            <button type="submit" 
                                    class="w-full gradient-bg text-white py-3 px-6 rounded-lg font-semibold hover:opacity-90 transform hover:scale-105 transition duration-300">
                                📤 ส่งข้อความ
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Operating Hours -->
            <div class="mt-20">
                <h3 class="text-3xl font-bold text-center mb-12 text-gray-800">🕒 เวลาทำการ</h3>
                <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 text-center">
                        <div class="text-4xl mb-4">🏥</div>
                        <h4 class="text-xl font-semibold mb-3 text-gray-800">เวลาทำการปกติ</h4>
                        <div class="space-y-2 text-gray-600">
                            <p><strong>จันทร์ - ศุกร์</strong></p>
                            <p>08:00 - 16:30</p>
                            <p><strong>เสาร์ - อาทิตย์</strong></p>
                            <p>08:00 - 12:00</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 text-center border-2 border-red-200">
                        <div class="text-4xl mb-4">🚨</div>
                        <h4 class="text-xl font-semibold mb-3 text-red-800">แผนกฉุกเฉิน</h4>
                        <div class="space-y-2 text-red-700">
                            <p><strong>เปิดตลอด 24 ชั่วโมง</strong></p>
                            <p>ทุกวัน รวมวันหยุด</p>
                            <p class="text-sm">พร้อมบริการฉุกเฉิน</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 text-center">
                        <div class="text-4xl mb-4">💊</div>
                        <h4 class="text-xl font-semibold mb-3 text-gray-800">ร้านยา</h4>
                        <div class="space-y-2 text-gray-600">
                            <p><strong>จันทร์ - ศุกร์</strong></p>
                            <p>08:00 - 16:30</p>
                            <p><strong>เสาร์ - อาทิตย์</strong></p>
                            <p>08:00 - 12:00</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="mt-20">
                <h3 class="text-3xl font-bold text-center mb-12 text-gray-800">🗺️ แผนที่</h3>
                <div class="bg-gray-200 rounded-xl h-96 flex items-center justify-center">
                    <div class="text-center text-gray-600">
                        <div class="text-6xl mb-4">📍</div>
                        <p class="text-xl font-semibold mb-2">แผนที่โรงพยาบาลทุ่งหัวช้าง</p>
                        <p class="text-sm mb-2">123 ถนนหลัก ตำบลทุ่งหัวช้าง</p>
                        <p class="text-sm mb-4">อำเภอเมือง จังหวัดลำพูน 51000</p>
                        <button class="gradient-bg text-white px-6 py-2 rounded-lg text-sm hover:opacity-90 transition duration-300">
                            เปิดใน Google Maps
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Emergency Banner -->
    <section class="bg-gradient-to-r from-red-500 to-red-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="text-5xl mr-6 animate-pulse">🚨</div>
                    <div>
                        <h3 class="text-2xl font-bold mb-2">สำหรับกรณีฉุกเฉิน</h3>
                        <p class="opacity-90">โทรหาเราได้ตลอด 24 ชั่วโมง</p>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <div class="text-4xl font-bold mb-2">1669</div>
                    <div class="text-lg opacity-90">หมายเลขฉุกเฉิน</div>
                    <div class="text-sm opacity-80 mt-1">หรือ 053-580-999</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Hospital Info -->
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-xl">THC</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">โรงพยาบาลทุ่งหัวช้าง</h3>
                            <p class="text-sm text-gray-300">จังหวัดลำพูน</p>
                        </div>
                    </div>
                    <p class="text-gray-300 text-sm mb-4">ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน</p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition duration-300">
                            <span class="text-sm">f</span>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center hover:bg-blue-500 transition duration-300">
                            <span class="text-sm">@</span>
                        </a>
                        <a href="#" class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center hover:bg-green-600 transition duration-300">
                            <span class="text-sm">L</span>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">เมนูหลัก</h4>
                    <ul class="space-y-2">
                        <li><a href="#about" class="text-gray-300 hover:text-white transition duration-300">เกี่ยวกับเรา</a></li>
                        <li><a href="#services" class="text-gray-300 hover:text-white transition duration-300">บริการ</a></li>
                        <li><a href="#doctors" class="text-gray-300 hover:text-white transition duration-300">ทีมแพทย์</a></li>
                        <li><a href="#news" class="text-gray-300 hover:text-white transition duration-300">ข่าวสาร</a></li>
                        <li><a href="#contact" class="text-gray-300 hover:text-white transition duration-300">ติดต่อเรา</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">บริการ</h4>
                    <ul class="space-y-2">
                        <li><a href="#services" class="text-gray-300 hover:text-white transition duration-300">แผนกผู้ป่วยนอก</a></li>
                        <li><a href="#services" class="text-gray-300 hover:text-white transition duration-300">แผนกผู้ป่วยใน</a></li>
                        <li><a href="#services" class="text-gray-300 hover:text-white transition duration-300">แผนกฉุกเฉิน</a></li>
                        <li><a href="#services" class="text-gray-300 hover:text-white transition duration-300">ห้องปฏิบัติการ</a></li>
                        <li><a href="#services" class="text-gray-300 hover:text-white transition duration-300">ร้านยา</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">ติดต่อเรา</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start space-x-2">
                            <span class="text-blue-400">📍</span>
                            <div>
                                <p class="text-gray-300">123 ถนนหลัก ตำบลทุ่งหัวช้าง</p>
                                <p class="text-gray-300">อำเภอเมือง จังหวัดลำพูน 51000</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-blue-400">📞</span>
                            <span class="text-gray-300">053-580-100</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-blue-400">📠</span>
                            <span class="text-gray-300">053-580-110</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-blue-400">📧</span>
                            <span class="text-gray-300">info@thchospital.go.th</span>
                        </div>
                    </div>
                    
                    <!-- Operating Hours -->
                    <div class="mt-6 p-4 bg-gray-700 rounded-lg">
                        <h5 class="font-semibold text-sm mb-2">เวลาทำการ</h5>
                        <div class="text-xs space-y-1">
                            <p class="text-gray-300">จันทร์ - ศุกร์: 08:00 - 16:30</p>
                            <p class="text-gray-300">เสาร์ - อาทิตย์: 08:00 - 12:00</p>
                            <p class="text-red-300">ฉุกเฉิน: 24 ชั่วโมง</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-700 pt-8 mt-12">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-sm text-gray-400">
                        <p>&copy; 2025 โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน. สงวนสิทธิ์ทุกประการ.</p>
                    </div>
                    <div class="flex space-x-6 mt-4 md:mt-0 text-sm">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">นโยบายความเป็นส่วนตัว</a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">ข้อกำหนดการใช้งาน</a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">แผนผังเว็บไซต์</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-6 right-6 gradient-bg text-white p-4 rounded-full shadow-lg hover:opacity-90 transform hover:scale-110 transition duration-300 opacity-0 invisible">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    document.getElementById('mobile-menu').classList.add('hidden');
                }
            });
        });

        // Back to top functionality
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.classList.remove('opacity-0', 'invisible');
                backToTop.classList.add('opacity-100', 'visible');
            } else {
                backToTop.classList.add('opacity-0', 'invisible');
                backToTop.classList.remove('opacity-100', 'visible');
            }
        });

        document.getElementById('back-to-top').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Contact form handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show success message
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Loading state
            submitBtn.innerHTML = '⏳ กำลังส่ง...';
            submitBtn.disabled = true;
            
            // Simulate form submission
            setTimeout(() => {
                alert('✅ ส่งข้อความเรียบร้อยแล้ว! เราจะติดต่อกลับภายใน 24 ชั่วโมง');
                this.reset();
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });

        // Add scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe all cards and sections
        document.querySelectorAll('.card-hover, section > div').forEach(el => {
            observer.observe(el);
        });

        // News card click handlers
        document.querySelectorAll('#news .card-hover').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('h3').textContent;
                alert(`📰 ข่าว: ${title}\n\nระบบแสดงรายละเอียดข่าวจะพัฒนาต่อไป`);
            });
        });

        // Add typing effect to hero text
        function typeWriter(element, text, speed = 100) {
            let i = 0;
            element.innerHTML = '';
            
            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }

        // Initialize typing effect when page loads
        window.addEventListener('load', function() {
            const heroTitle = document.querySelector('#home h1');
            if (heroTitle) {
                const originalText = heroTitle.textContent;
                setTimeout(() => {
                    typeWriter(heroTitle, originalText, 80);
                }, 1000);
            }
        });

        // Add floating animation to hero elements
        function addFloatingAnimation() {
            const floatingElements = document.querySelectorAll('#home .absolute');
            floatingElements.forEach((el, index) => {
                el.style.animation = `float ${3 + index}s ease-in-out infinite`;
            });
        }

        // CSS animation for floating effect
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            
            .animate-float {
                animation: float 3s ease-in-out infinite;
            }
            
            .animate-float:nth-child(2) {
                animation-delay: 1s;
            }
        `;
        document.head.appendChild(style);

        // Initialize floating animation
        addFloatingAnimation();

        // Add counter animation for statistics
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            
            function updateCounter() {
                start += increment;
                if (start < target) {
                    element.textContent = Math.ceil(start) + '+';
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target + '+';
                }
            }
            updateCounter();
        }

        // Trigger counter animation when statistics section is visible
        const statsObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.text-4xl.font-bold');
                    counters.forEach(counter => {
                        const text = counter.textContent;
                        const number = parseInt(text.replace(/\D/g, ''));
                        if (number && !counter.classList.contains('animated')) {
                            counter.classList.add('animated');
                            animateCounter(counter, number);
                        }
                    });
                }
            });
        }, { threshold: 0.5 });

        // Observe statistics section
        const statsSection = document.querySelector('.gradient-bg');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }

        // Add hover effects for service cards
        document.querySelectorAll('#services .card-hover').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
                this.style.boxShadow = '0 25px 50px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
            });
        });

        // Add click effects for buttons
        document.querySelectorAll('button, a.bg-').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Create ripple effect
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255,255,255,0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add ripple animation CSS
        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            @keyframes ripple {
                to { transform: scale(2); opacity: 0; }
            }
        `;
        document.head.appendChild(rippleStyle);

        // Lazy loading for images (if any were added)
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Add search functionality (for future implementation)
        function addSearchFunctionality() {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'ค้นหาบริการ...';
            searchInput.className = 'px-4 py-2 border rounded-lg';
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const serviceCards = document.querySelectorAll('#services .card-hover');
                
                serviceCards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = searchTerm ? 'none' : 'block';
                    }
                });
            });
        }

        // Initialize all animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add stagger animation to cards
            const cards = document.querySelectorAll('.card-hover');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });

            // Add progress bar for page scroll
            const progressBar = document.createElement('div');
            progressBar.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 0%;
                height: 3px;
                background: linear-gradient(90deg, #667eea, #764ba2);
                z-index: 9999;
                transition: width 0.3s ease;
            `;
            document.body.appendChild(progressBar);

            window.addEventListener('scroll', () => {
                const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                progressBar.style.width = scrolled + '%';
            });
        });

        // Console message for developers
        console.log(`
🏥 โรงพยาบาลทุ่งหัวช้าง
✨ เว็บไซต์พัฒนาด้วย HTML, CSS, JavaScript
🎨 ดีไซน์: Modern & Responsive
📱 รองรับ: Mobile, Tablet, Desktop
        `);
    </script>
</body>
</html>