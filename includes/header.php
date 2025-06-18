<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน</title>
    <meta name="description" content="โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .line-clamp-3 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Top Bar -->
    <div class="bg-blue-800 text-white py-2">
        <div class="container mx-auto px-4 flex flex-col sm:flex-row justify-between items-center text-sm">
            <div class="flex items-center space-x-4">
                <span>📞 053-975-201</span>
                <span>📧 info@thchospital.go.th</span>
            </div>
            <div class="flex items-center space-x-4 mt-2 sm:mt-0">
                <span>🕒 จันทร์-ศุกร์ 08:00-16:30</span>
                <span class="text-red-300">🚨 ฉุกเฉิน 24 ชม.</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xl">THC</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">โรงพยาบาลทุ่งหัวช้าง</h1>
                        <p class="text-sm text-gray-600">จังหวัดลำพูน</p>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">หน้าหลัก</a>
                    <div class="relative group">
                        <a href="about.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300 flex items-center">
                            เกี่ยวกับเรา
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                            <div class="py-2">
                                <a href="about.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600">ประวัติโรงพยาบาล</a>
                                <a href="management.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600">ผู้บริหาร</a>
                                <a href="organization.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600">โครงสร้างองค์กร</a>
                            </div>
                        </div>
                    </div>
                    <div class="relative group">
                        <a href="services.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300 flex items-center">
                            บริการ
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                            <div class="py-2">
                                <a href="services.php#outpatient" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600">ผู้ป่วยนอก</a>
                                <a href="services.php#inpatient" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600">ผู้ป่วยใน</a>
                                <a href="services.php#emergency" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600">ฉุกเฉิน</a>
                                <a href="services.php#lab" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600">ห้องปฏิบัติการ</a>
                            </div>
                        </div>
                    </div>
                    <a href="news.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">ข่าวสาร</a>
                    <a href="appointment.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">นัดหมาย</a>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">ติดต่อ</a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="lg:hidden hidden pb-4">
                <a href="index.php" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">หน้าหลัก</a>
                <a href="about.php" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">เกี่ยวกับเรา</a>
                <a href="services.php" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">บริการ</a>
                <a href="news.php" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">ข่าวสาร</a>
                <a href="appointment.php" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">นัดหมาย</a>
                <a href="contact.php" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">ติดต่อ</a>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>