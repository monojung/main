<?php
// ตรวจสอบว่ามีการกำหนด $current_page หรือไม่
if (!isset($current_page)) {
    $current_page = '';
}

// เมนูรายการ
$menu_items = [
    [
        'id' => 'dashboard',
        'title' => 'แดชบอร์ด',
        'url' => 'dashboard.php',
        'icon' => '📊',
        'description' => 'ภาพรวมระบบ'
    ],
    [
        'id' => 'news',
        'title' => 'จัดการข่าวสาร',
        'url' => 'news.php',
        'icon' => '📰',
        'description' => 'เพิ่ม แก้ไข ลบข่าวสาร'
    ],
    [
        'id' => 'ita',
        'title' => 'จัดการ ITA',
        'url' => 'ita.php',
        'icon' => '🔧',
        'description' => 'คำขอ IT Support'
    ],
    [
        'id' => 'reports',
        'title' => 'รายงาน',
        'url' => 'reports.php',
        'icon' => '📈',
        'description' => 'สถิติและรายงาน'
    ],
    [
        'id' => 'users',
        'title' => 'จัดการผู้ใช้',
        'url' => 'users.php',
        'icon' => '👥',
        'description' => 'บัญชีผู้ใช้งาน'
    ],
    [
        'id' => 'settings',
        'title' => 'ตั้งค่าระบบ',
        'url' => 'settings.php',
        'icon' => '⚙️',
        'description' => 'การกำหนดค่า'
    ]
];
?>

<!-- Sidebar -->
<aside class="w-64 min-h-screen shadow-2xl" id="sidebar">
    <div class="glass-card h-full">
        <!-- Logo Section -->
        <div class="p-6 border-b border-white border-opacity-20">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold text-lg">🏥</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">ระบบจัดการ</h3>
                    <p class="text-sm text-gray-600">โรงพยาบาลทุ่งหัวช้าง</p>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-white border-opacity-20">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold">
                        <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                    </p>
                    <p class="text-xs text-gray-500">ผู้ดูแลระบบ</p>
                </div>
                <div class="flex-shrink-0">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse" title="ออนไลน์"></div>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4">
            <div class="space-y-2">
                <?php foreach ($menu_items as $item): ?>
                    <?php 
                    $is_active = $current_page === $item['id'];
                    $active_classes = $is_active 
                        ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg scale-105' 
                        : 'text-gray-700 hover:bg-white hover:bg-opacity-70 hover:shadow-md';
                    ?>
                    <a href="<?php echo $item['url']; ?>" 
                       class="<?php echo $active_classes; ?> group flex items-center px-4 py-3 rounded-xl transition-all duration-300 hover:scale-105">
                        <span class="text-2xl mr-3 <?php echo $is_active ? '' : 'group-hover:scale-110'; ?> transition-transform">
                            <?php echo $item['icon']; ?>
                        </span>
                        <div class="flex-1">
                            <div class="text-sm font-medium">
                                <?php echo $item['title']; ?>
                            </div>
                            <div class="text-xs <?php echo $is_active ? 'text-blue-100' : 'text-gray-500'; ?>">
                                <?php echo $item['description']; ?>
                            </div>
                        </div>
                        <?php if ($is_active): ?>
                            <div class="w-2 h-8 bg-white bg-opacity-30 rounded-full"></div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>

        <!-- Quick Stats -->
        <div class="p-4 border-t border-white border-opacity-20">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">📊 สถิติด่วน</h4>
            <div class="space-y-2">
                <?php
                // แสดงสถิติพื้นฐาน
                if (isset($conn)) {
                    try {
                        // ข่าวสารทั้งหมด
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
                        $stmt->execute();
                        $total_news = $stmt->fetchColumn() ?? 0;
                        
                        // ผู้ใช้ออนไลน์
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
                        $stmt->execute();
                        $total_users = $stmt->fetchColumn() ?? 0;
                        
                        // ITA Requests (ถ้ามีตาราง)
                        $total_ita = 0;
                        try {
                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ita_requests WHERE status = 'pending'");
                            $stmt->execute();
                            $total_ita = $stmt->fetchColumn() ?? 0;
                        } catch (Exception $e) {
                            // ตารางยังไม่มี
                        }
                ?>
                        <div class="flex items-center justify-between p-2 bg-white bg-opacity-30 rounded-lg">
                            <span class="text-xs text-gray-600">📰 ข่าวสาร</span>
                            <span class="text-sm font-bold text-gray-800"><?php echo number_format($total_news); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-white bg-opacity-30 rounded-lg">
                            <span class="text-xs text-gray-600">👥 ผู้ใช้</span>
                            <span class="text-sm font-bold text-gray-800"><?php echo number_format($total_users); ?></span>
                        </div>
                        <?php if ($total_ita > 0): ?>
                        <div class="flex items-center justify-between p-2 bg-yellow-100 rounded-lg">
                            <span class="text-xs text-yellow-700">🔧 ITA รอ</span>
                            <span class="text-sm font-bold text-yellow-800"><?php echo number_format($total_ita); ?></span>
                        </div>
                        <?php endif; ?>
                <?php
                    } catch (Exception $e) {
                        // แสดงข้อมูลเริ่มต้น
                ?>
                        <div class="flex items-center justify-between p-2 bg-white bg-opacity-30 rounded-lg">
                            <span class="text-xs text-gray-600">📊 ระบบ</span>
                            <span class="text-sm font-bold text-green-600">ปกติ</span>
                        </div>
                <?php
                    }
                } else {
                ?>
                    <div class="flex items-center justify-between p-2 bg-white bg-opacity-30 rounded-lg">
                        <span class="text-xs text-gray-600">🔄 โหลด</span>
                        <span class="text-sm font-bold text-blue-600">...</span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- System Status -->
        <div class="p-4 border-t border-white border-opacity-20">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600">สถานะระบบ</span>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-xs text-green-600 font-medium">ออนไลน์</span>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                อัพเดท: <?php echo date('H:i'); ?> น.
            </div>
        </div>

        <!-- Logout Button -->
        <div class="p-4 border-t border-white border-opacity-20">
            <a href="../logout.php" 
               class="w-full bg-gradient-to-r from-red-500 to-pink-500 text-white py-3 px-4 rounded-xl hover:from-red-600 hover:to-pink-600 transition-all duration-300 hover:scale-105 shadow-lg flex items-center justify-center space-x-2 group">
                <span class="text-lg group-hover:scale-110 transition-transform">🚪</span>
                <span class="font-medium">ออกจากระบบ</span>
            </a>
        </div>
    </div>
</aside>

<!-- Mobile Menu Toggle Button -->
<button id="mobile-menu-btn" class="fixed top-4 left-4 z-50 lg:hidden bg-white bg-opacity-90 backdrop-blur-lg p-3 rounded-xl shadow-lg hover:bg-opacity-100 transition-all duration-300">
    <span class="text-xl">☰</span>
</button>

<!-- Mobile Overlay -->
<div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-40 lg:hidden hidden"></div>

<script>
// Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    if (mobileMenuBtn && sidebar && mobileOverlay) {
        // Toggle mobile menu
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            mobileOverlay.classList.toggle('hidden');
            
            // Update button icon
            const icon = this.querySelector('span');
            icon.textContent = sidebar.classList.contains('-translate-x-full') ? '☰' : '✕';
        });
        
        // Close menu when clicking overlay
        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
            
            const icon = mobileMenuBtn.querySelector('span');
            icon.textContent = '☰';
        });
        
        // Initialize mobile menu state
        sidebar.classList.add('-translate-x-full', 'fixed', 'z-50', 'lg:relative', 'lg:translate-x-0');
    }
    
    // Add active menu item animation
    const activeMenuItem = document.querySelector('.bg-gradient-to-r.from-blue-500');
    if (activeMenuItem) {
        activeMenuItem.style.animation = 'glow 2s ease-in-out infinite alternate';
    }
});

// Add custom styles for mobile responsiveness
const style = document.createElement('style');
style.textContent = `
    @keyframes glow {
        from {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
        to {
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.8);
        }
    }
    
    @media (max-width: 1023px) {
        #sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        
        #sidebar:not(.-translate-x-full) {
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(style);
</script>