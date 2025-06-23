<?php 
$page_title = "หน้าหลัก";
require_once 'config/database.php';

// Get news, services, and statistics from database
$news = array();
$services = array();
$stats = array();
$doctors = array();

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        // Get featured news
        $stmt = $conn->prepare("
            SELECT title, excerpt, publish_date, category, slug, views
            FROM news 
            WHERE status = 'published' 
            AND (publish_date IS NULL OR publish_date <= NOW())
            ORDER BY is_featured DESC, is_urgent DESC, publish_date DESC 
            LIMIT 6
        ");
        $stmt->execute();
        $news = $stmt->fetchAll();
        
        // Get active departments for services
        $stmt = $conn->prepare("
            SELECT id, code, name, description, location, phone
            FROM departments 
            WHERE is_active = 1 
            ORDER BY display_order ASC, name ASC
        ");
        $stmt->execute();
        $departments = $stmt->fetchAll();
        
        // Map departments to services with proper icons
        $service_icons = array(
            'INT' => '🩺', // Internal Medicine
            'PED' => '👶', // Pediatrics
            'OBS' => '🤱', // Obstetrics & Gynecology
            'SUR' => '🏥', // Surgery
            'ORT' => '🦴', // Orthopedics
            'DEN' => '🦷', // Dentistry
            'EMR' => '🚑', // Emergency
            'LAB' => '🔬', // Laboratory
            'RAD' => '📻', // Radiology
            'PHA' => '💊', // Pharmacy
            'OPH' => '👁️', // Ophthalmology
            'ENT' => '👂', // ENT
            'DER' => '🧴', // Dermatology
            'PSY' => '🧠', // Psychiatry
            'REH' => '🏃', // Rehabilitation
            'CAR' => '❤️'  // Cardiology
        );
        
        foreach ($departments as $dept) {
            $services[] = array(
                'id' => $dept['id'],
                'name' => $dept['name'],
                'icon' => isset($service_icons[$dept['code']]) ? $service_icons[$dept['code']] : '🏥',
                'desc' => $dept['description'] ? $dept['description'] : 'บริการ' . $dept['name'],
                'location' => $dept['location'],
                'phone' => $dept['phone']
            );
        }
        
        // Get statistics
        $current_year = date('Y');
        $current_month = date('Y-m');
        
        // Total patients
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM patients WHERE is_active = 1");
        $stmt->execute();
        $total_patients = $stmt->fetch()['count'];
        
        // Total doctors
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM doctors WHERE is_active = 1");
        $stmt->execute();
        $total_doctors = $stmt->fetch()['count'];
        
        // Total departments
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM departments WHERE is_active = 1");
        $stmt->execute();
        $total_departments = $stmt->fetch()['count'];
        
        // Monthly visits (if visits table exists)
        try {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM visits 
                WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?
            ");
            $stmt->execute([$current_month]);
            $monthly_visits = $stmt->fetch()['count'];
        } catch (Exception $e) {
            $monthly_visits = 0;
        }
        
        // Total users/staff
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        $stmt->execute();
        $total_staff = $stmt->fetch()['count'];
        
        // Total news
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
        $stmt->execute();
        $total_news = $stmt->fetch()['count'];
        
        $stats = array(
            array('number' => number_format($monthly_visits), 'label' => 'การรักษาเดือนนี้', 'icon' => '📋'),
            array('number' => number_format($total_patients), 'label' => 'ผู้ป่วยทั้งหมด', 'icon' => '👥'),
            array('number' => number_format($total_doctors), 'label' => 'แพทย์', 'icon' => '👨‍⚕️'),
            array('number' => number_format($total_departments), 'label' => 'แผนกบริการ', 'icon' => '🏥')
        );
        
        // Get featured doctors
        $stmt = $conn->prepare("
            SELECT d.*, dept.name as department_name
            FROM doctors d
            LEFT JOIN departments dept ON d.department_id = dept.id
            WHERE d.is_active = 1 AND d.is_featured = 1
            ORDER BY d.display_order ASC, d.first_name ASC
            LIMIT 4
        ");
        $stmt->execute();
        $doctors = $stmt->fetchAll();
        
        // Get hospital settings
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
        $stmt->execute();
        $settings_data = $stmt->fetchAll();
        
        $hospital_settings = array();
        foreach ($settings_data as $setting) {
            $hospital_settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
    }
} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
    
    // Fallback data if database fails
    $news = array(
        array(
            'title' => 'ข่าวสารจากโรงพยาบาล',
            'excerpt' => 'ติดตามข่าวสารและประกาศต่างๆ จากโรงพยาบาล',
            'publish_date' => date('Y-m-d'),
            'category' => 'general',
            'slug' => '#',
            'views' => 0
        )
    );
    
    $services = array(
        array('name' => 'แผนกผู้ป่วยใน', 'icon' => '🏨', 'desc' => 'บริการดูแลผู้ป่วยใน 24 ชั่วโมง'),
        array('name' => 'แผนกผู้ป่วยนอก', 'icon' => '👩‍⚕️', 'desc' => 'บริการตรวจรักษาผู้ป่วยนอก'),
        array('name' => 'แผนกฉุกเฉิน', 'icon' => '🚑', 'desc' => 'บริการฉุกเฉินตลอด 24 ชั่วโมง'),
        array('name' => 'ห้องปฏิบัติการ', 'icon' => '🔬', 'desc' => 'ตรวจวิเคราะห์ทางห้องปฏิบัติการ')
    );
    
    $stats = array(
        array('number' => '200+', 'label' => 'การรักษาเดือนนี้', 'icon' => '📋'),
        array('number' => '1,500+', 'label' => 'ผู้ป่วยทั้งหมด', 'icon' => '👥'),
        array('number' => '25+', 'label' => 'แพทย์', 'icon' => '👨‍⚕️'),
        array('number' => '8', 'label' => 'แผนกบริการ', 'icon' => '🏥')
    );
    
    $hospital_settings = array();
}

// Get hospital info with fallback
function getHospitalSetting($key, $default = '') {
    global $hospital_settings;
    return isset($hospital_settings[$key]) ? $hospital_settings[$key] : $default;
}

$hospital_name = getHospitalSetting('hospital_name', 'โรงพยาบาลทุ่งหัวช้าง');
$hospital_phone = getHospitalSetting('hospital_phone', '053-580-100');
$emergency_phone = getHospitalSetting('emergency_phone', '1669');
$hospital_email = getHospitalSetting('hospital_email', 'info@thchospital.go.th');
$working_hours_start = getHospitalSetting('working_hours_start', '08:00');
$working_hours_end = getHospitalSetting('working_hours_end', '16:30');
$weekend_hours_start = getHospitalSetting('weekend_hours_start', '08:00');
$weekend_hours_end = getHospitalSetting('weekend_hours_end', '12:00');

include 'includes/header.php'; 
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-green-600 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-4"><?php echo htmlspecialchars($hospital_name); ?></h1>
            <p class="text-xl md:text-2xl mb-8">จังหวัดลำพูน</p>
            <p class="text-lg mb-8 max-w-2xl mx-auto">ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="services.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">บริการของเรา</a>
                <a href="contact.php" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">ติดต่อเรา</a>
            </div>
        </div>
    </section>

    <!-- Quick Info Cards -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <div class="text-blue-600 text-4xl mb-4">🏥</div>
                    <h3 class="text-xl font-semibold mb-2">เวลาทำการ</h3>
                    <p class="text-gray-600">จันทร์ - ศุกร์: <?php echo $working_hours_start; ?> - <?php echo $working_hours_end; ?></p>
                    <p class="text-gray-600">เสาร์ - อาทิตย์: <?php echo $weekend_hours_start; ?> - <?php echo $weekend_hours_end; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <div class="text-green-600 text-4xl mb-4">📞</div>
                    <h3 class="text-xl font-semibold mb-2">ติดต่อเรา</h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($hospital_phone); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($emergency_phone); ?> (ฉุกเฉิน)</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <div class="text-red-600 text-4xl mb-4">🚨</div>
                    <h3 class="text-xl font-semibold mb-2">ฉุกเฉิน 24 ชม.</h3>
                    <p class="text-gray-600">แผนกอุบัติเหตุและฉุกเฉิน</p>
                    <p class="text-gray-600">เปิดบริการตลอด 24 ชั่วโมง</p>
                </div>
            </div>
        </div>
    </section>

    <!-- News & Announcements -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">ข่าวสารและประกาศ</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($news as $item): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <div class="text-sm text-blue-600 font-semibold">
                                <?php 
                                $category_names = array(
                                    'general' => 'ทั่วไป',
                                    'announcement' => 'ประกาศ', 
                                    'procurement' => 'จัดซื้อจัดจ้าง',
                                    'service' => 'บริการ',
                                    'health_tips' => 'สุขภาพ'
                                );
                                echo isset($category_names[$item['category']]) ? $category_names[$item['category']] : 'ทั่วไป';
                                ?>
                            </div>
                            <?php if (isset($item['views']) && $item['views'] > 0): ?>
                            <div class="text-xs text-gray-500">
                                👁️ <?php echo number_format($item['views']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 line-clamp-3">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </h3>
                        <?php if (!empty($item['excerpt'])): ?>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                            <?php echo htmlspecialchars($item['excerpt']); ?>
                        </p>
                        <?php endif; ?>
                        <p class="text-gray-600 text-sm">
                            📅 <?php echo formatThaiDate($item['publish_date']); ?>
                        </p>
                        <?php if ($item['slug'] !== '#'): ?>
                        <a href="news.php?slug=<?php echo urlencode($item['slug']); ?>" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">อ่านเพิ่มเติม →</a>
                        <?php else: ?>
                        <span class="inline-block mt-4 text-gray-400 font-medium">อ่านเพิ่มเติม →</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-12">
                <a href="news.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">ดูข่าวสารทั้งหมด</a>
            </div>
        </div>
    </section>

    <!-- Services Overview -->
    <section class="py-16 bg-blue-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">บริการหลักของเรา</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach(array_slice($services, 0, 8) as $service): ?>
                <div class="bg-white p-6 rounded-lg shadow-lg text-center hover:shadow-xl transition duration-300">
                    <div class="text-4xl mb-4"><?php echo $service['icon']; ?></div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800"><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars($service['desc']); ?></p>
                    <?php if (!empty($service['location'])): ?>
                    <p class="text-xs text-gray-500 mb-1">📍 <?php echo htmlspecialchars($service['location']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($service['phone'])): ?>
                    <p class="text-xs text-gray-500">📞 <?php echo htmlspecialchars($service['phone']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-12">
                <a href="services.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">ดูบริการทั้งหมด</a>
            </div>
        </div>
    </section>

    <!-- Featured Doctors (if available) -->
    <?php if (!empty($doctors)): ?>
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">ทีมแพทย์ของเรา</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach($doctors as $doctor): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                    <div class="p-6 text-center">
                        <div class="w-20 h-20 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <span class="text-blue-600 text-2xl">👨‍⚕️</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-800">
                            <?php echo htmlspecialchars(($doctor['title'] ?? 'นพ.') . ' ' . $doctor['first_name'] . ' ' . $doctor['last_name']); ?>
                        </h3>
                        <?php if (!empty($doctor['specialization'])): ?>
                        <p class="text-blue-600 text-sm font-medium mb-2"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($doctor['department_name'])): ?>
                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($doctor['department_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-12">
                <a href="doctors.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">ดูทีมแพทย์ทั้งหมด</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Statistics Section -->
    <section class="py-16 bg-gradient-to-r from-blue-600 to-green-600 text-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">ตัวเลขของเรา</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach($stats as $stat): ?>
                <div class="text-center">
                    <div class="text-4xl mb-4"><?php echo $stat['icon']; ?></div>
                    <div class="text-3xl font-bold mb-2"><?php echo $stat['number']; ?></div>
                    <div class="text-lg opacity-90"><?php echo $stat['label']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4 text-gray-800">พร้อมให้บริการคุณ</h2>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                ทีมแพทย์และเจ้าหน้าที่มืออาชีพพร้อมดูแลสุขภาพของคุณด้วยใจ
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="contact.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                    📞 ติดต่อเรา
                </a>
                <a href="services.php" class="border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-600 hover:text-white transition duration-300">
                    🏥 ดูบริการ
                </a>
            </div>
        </div>
    </section>

    <!-- Emergency Banner -->
    <section class="bg-red-600 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="text-4xl mr-4">🚨</div>
                    <div>
                        <h3 class="text-xl font-bold">สำหรับกรณีฉุกเฉิน</h3>
                        <p class="opacity-90">โทรหาเราได้ตลอด 24 ชั่วโมง</p>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <div class="text-2xl font-bold mb-1"><?php echo htmlspecialchars($emergency_phone); ?></div>
                    <div class="text-sm opacity-90">หมายเลขฉุกเฉิน</div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.line-clamp-3 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}
</style>

<?php include 'includes/footer.php'; ?>