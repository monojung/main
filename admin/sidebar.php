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
        'description' => 'ภาพรวมระบบ',
        'color' => 'from-blue-500 to-purple-600'
    ],
    [
        'id' => 'news',
        'title' => 'จัดการข่าวสาร',
        'url' => 'news.php',
        'icon' => '📰',
        'description' => 'เพิ่ม แก้ไข ลบข่าวสาร',
        'color' => 'from-green-500 to-teal-600'
    ],
    [
        'id' => 'ita',
        'title' => 'จัดการ ITA',
        'url' => 'ita.php',
        'icon' => '🔧',
        'description' => 'คำขอ IT Support',
        'color' => 'from-orange-500 to-red-600'
    ],
    [
        'id' => 'reports',
        'title' => 'รายงาน',
        'url' => 'reports.php',
        'icon' => '📈',
        'description' => 'สถิติและรายงาน',
        'color' => 'from-purple-500 to-indigo-600'
    ],
    [
        'id' => 'users',
        'title' => 'จัดการผู้ใช้',
        'url' => 'users.php',
        'icon' => '👥',
        'description' => 'บัญชีผู้ใช้งาน',
        'color' => 'from-indigo-500 to-blue-600'
    ],
    [
        'id' => 'settings',
        'title' => 'ตั้งค่าระบบ',
        'url' => 'settings.php',
        'icon' => '⚙️',
        'description' => 'การกำหนดค่า',
        'color' => 'from-gray-500 to-gray-600'
    ]
];

// Get quick stats for sidebar
$sidebar_stats = [
    'total_news' => 0,
    'total_users' => 0,
    'pending_ita' => 0,
    'system_status' => 'online'
];

if (isset($conn)) {
    try {
        // ข่าวสารทั้งหมด
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
        $stmt->execute();
        $sidebar_stats['total_news'] = $stmt->fetchColumn() ?? 0;
        
        // ผู้ใช้ที่ใช้งานได้
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        $stmt->execute();
        $sidebar_stats['total_users'] = $stmt->fetchColumn() ?? 0;
        
        // ITA Requests ที่รอดำเนินการ (ถ้ามีตาราง)
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ita_requests WHERE status = 'pending'");
            $stmt->execute();
            $sidebar_stats['pending_ita'] = $stmt->fetchColumn() ?? 0;
        } catch (Exception $e) {
            // ตารางยังไม่มี
        }
        
    } catch (Exception $e) {
        // ใช้ค่าเริ่มต้น
    }
}
?>

<!-- Sidebar -->
<aside class="w-64 min-h-screen shadow-2xl relative" id="sidebar">
    <div class="glass-card h-full flex flex-col">
        <!-- Logo Section -->
        <div class="p-6 border-b border-white border-opacity-20">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-300">
                    <span class="text-white font-bold text-lg animate-pulse">🏥</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">ระบบจัดการ</h3>
                    <p class="text-sm text-gray-600">โรงพยาบาลทุ่งหัวช้าง</p>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-white border-opacity-20 bg-gradient-to-r from-blue-50 to-purple-50">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                        <span class="text-white font-semibold text-sm">
                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
                        </span>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white animate-pulse"></div>
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
            <div class="mt-3 text-xs text-gray-500 text-center">
                เข้าสู่ระบบ: <?php echo date('H:i'); ?> น.
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 overflow-y-auto">
            <div class="space-y-2">
                <?php foreach ($menu_items as $item): ?>
                    <?php 
                    $is_active = $current_page === $item['id'];
                    $active_classes = $is_active 
                        ? 'bg-gradient-to-r ' . $item['color'] . ' text-white shadow-lg scale-105 border-l-4 border-white border-opacity-30' 
                        : 'text-gray-700 hover:bg-white hover:bg-opacity-70 hover:shadow-md hover:scale-102';
                    ?>
                    <a href="<?php echo $item['url']; ?>" 
                       class="<?php echo $active_classes; ?> group flex items-center px-4 py-3 rounded-xl transition-all duration-300 relative overflow-hidden">
                        
                        <?php if ($is_active): ?>
                        <!-- Active indicator animation -->
                        <div class="absolute inset-0 bg-white bg-opacity-10 rounded-xl animate-pulse"></div>
                        <?php endif; ?>
                        
                        <span class="text-2xl mr-3 <?php echo $is_active ? 'animate-bounce' : 'group-hover:scale-110'; ?> transition-transform duration-300 relative z-10">
                            <?php echo $item['icon']; ?>
                        </span>
                        <div class="flex-1 relative z-10">
                            <div class="text-sm font-medium">
                                <?php echo $item['title']; ?>
                            </div>
                            <div class="text-xs <?php echo $is_active ? 'text-blue-100' : 'text-gray-500 group-hover:text-gray-600'; ?> transition-colors duration-300">
                                <?php echo $item['description']; ?>
                            </div>
                        </div>
                        
                        <?php if ($is_active): ?>
                            <div class="w-2 h-8 bg-white bg-opacity-30 rounded-full relative z-10"></div>
                        <?php else: ?>
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 relative z-10">
                                <span class="text-xs">→</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Notification badges -->
                        <?php if ($item['id'] === 'ita' && $sidebar_stats['pending_ita'] > 0): ?>
                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse relative z-20">
                            <?php echo min($sidebar_stats['pending_ita'], 9); ?><?php echo $sidebar_stats['pending_ita'] > 9 ? '+' : ''; ?>
                        </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>

        <!-- Quick Stats -->
        <div class="p-4 border-t border-white border-opacity-20 bg-gradient-to-r from-gray-50 to-blue-50">
            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                <span class="animate-pulse mr-2">📊</span>
                สถิติด่วน
            </h4>
            <div class="space-y-2">
                <div class="flex items-center justify-between p-2 bg-white bg-opacity-50 rounded-lg hover:bg-opacity-70 transition-all duration-300 cursor-pointer" onclick="window.location.href='news.php'">
                    <span class="text-xs text-gray-600 flex items-center">
                        <span class="mr-2">📰</span>
                        ข่าวสาร
                    </span>
                    <span class="text-sm font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                        <?php echo number_format($sidebar_stats['total_news']); ?>
                    </span>
                </div>
                
                <div class="flex items-center justify-between p-2 bg-white bg-opacity-50 rounded-lg hover:bg-opacity-70 transition-all duration-300 cursor-pointer" onclick="window.location.href='users.php'">
                    <span class="text-xs text-gray-600 flex items-center">
                        <span class="mr-2">👥</span>
                        ผู้ใช้
                    </span>
                    <span class="text-sm font-bold text-green-600 bg-green-100 px-2 py-1 rounded-full">
                        <?php echo number_format($sidebar_stats['total_users']); ?>
                    </span>
                </div>
                
                <?php if ($sidebar_stats['pending_ita'] > 0): ?>
                <div class="flex items-center justify-between p-2 bg-yellow-100 rounded-lg hover:bg-yellow-200 transition-all duration-300 cursor-pointer animate-pulse" onclick="window.location.href='ita.php'">
                    <span class="text-xs text-yellow-700 flex items-center">
                        <span class="mr-2">🔧</span>
                        ITA รอ
                    </span>
                    <span class="text-sm font-bold text-yellow-800 bg-yellow-200 px-2 py-1 rounded-full">
                        <?php echo number_format($sidebar_stats['pending_ita']); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Status -->
        <div class="p-4 border-t border-white border-opacity-20 bg-gradient-to-r from-green-50 to-blue-50">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600 font-medium">สถานะระบบ</span>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-xs text-green-600 font-medium">ออนไลน์</span>
                </div>
            </div>
            <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                <div class="text-center p-2 bg-white bg-opacity-50 rounded">
                    <div class="text-blue-600 font-semibold" id="server-load">Normal</div>
                    <div class="text-gray-500">Load</div>
                </div>
                <div class="text-center p-2 bg-white bg-opacity-50 rounded">
                    <div class="text-green-600 font-semibold" id="db-status">OK</div>
                    <div class="text-gray-500">DB</div>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500 text-center">
                อัพเดท: <span id="last-update"><?php echo date('H:i'); ?></span> น.
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="p-4 border-t border-white border-opacity-20">
            <h4 class="text-xs font-semibold text-gray-700 mb-2 flex items-center">
                <span class="mr-2">⚡</span>
                การดำเนินการด่วน
            </h4>
            <div class="grid grid-cols-2 gap-2">
                <button onclick="window.location.href='news.php?action=add'" 
                        class="bg-gradient-to-r from-green-400 to-green-500 text-white p-2 rounded-lg text-xs font-medium hover:from-green-500 hover:to-green-600 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <div class="text-lg mb-1">📰</div>
                    <div>ข่าวใหม่</div>
                </button>
                
                <button onclick="window.location.href='ita.php?action=add'" 
                        class="bg-gradient-to-r from-blue-400 to-blue-500 text-white p-2 rounded-lg text-xs font-medium hover:from-blue-500 hover:to-blue-600 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <div class="text-lg mb-1">🔧</div>
                    <div>ITA ใหม่</div>
                </button>
                
                <button onclick="window.location.href='users.php?action=add'" 
                        class="bg-gradient-to-r from-purple-400 to-purple-500 text-white p-2 rounded-lg text-xs font-medium hover:from-purple-500 hover:to-purple-600 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <div class="text-lg mb-1">👤</div>
                    <div>ผู้ใช้ใหม่</div>
                </button>
                
                <button onclick="window.location.href='reports.php'" 
                        class="bg-gradient-to-r from-orange-400 to-orange-500 text-white p-2 rounded-lg text-xs font-medium hover:from-orange-500 hover:to-orange-600 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <div class="text-lg mb-1">📊</div>
                    <div>รายงาน</div>
                </button>
            </div>
        </div>

        <!-- Logout Button -->
        <div class="p-4 border-t border-white border-opacity-20">
            <a href="../logout.php" 
               class="w-full bg-gradient-to-r from-red-500 to-pink-500 text-white py-3 px-4 rounded-xl hover:from-red-600 hover:to-pink-600 transition-all duration-300 hover:scale-105 shadow-lg flex items-center justify-center space-x-2 group relative overflow-hidden">
                
                <!-- Animated background -->
                <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-pink-600 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                
                <span class="text-lg group-hover:scale-110 transition-transform duration-300 relative z-10">🚪</span>
                <span class="font-medium relative z-10">ออกจากระบบ</span>
            </a>
        </div>

        <!-- Version Info -->
        <div class="p-2 text-center border-t border-white border-opacity-20">
            <div class="text-xs text-gray-400">
                Version 2.2.0
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Menu Toggle Button -->
<button id="mobile-menu-btn" class="fixed top-4 left-4 z-50 lg:hidden bg-white bg-opacity-90 backdrop-blur-lg p-3 rounded-xl shadow-lg hover:bg-opacity-100 transition-all duration-300 transform hover:scale-110">
    <span class="text-xl">☰</span>
</button>

<!-- Mobile Overlay -->
<div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-40 lg:hidden hidden transition-opacity duration-300"></div>

<style>
/* Custom styles for sidebar animations */
.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.sidebar-enter {
    animation: slideIn 0.3s ease-out;
}

.notification-badge {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% {
        transform: translate3d(0,0,0);
    }
    40%, 43% {
        transform: translate3d(0,-8px,0);
    }
    70% {
        transform: translate3d(0,-4px,0);
    }
    90% {
        transform: translate3d(0,-2px,0);
    }
}

.hover-scale:hover {
    transform: scale(1.05);
}

/* Scrollbar styling for sidebar */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>

<script>
// Enhanced Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    if (mobileMenuBtn && sidebar && mobileOverlay) {
        // Toggle mobile menu
        mobileMenuBtn.addEventListener('click', function() {
            const isOpen = !sidebar.classList.contains('-translate-x-full');
            
            if (isOpen) {
                // Close menu
                sidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('hidden');
                this.querySelector('span').textContent = '☰';
                document.body.style.overflow = '';
            } else {
                // Open menu
                sidebar.classList.remove('-translate-x-full');
                mobileOverlay.classList.remove('hidden');
                this.querySelector('span').textContent = '✕';
                document.body.style.overflow = 'hidden';
            }
        });
        
        // Close menu when clicking overlay
        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
            mobileMenuBtn.querySelector('span').textContent = '☰';
            document.body.style.overflow = '';
        });
        
        // Close menu when clicking menu item on mobile
        sidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) { // lg breakpoint
                    sidebar.classList.add('-translate-x-full');
                    mobileOverlay.classList.add('hidden');
                    mobileMenuBtn.querySelector('span').textContent = '☰';
                    document.body.style.overflow = '';
                }
            });
        });
        
        // Initialize mobile menu state
        sidebar.classList.add('-translate-x-full', 'fixed', 'z-50', 'lg:relative', 'lg:translate-x-0');
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                // Desktop view
                sidebar.classList.remove('-translate-x-full');
                mobileOverlay.classList.add('hidden');
                mobileMenuBtn.querySelector('span').textContent = '☰';
                document.body.style.overflow = '';
            } else {
                // Mobile view
                if (!mobileOverlay.classList.contains('hidden')) {
                    sidebar.classList.remove('-translate-x-full');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });
    }
    
    // Add active menu item animation
    const activeMenuItem = document.querySelector('a.bg-gradient-to-r');
    if (activeMenuItem) {
        activeMenuItem.style.animation = 'glow 2s ease-in-out infinite alternate';
    }
    
    // Real-time clock update
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('th-TH', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        const lastUpdateElement = document.getElementById('last-update');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = timeString;
        }
    }
    
    // Update time every minute
    setInterval(updateTime, 60000);
    updateTime();
    
    // System status monitoring (simulation)
    function updateSystemStatus() {
        const serverLoad = document.getElementById('server-load');
        const dbStatus = document.getElementById('db-status');
        
        // Simulate system monitoring
        const loadLevels = ['Low', 'Normal', 'High'];
        const loadColors = ['text-green-600', 'text-blue-600', 'text-orange-600'];
        const currentLoad = Math.floor(Math.random() * 3);
        
        if (serverLoad) {
            serverLoad.className = loadColors[currentLoad] + ' font-semibold';
            serverLoad.textContent = loadLevels[currentLoad];
        }
        
        if (dbStatus) {
            // Database is usually OK, rarely show issues
            const dbOk = Math.random() > 0.1;
            dbStatus.className = dbOk ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
            dbStatus.textContent = dbOk ? 'OK' : 'Slow';
        }
    }
    
    // Update system status every 30 seconds
    setInterval(updateSystemStatus, 30000);
    
    // Add hover effects to quick action buttons
    document.querySelectorAll('button[onclick]').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05) translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) translateY(0)';
        });
    });
    
    // Notification badge animation
    const notificationBadges = document.querySelectorAll('.animate-pulse');
    notificationBadges.forEach(badge => {
        if (badge.textContent && parseInt(badge.textContent) > 0) {
            badge.classList.add('notification-badge');
        }
    });
    
    // Add contextual tooltips
    const menuItems = document.querySelectorAll('nav a');
    menuItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            // Could add tooltips here if needed
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Alt + D for Dashboard
        if (e.altKey && e.key === 'd') {
            e.preventDefault();
            window.location.href = 'dashboard.php';
        }
        // Alt + N for News
        if (e.altKey && e.key === 'n') {
            e.preventDefault();
            window.location.href = 'news.php';
        }
        // Alt + I for ITA
        if (e.altKey && e.key === 'i') {
            e.preventDefault();
            window.location.href = 'ita.php';
        }
        // Alt + U for Users
        if (e.altKey && e.key === 'u') {
            e.preventDefault();
            window.location.href = 'users.php';
        }
        // Alt + R for Reports
        if (e.altKey && e.key === 'r') {
            e.preventDefault();
            window.location.href = 'reports.php';
        }
        // Alt + S for Settings
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            window.location.href = 'settings.php';
        }
    });
});

// Add custom styles for glow effect
const glowStyle = document.createElement('style');
glowStyle.textContent = `
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
    
    .hover-scale {
        transition: transform 0.2s ease-in-out;
    }
`;
document.head.appendChild(glowStyle);

console.log('🎯 Enhanced Sidebar loaded successfully!');
</script>