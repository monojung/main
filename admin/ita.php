.hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-cyan-700 text-white shadow-2xl sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                        <span class="text-white font-bold text-xl">🔧</span>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold">จัดการ ITA Requests</h1>
                        <p class="text-blue-200 text-sm">ระบบจัดการคำขอ IT Support</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-blue-200"><?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl transition duration-300 shadow-lg">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex min-h-screen">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 lg:p-8 overflow-x-hidden">
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 px-4 py-3 rounded-lg fade-in">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span><?php echo $message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-lg fade-in">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($action)): ?>
            <!-- List View -->
            <!-- Page Header -->
            <div class="mb-8 fade-in">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">🔧 จัดการ ITA Requests</h2>
                        <p class="text-gray-200">ระบบจัดการคำขอ IT Support และการบำรุงรักษา</p>
                    </div>
                    <a href="?action=add" class="bg-white text-blue-600 hover:bg-blue-50 px-6 py-3 rounded-xl transition duration-300 shadow-lg font-medium flex items-center space-x-2">
                        <span class="text-lg">🆕</span>
                        <span>เพิ่ม ITA Request ใหม่</span>
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 lg:gap-6 mb-8">
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?></div>
                        <div class="text-sm text-gray-600">ทั้งหมด</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-yellow-600"><?php echo number_format($stats['pending']); ?></div>
                        <div class="text-sm text-gray-600">รอดำเนินการ</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-orange-600"><?php echo number_format($stats['in_progress']); ?></div>
                        <div class="text-sm text-gray-600">กำลังดำเนินการ</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-green-600"><?php echo number_format($stats['completed']); ?></div>
                        <div class="text-sm text-gray-600">เสร็จสิ้น</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-red-600"><?php echo number_format($stats['urgent']); ?></div>
                        <div class="text-sm text-gray-600">ด่วน</div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="glass-card rounded-2xl p-6 mb-8 fade-in">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🔍 ค้นหา</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="หัวข้อ, รหัส, ผู้ขอ..." 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📊 สถานะ</label>
                        <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">ทั้งหมด</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                            <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>ไม่อนุมัติ</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">⚡ ความสำคัญ</label>
                        <select name="priority" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">ทั้งหมด</option>
                            <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>ต่ำ</option>
                            <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>ปานกลาง</option>
                            <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>สูง</option>
                            <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>ด่วน</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📂 หมวดหมู่</label>
                        <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">ทั้งหมด</option>
                            <option value="hardware" <?php echo $category_filter === 'hardware' ? 'selected' : ''; ?>>ฮาร์ดแวร์</option>
                            <option value="software" <?php echo $category_filter === 'software' ? 'selected' : ''; ?>>ซอฟต์แวร์</option>
                            <option value="network" <?php echo $category_filter === 'network' ? 'selected' : ''; ?>>เครือข่าย</option>
                            <option value="maintenance" <?php echo $category_filter === 'maintenance' ? 'selected' : ''; ?>>บำรุงรักษา</option>
                            <option value="other" <?php echo $category_filter === 'other' ? 'selected' : ''; ?>>อื่นๆ</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-5">
                        <button type="submit" class="bg-blue-600 text-white py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300 font-medium">
                            🔍 ค้นหา
                        </button>
                    </div>
                </form>
            </div>

            <!-- ITA Table -->
            <div class="glass-card rounded-2xl overflow-hidden shadow-xl fade-in">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">📋 รายการ ITA Requests</h3>
                        <div class="text-sm text-gray-600">
                            แสดง <?php echo number_format(count($ita_list ?? [])); ?> จาก <?php echo number_format($total_records ?? 0); ?> รายการ
                        </div>
                    </div>
                </div>

                <?php if (empty($ita_list)): ?>
                <div class="p-12 text-center">
                    <div class="text-6xl mb-4">🔧</div>
                    <p class="text-gray-500 text-lg font-medium">ไม่มี ITA Requests</p>
                    <p class="text-gray-400 text-sm mb-6">เริ่มต้นโดยการเพิ่ม ITA Request ใหม่</p>
                    <a href="?action=add" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition duration-300 inline-flex items-center space-x-2">
                        <span>🆕</span>
                        <span>เพิ่ม ITA Request ใหม่</span>
                    </a>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รหัส/หัวข้อ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้ขอ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมวดหมู่/ความสำคัญ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้รับผิดชอบ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($ita_list as $ita): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-blue-600"><?php echo htmlspecialchars($ita['request_number']); ?></div>
                                        <div class="text-sm font-medium text-gray-900 mt-1">
                                            <?php echo htmlspecialchars($ita['title']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <?php echo truncateText($ita['description'], 80); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($ita['requester_name']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($ita['requester_email']); ?></div>
                                        <?php if ($ita['department']): ?>
                                        <div class="text-xs text-gray-400"><?php echo htmlspecialchars($ita['department']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <?php
                                        $category_colors = [
                                            'hardware' => 'bg-purple-100 text-purple-800',
                                            'software' => 'bg-blue-100 text-blue-800',
                                            'network' => 'bg-green-100 text-green-800',
                                            'maintenance' => 'bg-yellow-100 text-yellow-800',
                                            'other' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $category_labels = [
                                            'hardware' => 'ฮาร์ดแวร์',
                                            'software' => 'ซอฟต์แวร์',
                                            'network' => 'เครือข่าย',
                                            'maintenance' => 'บำรุงรักษา',
                                            'other' => 'อื่นๆ'
                                        ];
                                        
                                        $priority_colors = [
                                            'low' => 'bg-gray-100 text-gray-800',
                                            'medium' => 'bg-blue-100 text-blue-800',
                                            'high' => 'bg-orange-100 text-orange-800',
                                            'urgent' => 'bg-red-100 text-red-800'
                                        ];
                                        $priority_labels = [
                                            'low' => 'ต่ำ',
                                            'medium' => 'ปานกลาง',
                                            'high' => 'สูง',
                                            'urgent' => 'ด่วน'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $category_colors[$ita['category']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $category_labels[$ita['category']] ?? $ita['category']; ?>
                                        </span>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $priority_colors[$ita['priority']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $priority_labels[$ita['priority']] ?? $ita['priority']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $status_colors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $status_labels = [
                                        'pending' => 'รอดำเนินการ',
                                        'in_progress' => 'กำลังดำเนินการ',
                                        'completed' => 'เสร็จสิ้น',
                                        'rejected' => 'ไม่อนุมัติ',
                                        'cancelled' => 'ยกเลิก'
                                    ];
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $status_colors[$ita['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $status_labels[$ita['status']] ?? $ita['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php if ($ita['assignee_first_name']): ?>
                                        <?php echo htmlspecialchars($ita['assignee_first_name'] . ' ' . $ita['assignee_last_name']); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">ยังไม่มอบหมาย</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo safeFormatThaiDateTime($ita['created_at']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $ita['id']; ?>" 
                                           class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            ✏️ แก้ไข
                                        </a>
                                        
                                        <?php if ($ita['status'] === 'pending'): ?>
                                        <button onclick="updateStatus(<?php echo $ita['id']; ?>, 'in_progress')"
                                                class="bg-orange-100 text-orange-600 hover:bg-orange-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            ▶️ เริ่มดำเนินการ
                                        </button>
                                        <?php elseif ($ita['status'] === 'in_progress'): ?>
                                        <button onclick="updateStatus(<?php echo $ita['id']; ?>, 'completed')"
                                                class="bg-green-100 text-green-600 hover:bg-green-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            ✅ เสร็จสิ้น
                                        </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="confirmDelete(<?php echo $ita['id']; ?>)"
                                                class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            🗑️ ลบ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            แสดงรายการ <?php echo number_format(($page - 1) * $per_page + 1); ?> - <?php echo number_format(min($page * $per_page, $total_records)); ?> 
                            จากทั้งหมด <?php echo number_format($total_records); ?> รายการ
                        </div>
                        <div class="flex space-x-2">
                            <?php if ($pagination['has_prev']): ?>
                            <a href="?page=<?php echo $pagination['prev_page']; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="bg-white border border-gray-300 text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                                ← ก่อนหน้า
                            </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="<?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-500 hover:text-gray-700'; ?> px-4 py-2 rounded-lg transition duration-200">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?php echo $pagination['next_page']; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                               class="bg-white border border-gray-300 text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg transition duration-200">
                                ถัดไป →
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <!-- Add/Edit Form -->
            <div class="mb-8 fade-in">
                <div class="flex items-center space-x-4 mb-4">
                    <a href="ita.php" class="text-white hover:text-gray-200 transition duration-200">
                        <span class="text-2xl">←</span>
                    </a>
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white">
                            <?php echo $action === 'add' ? '🆕 เพิ่ม ITA Request ใหม่' : '✏️ แก้ไข ITA Request'; ?>
                        </h2>
                        <p class="text-gray-200">
                            <?php echo $action === 'add' ? 'สร้างคำขอ IT Support ใหม่' : 'แก้ไขข้อมูล ITA Request'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8 fade-in">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($edit_ita): ?>
                    <input type="hidden" name="ita_id" value="<?php echo $edit_ita['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Request Information -->
                    <div class="bg-blue-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">📋 ข้อมูลคำขอ</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">หัวข้อคำขอ *</label>
                                <input type="text" name="title" required 
                                       value="<?php echo htmlspecialchars($edit_ita['title'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="กรอกหัวข้อคำขอ">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมวดหมู่</label>
                                <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="hardware" <?php echo ($edit_ita['category'] ?? 'other') === 'hardware' ? 'selected' : ''; ?>>ฮาร์ดแวร์</option>
                                    <option value="software" <?php echo ($edit_ita['category'] ?? '') === 'software' ? 'selected' : ''; ?>>ซอฟต์แวร์</option>
                                    <option value="network" <?php echo ($edit_ita['category'] ?? '') === 'network' ? 'selected' : ''; ?>>เครือข่าย</option>
                                    <option value="maintenance" <?php echo ($edit_ita['category'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>บำรุงรักษา</option>
                                    <option value="other" <?php echo ($edit_ita['category'] ?? 'other') === 'other' ? 'selected' : ''; ?>>อื่นๆ</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ความสำคัญ</label>
                                <select name="priority" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="low" <?php echo ($edit_ita['priority'] ?? 'medium') === 'low' ? 'selected' : ''; ?>>ต่ำ</option>
                                    <option value="medium" <?php echo ($edit_ita['priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>ปานกลาง</option>
                                    <option value="high" <?php echo ($edit_ita['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>สูง</option>
                                    <option value="urgent" <?php echo ($edit_ita['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>ด่วน</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">รายละเอียดคำขอ *</label>
                                <textarea name="description" required rows="5" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="อธิบายรายละเอียดปัญหาหรือคำขอ"><?php echo htmlspecialchars($edit_ita['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Requester Information -->
                    <div class="bg-green-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">👤 ข้อมูลผู้ขอ</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อผู้ขอ *</label>
                                <input type="text" name="requester_name" required 
                                       value="<?php echo htmlspecialchars($edit_ita['requester_name'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="ชื่อ-นามสกุล">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">อีเมล *</label>
                                <input type="email" name="requester_email" required 
                                       value="<?php echo htmlspecialchars($edit_ita['requester_email'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="email@example.com">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมายเลขโทรศัพท์</label>
                                <input type="tel" name="requester_phone" 
                                       value="<?php echo htmlspecialchars($edit_ita['requester_phone'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0xx-xxx-xxxx">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">แผนก</label>
                                <input type="text" name="department" 
                                       value="<?php echo htmlspecialchars($edit_ita['department'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="ชื่อแผนก">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">สถานที่</label>
                                <input type="text" name="location" 
                                       value="<?php echo htmlspecialchars($edit_ita['location'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="อาคาร ชั้น ห้อง">
                            </div>
                        </div>
                    </div>

                    <!-- Assignment & Management -->
                    <div class="bg-purple-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">👨‍💼 การมอบหมายงาน</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">มอบหมายให้</label>
                                <select name="assigned_to" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">เลือกผู้รับผิดชอบ</option>
                                    <?php foreach ($technicians as $tech): ?>
                                    <option value="<?php echo $tech['id']; ?>" <?php echo ($edit_ita['assigned_to'] ?? '') == $tech['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">วันที่คาดว่าจะเสร็จ</label>
                                <input type="date" name="estimated_completion" 
                                       value="<?php echo $edit_ita && $edit_ita['estimated_completion'] ? date('Y-m-d', strtotime($edit_ita['estimated_completion'])) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <?php if ($edit_ita): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="pending" <?php echo ($edit_ita['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                    <option value="in_progress" <?php echo ($edit_ita['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                    <option value="completed" <?php echo ($edit_ita['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                                    <option value="rejected" <?php echo ($edit_ita['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>ไม่อนุมัติ</option>
                                    <option value="cancelled" <?php echo ($edit_ita['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="<?php echo $edit_ita ? '' : 'md:col-span-2'; ?>">
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                                <textarea name="notes" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="หมายเหตุเพิ่มเติม"><?php echo htmlspecialchars($edit_ita['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="ita.php" 
                           class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-6 py-3 rounded-xl transition duration-300 font-medium">
                            ยกเลิก
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 text-white hover:bg-blue-700 px-6 py-3 rounded-xl transition duration-300 font-medium flex items-center space-x-2">
                            <span><?php echo $action === 'add' ? '🆕' : '💾'; ?></span>
                            <span><?php echo $action === 'add' ? 'เพิ่ม ITA Request' : 'บันทึกการแก้ไข'; ?></span>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Status update function
        function updateStatus(itaId, newStatus) {
            const statusText = {
                'in_progress': 'เริ่มดำเนินการ',
                'completed': 'เสร็จสิ้น',
                'rejected': 'ไม่อนุมัติ',
                'cancelled': 'ยกเลิก'
            };
            
            if (confirm(`คุณต้องการ${statusText[newStatus]}คำขอนี้หรือไม่?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="ita_id" value="${itaId}">
                    <input type="hidden" name="new_status" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Delete confirmation
        function confirmDelete(itaId) {
            if (confirm('คุณต้องการลบ ITA Request นี้หรือไม่? การดำเนินการนี้ไม่สามารถย้อนกลับได้')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="ita_id" value="${itaId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const title = document.querySelector('input[name="title"]');
                    const description = document.querySelector('textarea[name="description"]');
                    const requesterName = document.querySelector('input[name="requester_name"]');
                    const requesterEmail = document.querySelector('input[name="requester_email"]');
                    
                    if (title && description && requesterName && requesterEmail) {
                        if (!title.value.trim() || !description.value.trim() || !requesterName.value.trim() || !requesterEmail.value.trim()) {
                            e.preventDefault();
                            alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                            return false;
                        }
                        
                        // Email validation
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(requesterEmail.value)) {
                            e.preventDefault();
                            alert('กรุณากรอกอีเมลที่ถูกต้อง');
                            return false;
                        }
                    }
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span>กำลังบันทึก...';
                        submitBtn.disabled = true;
                        
                        // Re-enable after 10 seconds as fallback
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 10000);
                    }
                });
            }
        });

        // Phone number formatting
        const phoneInput = document.querySelector('input[name="requester_phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    if (value.length <= 3) {
                        value = value;
                    } else if (value.length <= 6) {
                        value = value.substring(0, 3) + '-' + value.substring(3);
                    } else if (value.length <= 10) {
                        value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6);
                    } else {
                        value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6, 10);
                    }
                }
                e.target.value = value;
            });
        }

        // Auto-generate request number preview (for add form)
        const titleInput = document.querySelector('input[name="title"]');
        if (titleInput && !document.querySelector('input[name="ita_id"]')) {
            titleInput.addEventListener('input', function() {
                // Could show a preview of the request number
                console.log('Title updated:', this.value);
            });
        }

        // Priority color coding
        const prioritySelect = document.querySelector('select[name="priority"]');
        if (prioritySelect) {
            prioritySelect.addEventListener('change', function() {
                const colors = {
                    'low': 'border-gray-300',
                    'medium': 'border-blue-300',
                    'high': 'border-orange-300',
                    'urgent': 'border-red-300'
                };
                
                // Remove all color classes
                Object.values(colors).forEach(color => {
                    this.classList.remove(color);
                });
                
                // Add appropriate color
                this.classList.add(colors[this.value] || 'border-gray-300');
            });
            
            // Set initial color
            prioritySelect.dispatchEvent(new Event('change'));
        }

        // Estimated completion date validation
        const estimatedDateInput = document.querySelector('input[name="estimated_completion"]');
        if (estimatedDateInput) {
            estimatedDateInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    alert('วันที่คาดว่าจะเสร็จต้องไม่เป็นวันในอดีต');
                    this.value = '';
                }
            });
        }

        // Real-time character count for description
        const descriptionTextarea = document.querySelector('textarea[name="description"]');
        if (descriptionTextarea) {
            const maxLength = 1000;
            const counter = document.createElement('div');
            counter.className = 'text-sm text-gray-500 mt-1';
            descriptionTextarea.parentNode.appendChild(counter);
            
            function updateCounter() {
                const remaining = maxLength - descriptionTextarea.value.length;
                counter.textContent = `เหลือ ${remaining} ตัวอักษร`;
                
                if (remaining < 50) {
                    counter.className = 'text-sm text-red-500 mt-1';
                } else if (remaining < 100) {
                    counter.className = 'text-sm text-yellow-500 mt-1';
                } else {
                    counter.className = 'text-sm text-gray-500 mt-1';
                }
            }
            
            descriptionTextarea.addEventListener('input', updateCounter);
            descriptionTextarea.setAttribute('maxlength', maxLength);
            updateCounter();
        }

        // Auto-refresh status every 30 seconds for list view
        if (document.querySelector('table')) {
            setInterval(function() {
                // Simple check if page is still visible
                if (document.visibilityState === 'visible') {
                    console.log('Auto-refresh check for ITA status updates');
                    // Could implement real-time updates here
                }
            }, 30000);
        }

        console.log('🔧 ITA Management system loaded successfully!');
    </script>
</body>
</html><?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Require admin role
requireAdmin('../login.php');

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'ita';
$page_title = "จัดการ ITA Requests";

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Create ITA table if it doesn't exist
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS ita_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_number VARCHAR(20) UNIQUE NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        category ENUM('hardware', 'software', 'network', 'maintenance', 'other') DEFAULT 'other',
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        status ENUM('pending', 'in_progress', 'completed', 'rejected', 'cancelled') DEFAULT 'pending',
        requester_name VARCHAR(100) NOT NULL,
        requester_email VARCHAR(255) NOT NULL,
        requester_phone VARCHAR(20),
        department VARCHAR(100),
        location VARCHAR(255),
        assigned_to INT,
        attachments JSON,
        notes TEXT,
        estimated_completion DATE,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
    )");
} catch (Exception $e) {
    // Table creation failed, but continue
}

// Handle form submissions
$message = '';
$error = '';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_POST && $action) {
    try {
        switch ($action) {
            case 'add':
                $title = sanitizeInput($_POST['title'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $category = sanitizeInput($_POST['category'] ?? 'other');
                $priority = sanitizeInput($_POST['priority'] ?? 'medium');
                $requester_name = sanitizeInput($_POST['requester_name'] ?? '');
                $requester_email = sanitizeInput($_POST['requester_email'] ?? '');
                $requester_phone = sanitizeInput($_POST['requester_phone'] ?? '');
                $department = sanitizeInput($_POST['department'] ?? '');
                $location = sanitizeInput($_POST['location'] ?? '');
                $assigned_to = (int)($_POST['assigned_to'] ?? 0);
                $estimated_completion = $_POST['estimated_completion'] ?? null;
                
                if (empty($title) || empty($description) || empty($requester_name) || empty($requester_email)) {
                    $error = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
                } else {
                    // Generate request number
                    $request_number = 'ITA' . date('Ymd') . sprintf('%04d', rand(1, 9999));
                    
                    // Check if request number exists
                    $stmt = $conn->prepare("SELECT id FROM ita_requests WHERE request_number = ?");
                    $stmt->execute([$request_number]);
                    
                    // If exists, generate new one
                    while ($stmt->fetch()) {
                        $request_number = 'ITA' . date('Ymd') . sprintf('%04d', rand(1, 9999));
                        $stmt = $conn->prepare("SELECT id FROM ita_requests WHERE request_number = ?");
                        $stmt->execute([$request_number]);
                    }
                    
                    $stmt = $conn->prepare("
                        INSERT INTO ita_requests (request_number, title, description, category, priority, requester_name, requester_email, requester_phone, department, location, assigned_to, estimated_completion, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    if ($stmt->execute([$request_number, $title, $description, $category, $priority, $requester_name, $requester_email, $requester_phone, $department, $location, $assigned_to ?: null, $estimated_completion])) {
                        $ita_id = $conn->lastInsertId();
                        logActivity($conn, $_SESSION['user_id'], 'ita_created', 'ita_requests', $ita_id);
                        $message = "เพิ่ม ITA Request เรียบร้อยแล้ว (เลขที่: $request_number)";
                        $action = ''; // Reset action to show list
                    } else {
                        $error = "ไม่สามารถเพิ่ม ITA Request ได้";
                    }
                }
                break;
                
            case 'edit':
                $ita_id = (int)($_POST['ita_id'] ?? 0);
                $title = sanitizeInput($_POST['title'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $category = sanitizeInput($_POST['category'] ?? 'other');
                $priority = sanitizeInput($_POST['priority'] ?? 'medium');
                $status = sanitizeInput($_POST['status'] ?? 'pending');
                $requester_name = sanitizeInput($_POST['requester_name'] ?? '');
                $requester_email = sanitizeInput($_POST['requester_email'] ?? '');
                $requester_phone = sanitizeInput($_POST['requester_phone'] ?? '');
                $department = sanitizeInput($_POST['department'] ?? '');
                $location = sanitizeInput($_POST['location'] ?? '');
                $assigned_to = (int)($_POST['assigned_to'] ?? 0);
                $estimated_completion = $_POST['estimated_completion'] ?? null;
                $notes = sanitizeInput($_POST['notes'] ?? '');
                
                if (!$ita_id || empty($title) || empty($description)) {
                    $error = "ข้อมูลไม่ถูกต้อง";
                } else {
                    // Get old data for logging
                    $old_ita = getRecord($conn, 'ita_requests', $ita_id);
                    
                    // Set completed_at if status changed to completed
                    $completed_at = null;
                    if ($status === 'completed' && $old_ita['status'] !== 'completed') {
                        $completed_at = date('Y-m-d H:i:s');
                    }
                    
                    $update_query = "
                        UPDATE ita_requests 
                        SET title = ?, description = ?, category = ?, priority = ?, status = ?, 
                            requester_name = ?, requester_email = ?, requester_phone = ?, 
                            department = ?, location = ?, assigned_to = ?, estimated_completion = ?, 
                            notes = ?, updated_at = NOW()";
                    
                    $params = [$title, $description, $category, $priority, $status, $requester_name, $requester_email, $requester_phone, $department, $location, $assigned_to ?: null, $estimated_completion, $notes];
                    
                    if ($completed_at) {
                        $update_query .= ", completed_at = ?";
                        $params[] = $completed_at;
                    }
                    
                    $update_query .= " WHERE id = ?";
                    $params[] = $ita_id;
                    
                    $stmt = $conn->prepare($update_query);
                    
                    if ($stmt->execute($params)) {
                        logActivity($conn, $_SESSION['user_id'], 'ita_updated', 'ita_requests', $ita_id, $old_ita);
                        $message = "แก้ไข ITA Request เรียบร้อยแล้ว";
                        $action = ''; // Reset action to show list
                    } else {
                        $error = "ไม่สามารถแก้ไข ITA Request ได้";
                    }
                }
                break;
                
            case 'delete':
                $ita_id = (int)($_POST['ita_id'] ?? 0);
                if ($ita_id) {
                    $old_ita = getRecord($conn, 'ita_requests', $ita_id);
                    
                    if (deleteRecord($conn, 'ita_requests', $ita_id)) {
                        logActivity($conn, $_SESSION['user_id'], 'ita_deleted', 'ita_requests', $ita_id, $old_ita);
                        $message = "ลบ ITA Request เรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถลบ ITA Request ได้";
                    }
                }
                break;
                
            case 'update_status':
                $ita_id = (int)($_POST['ita_id'] ?? 0);
                $new_status = sanitizeInput($_POST['new_status'] ?? '');
                
                if ($ita_id && in_array($new_status, ['pending', 'in_progress', 'completed', 'rejected', 'cancelled'])) {
                    $update_query = "UPDATE ita_requests SET status = ?, updated_at = NOW()";
                    $params = [$new_status, $ita_id];
                    
                    // Set completed_at if status is completed
                    if ($new_status === 'completed') {
                        $update_query .= ", completed_at = NOW()";
                    }
                    
                    $update_query .= " WHERE id = ?";
                    
                    $stmt = $conn->prepare($update_query);
                    if ($stmt->execute($params)) {
                        logActivity($conn, $_SESSION['user_id'], 'ita_status_changed', 'ita_requests', $ita_id);
                        $message = "เปลี่ยนสถานะ ITA Request เรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถเปลี่ยนสถานะได้";
                    }
                }
                break;
        }
    } catch (Exception $e) {
        logError($e->getMessage(), __FILE__, __LINE__);
        $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
    }
}

// Get ITA for editing
$edit_ita = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_ita = getRecord($conn, 'ita_requests', (int)$_GET['id']);
    if (!$edit_ita) {
        $error = "ไม่พบ ITA Request ที่ต้องการแก้ไข";
        $action = '';
    }
}

// Get available technicians for assignment
$technicians = [];
try {
    $stmt = $conn->prepare("SELECT id, first_name, last_name FROM users WHERE role IN ('admin', 'editor') AND is_active = 1 ORDER BY first_name, last_name");
    $stmt->execute();
    $technicians = $stmt->fetchAll();
} catch (Exception $e) {
    // No technicians available
}

// Pagination and filtering for list view
if (empty($action)) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    
    // Filter options
    $status_filter = $_GET['status'] ?? '';
    $priority_filter = $_GET['priority'] ?? '';
    $category_filter = $_GET['category'] ?? '';
    $search = sanitizeInput($_GET['search'] ?? '');
    
    // Build query
    $where_conditions = ["1=1"];
    $params = [];
    
    if ($status_filter) {
        $where_conditions[] = "i.status = ?";
        $params[] = $status_filter;
    }
    
    if ($priority_filter) {
        $where_conditions[] = "i.priority = ?";
        $params[] = $priority_filter;
    }
    
    if ($category_filter) {
        $where_conditions[] = "i.category = ?";
        $params[] = $category_filter;
    }
    
    if ($search) {
        $where_conditions[] = "(i.title LIKE ? OR i.description LIKE ? OR i.request_number LIKE ? OR i.requester_name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    try {
        $count_query = "
            SELECT COUNT(*) 
            FROM ita_requests i 
            WHERE $where_clause
        ";
        $stmt = $conn->prepare($count_query);
        $stmt->execute($params);
        $total_records = $stmt->fetchColumn();
        
        // Get records
        $query = "
            SELECT i.*, u.first_name as assignee_first_name, u.last_name as assignee_last_name 
            FROM ita_requests i 
            LEFT JOIN users u ON i.assigned_to = u.id 
            WHERE $where_clause
            ORDER BY i.created_at DESC 
            LIMIT $per_page OFFSET $offset
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $ita_list = $stmt->fetchAll();
        
        $pagination = getPagination($total_records, $per_page, $page);
    } catch (Exception $e) {
        // Table doesn't exist
        $total_records = 0;
        $ita_list = [];
        $pagination = ['total_pages' => 0];
    }
}

// Get statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'urgent' => 0
];

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ita_requests");
    $stmt->execute();
    $stats['total'] = $stmt->fetchColumn() ?? 0;
    
    foreach (['pending', 'in_progress', 'completed'] as $status) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ita_requests WHERE status = ?");
        $stmt->execute([$status]);
        $stats[$status] = $stmt->fetchColumn() ?? 0;
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ita_requests WHERE priority = 'urgent'");
    $stmt->execute();
    $stats['urgent'] = $stmt->fetchColumn() ?? 0;
} catch (Exception $e) {
    // Keep default values
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - โรงพยาบาลทุ่งหัวช้าง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);    
        }