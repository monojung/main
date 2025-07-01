<?php
/**
 * Admin Sidebar Component
 * ส่วนเมนูด้านข้างสำหรับระบบผู้ดูแล
 * 
 * การใช้งาน:
 * 1. include 'sidebar.php';
 * 2. กำหนดตัวแปร $current_page ก่อน include
 * 
 * ตัวอย่าง: $current_page = 'dashboard';
 */

// ตรวจสอบว่ามีการกำหนด $current_page หรือไม่
if (!isset($current_page)) {
    $current_page = '';
}

// กำหนดเมนูทั้งหมด
$menu_items = [
    'dashboard' => [
        'title' => 'แดชบอร์ด',
        'icon' => '📊',
        'url' => 'dashboard.php',
        'description' => 'ภาพรวมระบบและสถิติ'
    ],
    'news' => [
        'title' => 'จัดการข่าวสาร',
        'icon' => '📰',
        'url' => 'news.php',
        'description' => 'เพิ่ม แก้ไข ลบข่าวสาร'
    ],
    'reports' => [
        'title' => 'รายงาน',
        'icon' => '📊',
        'url' => 'reports.php',
        'description' => 'สถิติและข้อมูลวิเคราะห์'
    ],
    'users' => [
        'title' => 'จัดการผู้ใช้',
        'icon' => '👨‍💼',
        'url' => 'users.php',
        'description' => 'บัญชีผู้ใช้และสิทธิ์'
    ],
    'settings' => [
        'title' => 'ตั้งค่าระบบ',
        'icon' => '⚙️',
        'url' => 'settings.php',
        'description' => 'การกำหนดค่าต่างๆ ของระบบ'
    ]
];

// ฟังก์ชันตรวจสอบว่าเมนูนั้นเป็นเมนูที่ active หรือไม่
function isActiveMenu($menu_key, $current_page) {
    return $menu_key === $current_page;
}

// ฟังก์ชันสร้าง CSS class สำหรับเมนู
function getMenuClass($menu_key, $current_page) {
    if (isActiveMenu($menu_key, $current_page)) {
        return 'flex items-center py-3 px-4 text-blue-600 bg-blue-50 rounded-lg font-medium border-l-4 border-blue-600 shadow-sm';
    }
    return 'flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200';
}
?>

<!-- Enhanced Sidebar -->
<aside class="w-20 lg:w-64 bg-white shadow-xl border-r border-gray-200 transition-all duration-300">
    <div class="p-4 lg:p-6">
        <!-- Sidebar Header (แสดงเฉพาะบน Desktop) -->
        <div class="hidden lg:block mb-6">
            <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-sm">THC</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">ระบบผู้ดูแล</h3>
                    <p class="text-xs text-gray-600">โรงพยาบาลทุ่งหัวช้าง</p>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <div class="space-y-2">
            <?php foreach ($menu_items as $menu_key => $menu_item): ?>
            <a href="<?php echo $menu_item['url']; ?>" 
               class="<?php echo getMenuClass($menu_key, $current_page); ?>"
               title="<?php echo $menu_item['description']; ?>">
                <span class="text-xl mr-3"><?php echo $menu_item['icon']; ?></span> 
                <span class="hidden lg:inline"><?php echo $menu_item['title']; ?></span>
                
                <!-- Active Indicator (เฉพาะในหน้าจอเล็ก) -->
                <?php if (isActiveMenu($menu_key, $current_page)): ?>
                <span class="lg:hidden absolute right-2 w-2 h-2 bg-blue-600 rounded-full"></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            
            <!-- Divider -->
            <hr class="my-3 border-gray-300">
            
            <!-- External Links -->
            <a href="../index.php" target="_blank" 
               class="flex items-center py-3 px-4 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition duration-200"
               title="เปิดเว็บไซต์หลักในแท็บใหม่">
                <span class="text-xl mr-3">🌐</span> 
                <span class="hidden lg:inline">เว็บไซต์หลัก</span>
            </a>
            
            <!-- User Profile Section (แสดงเฉพาะบน Desktop) -->
            <div class="hidden lg:block mt-6 p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-xs font-semibold">
                            <?php 
                            $user_name = $_SESSION['user_name'] ?? 'Admin';
                            $name_parts = explode(' ', $user_name);
                            echo mb_substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? mb_substr($name_parts[1], 0, 1) : '');
                            ?>
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            <?php echo htmlspecialchars($user_name); ?>
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            <?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Admin'); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <a href="../logout.php" 
                       class="flex items-center text-xs text-red-600 hover:text-red-800 transition duration-200">
                        <span class="mr-2">🚪</span>
                        <span>ออกจากระบบ</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu Toggle (แสดงเฉพาะบนมือถือ) -->
    <div class="lg:hidden fixed bottom-4 left-4 z-50">
        <button onclick="toggleSidebar()" 
                class="w-12 h-12 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center">
            <span class="text-lg">☰</span>
        </button>
    </div>
</aside>

<!-- Responsive Overlay (สำหรับมือถือ) -->
<div id="sidebarOverlay" 
     class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 hidden"
     onclick="closeSidebar()"></div>

<script>
// ฟังก์ชันสำหรับ Mobile Menu
function toggleSidebar() {
    const sidebar = document.querySelector('aside');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar.classList.contains('-translate-x-full')) {
        openSidebar();
    } else {
        closeSidebar();
    }
}

function openSidebar() {
    const sidebar = document.querySelector('aside');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    const sidebar = document.querySelector('aside');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// ปิด sidebar เมื่อกดปุ่ม Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSidebar();
    }
});

// เพิ่ม responsive class สำหรับมือถือ
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('aside');
    if (window.innerWidth < 1024) {
        sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-50', '-translate-x-full');
    }
});

// จัดการ resize หน้าจอ
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('aside');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth >= 1024) {
        // Desktop: แสดง sidebar ปกติ
        sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-50', '-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
    } else {
        // Mobile: ซ่อน sidebar
        sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-50', '-translate-x-full');
    }
});

// Active menu highlight animation
document.addEventListener('DOMContentLoaded', function() {
    const activeMenu = document.querySelector('.border-blue-600');
    if (activeMenu) {
        activeMenu.style.opacity = '0';
        activeMenu.style.transform = 'translateX(-10px)';
        
        setTimeout(() => {
            activeMenu.style.transition = 'all 0.3s ease';
            activeMenu.style.opacity = '1';
            activeMenu.style.transform = 'translateX(0)';
        }, 100);
    }
});

// Tooltip for mobile icons
if (window.innerWidth < 1024) {
    document.querySelectorAll('aside a[title]').forEach(link => {
        link.addEventListener('mouseenter', function() {
            if (window.innerWidth < 1024) {
                this.setAttribute('data-tooltip', this.getAttribute('title'));
            }
        });
    });
}

console.log('📱 Responsive Sidebar loaded successfully!');
</script>

<style>
/* Custom styles for sidebar */
aside {
    transition: transform 0.3s ease;
}

@media (max-width: 1023px) {
    aside.fixed {
        width: 64px;
    }
}

/* Tooltip styles for mobile */
[data-tooltip]:before {
    content: attr(data-tooltip);
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
    margin-left: 8px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}

[data-tooltip]:hover:before {
    opacity: 1;
}

/* Animation for menu items */
aside a {
    position: relative;
    overflow: hidden;
}

aside a:hover {
    transform: translateX(2px);
}

/* Active menu special effects */
.border-blue-600 {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
}

/* Responsive adjustments */
@media (min-width: 1024px) {
    aside {
        width: 16rem; /* w-64 */
    }
}

@media (max-width: 1023px) {
    aside {
        width: 5rem; /* w-20 */
    }
}
</style>