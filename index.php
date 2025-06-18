<?php 
$page_title = "หน้าหลัก";
require_once 'config/database.php';

// Get news and announcements from database
$news = array();
$services = array();

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        // Get featured news
        $stmt = $conn->prepare("
            SELECT title, excerpt, publish_date, category, slug 
            FROM news 
            WHERE status = 'published' 
            AND (publish_date IS NULL OR publish_date <= NOW())
            ORDER BY is_featured DESC, publish_date DESC 
            LIMIT 6
        ");
        $stmt->execute();
        $news = $stmt->fetchAll();
        
        // Get departments for services
        $stmt = $conn->prepare("
            SELECT code, name, description, location 
            FROM departments 
            WHERE is_active = 1 
            ORDER BY name
        ");
        $stmt->execute();
        $departments = $stmt->fetchAll();
        
        // Map departments to services with icons
        $service_icons = array(
            'GEN' => '👩‍⚕️',
            'PED' => '👶',
            'OBS' => '🤱',
            'SUR' => '🏥',
            'ORT' => '🦴',
            'DEN' => '🦷',
            'EMR' => '🚑',
            'LAB' => '🔬'
        );
        
        foreach ($departments as $dept) {
            $services[] = array(
                'name' => $dept['name'],
                'icon' => isset($service_icons[$dept['code']]) ? $service_icons[$dept['code']] : '🏥',
                'desc' => $dept['description'] ? $dept['description'] : 'บริการ' . $dept['name'],
                'location' => $dept['location']
            );
        }
    }
} catch (Exception $e) {
    logError($e->getMessage(), __FILE__, __LINE__);
    // Fallback to static data if database fails
    $news = array(
        array(
            'title' => 'ข่าวสารจากโรงพยาบาล',
            'excerpt' => 'ติดตามข่าวสารและประกาศต่างๆ จากโรงพยาบาล',
            'publish_date' => date('Y-m-d'),
            'category' => 'general',
            'slug' => '#'
        )
    );
    
    $services = array(
        array('name' => 'แผนกผู้ป่วยใน', 'icon' => '🏨', 'desc' => 'บริการดูแลผู้ป่วยใน 24 ชั่วโมง'),
        array('name' => 'แผนกผู้ป่วยนอก', 'icon' => '👩‍⚕️', 'desc' => 'บริการตรวจรักษาผู้ป่วยนอก'),
        array('name' => 'แผนกฉุกเฉิน', 'icon' => '🚑', 'desc' => 'บริการฉุกเฉินตลอด 24 ชั่วโมง'),
        array('name' => 'ห้องปฏิบัติการ', 'icon' => '🔬', 'desc' => 'ตรวจวิเคราะห์ทางห้องปฏิบัติการ')
    );
}

// If no news from database, add some default items
if (empty($news)) {
    $news = array(
        array(
            'title' => 'ประกาศจังหวัดลำพูน เรื่องประกาศผู้ชนะการเสนอราคาจัดซื้อระบบบริหารจัดการผู้ป่วยใน (IPD Paperless)',
            'excerpt' => 'ประกาศผู้ชนะการเสนอราคาจัดซื้อระบบ IPD Paperless ด้วยวิธี e-bidding',
            'publish_date' => '2025-06-15',
            'category' => 'procurement',
            'slug' => '#'
        ),
        array(
            'title' => 'แผนจัดซื้อจัดจ้าง ปี งบประมาณ 2568',
            'excerpt' => 'เผยแพร่แผนจัดซื้อจัดจ้าง ประจำปีงบประมาณ 2568',
            'publish_date' => '2025-06-10',
            'category' => 'procurement',
            'slug' => '#'
        ),
        array(
            'title' => 'การให้บริการฉีดวัคซีนไข้หวัดใหญ่ ประจำปี 2568',
            'excerpt' => 'เปิดให้บริการฉีดวัคซีนป้องกันไข้หวัดใหญ่ ประจำปี 2568',
            'publish_date' => '2025-06-01',
            'category' => 'service',
            'slug' => '#'
        )
    );
}

include 'includes/header.php'; 
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-green-600 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">โรงพยาบาลทุ่งหัวช้าง</h1>
            <p class="text-xl md:text-2xl mb-8">จังหวัดลำพูน</p>
            <p class="text-lg mb-8 max-w-2xl mx-auto">ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="services.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">บริการของเรา</a>
                <a href="appointment.php" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">นัดหมายแพทย์</a>
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
                    <p class="text-gray-600">จันทร์ - ศุกร์: 08:00 - 16:30</p>
                    <p class="text-gray-600">เสาร์ - อาทิตย์: 08:00 - 12:00</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <div class="text-green-600 text-4xl mb-4">📞</div>
                    <h3 class="text-xl font-semibold mb-2">ติดต่อเรา</h3>
                    <p class="text-gray-600">053-580-xxx</p>
                    <p class="text-gray-600">053-580-xxx (ฉุกเฉิน)</p>
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
                <?php foreach(array_slice($news, 0, 6) as $item): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                    <div class="p-6">
                        <div class="text-sm text-blue-600 font-semibold mb-2">
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
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 line-clamp-3">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </h3>
                        <?php if (!empty($item['excerpt'])): ?>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                            <?php echo htmlspecialchars($item['excerpt']); ?>
                        </p>
                        <?php endif; ?>
                        <p class="text-gray-600 text-sm">
                            <?php echo formatThaiDate($item['publish_date']); ?>
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
                    <p class="text-gray-600"><?php echo htmlspecialchars($service['desc']); ?></p>
                    <?php if (!empty($service['location'])): ?>
                    <p class="text-xs text-gray-500 mt-2">📍 <?php echo htmlspecialchars($service['location']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-12">
                <a href="services.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">ดูบริการทั้งหมด</a>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-16 bg-gradient-to-r from-blue-600 to-green-600 text-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">ตัวเลขของเรา</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php
                $stats = array();
                try {
                    if ($conn) {
                        // Get total appointments this month
                        $stmt = $conn->prepare("
                            SELECT COUNT(*) as count 
                            FROM appointments 
                            WHERE MONTH(appointment_date) = MONTH(CURDATE()) 
                            AND YEAR(appointment_date) = YEAR(CURDATE())
                        ");
                        $stmt->execute();
                        $monthly_appointments = $stmt->fetch();
                        
                        // Get total patients
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM patients WHERE is_active = 1");
                        $stmt->execute();
                        $total_patients = $stmt->fetch();
                        
                        // Get total doctors
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM doctors WHERE is_active = 1");
                        $stmt->execute();
                        $total_doctors = $stmt->fetch();
                        
                        // Get total departments
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM departments WHERE is_active = 1");
                        $stmt->execute();
                        $total_departments = $stmt->fetch();
                        
                        $stats = array(
                            array('number' => number_format($monthly_appointments['count']), 'label' => 'นัดหมายเดือนนี้', 'icon' => '📅'),
                            array('number' => number_format($total_patients['count']), 'label' => 'ผู้ป่วยทั้งหมด', 'icon' => '👥'),
                            array('number' => number_format($total_doctors['count']), 'label' => 'แพทย์และเจ้าหน้าที่', 'icon' => '👨‍⚕️'),
                            array('number' => number_format($total_departments['count']), 'label' => 'แผนกบริการ', 'icon' => '🏥')
                        );
                    }
                } catch (Exception $e) {
                    // Fallback stats
                }
                
                if (empty($stats)) {
                    $stats = array(
                        array('number' => '150+', 'label' => 'นัดหมายเดือนนี้', 'icon' => '📅'),
                        array('number' => '1,200+', 'label' => 'ผู้ป่วยทั้งหมด', 'icon' => '👥'),
                        array('number' => '25+', 'label' => 'แพทย์และเจ้าหน้าที่', 'icon' => '👨‍⚕️'),
                        array('number' => '8', 'label' => 'แผนกบริการ', 'icon' => '🏥')
                    );
                }
                
                foreach($stats as $stat): ?>
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
                <a href="appointment.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                    📅 นัดหมายแพทย์
                </a>
                <a href="contact.php" class="border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-600 hover:text-white transition duration-300">
                    📞 ติดต่อเรา
                </a>
                <a href="services.php" class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-300">
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
                    <div class="text-2xl font-bold mb-1">1669</div>
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