<!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Hospital Info -->
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold">THC</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">โรงพยาบาลทุ่งหัวช้าง</h3>
                            <p class="text-sm text-gray-300">จังหวัดลำพูน</p>
                        </div>
                    </div>
                    <p class="text-gray-300 text-sm mb-4">ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน</p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition duration-300">
                            <span class="text-sm">f</span>
                        </a>
                        <a href="#" class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition duration-300">
                            <span class="text-sm">@</span>
                        </a>
                        <a href="#" class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition duration-300">
                            <span class="text-sm">L</span>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">เมนูหลัก</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-300 hover:text-white transition duration-300">เกี่ยวกับเรา</a></li>
                        <li><a href="services.php" class="text-gray-300 hover:text-white transition duration-300">บริการ</a></li>
                        <li><a href="news.php" class="text-gray-300 hover:text-white transition duration-300">ข่าวสาร</a></li>
                        <li><a href="contact.php" class="text-gray-300 hover:text-white transition duration-300">ติดต่อเรา</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">บริการ</h4>
                    <ul class="space-y-2">
                        <li><a href="services.php#outpatient" class="text-gray-300 hover:text-white transition duration-300">แผนกผู้ป่วยนอก</a></li>
                        <li><a href="services.php#inpatient" class="text-gray-300 hover:text-white transition duration-300">แผนกผู้ป่วยใน</a></li>
                        <li><a href="services.php#emergency" class="text-gray-300 hover:text-white transition duration-300">แผนกฉุกเฉิน</a></li>
                        <li><a href="services.php#lab" class="text-gray-300 hover:text-white transition duration-300">ห้องปฏิบัติการ</a></li>
                        <li><a href="services.php#pharmacy" class="text-gray-300 hover:text-white transition duration-300">ร้านยา</a></li>
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
                            <span class="text-gray-300">053-580-xxx</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-blue-400">📠</span>
                            <span class="text-gray-300">053-580-xxx</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-blue-400">📧</span>
                            <span class="text-gray-300">info@thchospital.go.th</span>
                        </div>
                    </div>
                    
                    <!-- Operating Hours -->
                    <div class="mt-4 p-3 bg-gray-700 rounded-lg">
                        <h5 class="font-semibold text-sm mb-2">เวลาทำการ</h5>
                        <div class="text-xs space-y-1">
                            <p class="text-gray-300">จันทร์ - ศุกร์: 08:00 - 16:00</p>
                            <p class="text-gray-300">เสาร์ - อาทิตย์: 08:00 - 12:00</p>
                            <p class="text-red-300">ฉุกเฉิน: 24 ชั่วโมง</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-700 pt-8 mt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-sm text-gray-400">
                        <p>&copy; <?php echo date('Y'); ?> โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน. สงวนสิทธิ์ทุกประการ. Powered by Fz</p>
                    </div>
                    <div class="flex space-x-6 mt-4 md:mt-0 text-sm">
                        <a href="privacy.php" class="text-gray-400 hover:text-white transition duration-300">นโยบายความเป็นส่วนตัว</a>
                        <a href="terms.php" class="text-gray-400 hover:text-white transition duration-300">ข้อกำหนดการใช้งาน</a>
                        <a href="sitemap.php" class="text-gray-400 hover:text-white transition duration-300">แผนผังเว็บไซต์</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-4 right-4 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition duration-300 opacity-0 invisible">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    <script>
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
    </script>
</body>
</html>