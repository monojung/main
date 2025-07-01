<a href="?action=add" class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-xl transition duration-300 shadow-lg font-medium flex items-center space-x-2">
                        <span class="text-lg">👤</span>
                        <span>เพิ่มผู้ใช้ใหม่</span>
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?></div>
                        <div class="text-sm text-gray-600">ทั้งหมด</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-green-600"><?php echo number_format($stats['active']); ?></div>
                        <div class="text-sm text-gray-600">ใช้งานได้</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-red-600"><?php echo number_format($stats['inactive']); ?></div>
                        <div class="text-sm text-gray-600">ปิดใช้งาน</div>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-4 lg:p-6 hover-lift fade-in">
                    <div class="text-center">
                        <div class="text-2xl lg:text-3xl font-bold text-purple-600"><?php echo number_format($stats['admins']); ?></div>
                        <div class="text-sm text-gray-600">ผู้ดูแลระบบ</div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="glass-card rounded-2xl p-6 mb-8 fade-in">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🔍 ค้นหา</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="ชื่อ, นามสกุล, ชื่อผู้ใช้..." 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">👤 บทบาท</label>
                        <select name="role" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">ทั้งหมด</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>ผู้ดูแลระบบ</option>
                            <option value="editor" <?php echo $role_filter === 'editor' ? 'selected' : ''; ?>>บรรณาธิการ</option>
                            <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>ผู้ใช้ทั่วไป</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📊 สถานะ</label>
                        <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">ทั้งหมด</option>
                            <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>ใช้งานได้</option>
                            <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>ปิดใช้งาน</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🏢 แผนก</label>
                        <select name="department" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">ทั้งหมด</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo $department_filter == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                        <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 font-medium">
                            🔍 ค้นหา
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="glass-card rounded-2xl overflow-hidden shadow-xl fade-in">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">📋 รายการผู้ใช้งาน</h3>
                        <div class="text-sm text-gray-600">
                            แสดง <?php echo number_format(count($users_list)); ?> จาก <?php echo number_format($total_records); ?> รายการ
                        </div>
                    </div>
                </div>

                <?php if (empty($users_list)): ?>
                <div class="p-12 text-center">
                    <div class="text-6xl mb-4">👥</div>
                    <p class="text-gray-500 text-lg font-medium">ไม่มีผู้ใช้งาน</p>
                    <p class="text-gray-400 text-sm mb-6">เริ่มต้นโดยการเพิ่มผู้ใช้ใหม่</p>
                    <a href="?action=add" class="bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 transition duration-300 inline-flex items-center space-x-2">
                        <span>👤</span>
                        <span>เพิ่มผู้ใช้ใหม่</span>
                    </a>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้ใช้</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">บทบาท</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">แผนก</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เข้าสู่ระบบล่าสุด</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users_list as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                            <span class="text-white font-semibold text-lg">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">คุณ</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                @<?php echo htmlspecialchars($user['username']); ?>
                                            </div>
                                            <div class="text-sm text-gray-400">
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $role_colors = [
                                        'admin' => 'bg-red-100 text-red-800',
                                        'editor' => 'bg-blue-100 text-blue-800',
                                        'user' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $role_labels = [
                                        'admin' => '👑 ผู้ดูแลระบบ',
                                        'editor' => '✏️ บรรณาธิการ',
                                        'user' => '👤 ผู้ใช้ทั่วไป'
                                    ];
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $role_colors[$user['role']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $role_labels[$user['role']] ?? $user['role']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo htmlspecialchars($user['department_name'] ?? 'ไม่ระบุ'); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($user['is_active']): ?>
                                    <span class="flex items-center text-green-600">
                                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                        ใช้งานได้
                                    </span>
                                    <?php else: ?>
                                    <span class="flex items-center text-red-600">
                                        <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                                        ปิดใช้งาน
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo $user['last_login'] ? safeFormatThaiDateTime($user['last_login']) : 'ยังไม่เคยเข้าสู่ระบบ'; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $user['id']; ?>" 
                                           class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            ✏️ แก้ไข
                                        </a>
                                        
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="toggleStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active'] ? 0 : 1; ?>)"
                                                class="bg-<?php echo $user['is_active'] ? 'yellow' : 'green'; ?>-100 text-<?php echo $user['is_active'] ? 'yellow' : 'green'; ?>-600 hover:bg-<?php echo $user['is_active'] ? 'yellow' : 'green'; ?>-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            <?php echo $user['is_active'] ? '⏸️ ปิดใช้งาน' : '▶️ เปิดใช้งาน'; ?>
                                        </button>
                                        
                                        <button onclick="confirmDelete(<?php echo $user['id']; ?>)"
                                                class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg transition duration-200 text-xs">
                                            🗑️ ลบ
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
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
                               class="<?php echo $i === $page ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-500 hover:text-gray-700'; ?> px-4 py-2 rounded-lg transition duration-200">
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
                    <a href="users.php" class="text-white hover:text-gray-200 transition duration-200">
                        <span class="text-2xl">←</span>
                    </a>
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white">
                            <?php echo $action === 'add' ? '👤 เพิ่มผู้ใช้ใหม่' : '✏️ แก้ไขข้อมูลผู้ใช้'; ?>
                        </h2>
                        <p class="text-gray-200">
                            <?php echo $action === 'add' ? 'สร้างบัญชีผู้ใช้ใหม่และกำหนดสิทธิ์' : 'แก้ไขข้อมูลและสิทธิ์ผู้ใช้'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8 fade-in">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($edit_user): ?>
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Personal Information -->
                    <div class="bg-gray-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">👑 บทบาทและสิทธิ์</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">บทบาท</label>
                                <select name="role" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="user" <?php echo ($edit_user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>ผู้ใช้ทั่วไป</option>
                                    <option value="editor" <?php echo ($edit_user['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>บรรณาธิการ</option>
                                    <option value="admin" <?php echo ($edit_user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>ผู้ดูแลระบบ</option>
                                </select>
                                <p class="text-sm text-gray-500 mt-2">
                                    ผู้ใช้ทั่วไป: ดูข้อมูล | บรรณาธิการ: จัดการข่าว | ผู้ดูแลระบบ: ทุกสิทธิ์
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">สถานะการใช้งาน</label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" 
                                               <?php echo ($edit_user['is_active'] ?? 1) ? 'checked' : ''; ?>
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-3 text-sm font-medium text-gray-700">เปิดใช้งาน</span>
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">
                                    ผู้ใช้ที่ปิดใช้งานจะไม่สามารถเข้าสู่ระบบได้
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="users.php" 
                           class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-6 py-3 rounded-xl transition duration-300 font-medium">
                            ยกเลิก
                        </a>
                        <button type="submit" 
                                class="bg-indigo-600 text-white hover:bg-indigo-700 px-6 py-3 rounded-xl transition duration-300 font-medium flex items-center space-x-2">
                            <span><?php echo $action === 'add' ? '👤' : '💾'; ?></span>
                            <span><?php echo $action === 'add' ? 'เพิ่มผู้ใช้' : 'บันทึกการแก้ไข'; ?></span>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Status toggle function
        function toggleStatus(userId, newStatus) {
            const statusText = newStatus ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
            if (confirm(`คุณต้องการ${statusText}ผู้ใช้นี้หรือไม่?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="user_id" value="${userId}">
                    <input type="hidden" name="new_status" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Delete confirmation
        function confirmDelete(userId) {
            if (confirm('คุณต้องการลบผู้ใช้นี้หรือไม่? การดำเนินการนี้ไม่สามารถย้อนกลับได้')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${userId}">
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
                    const firstName = document.querySelector('input[name="first_name"]').value.trim();
                    const lastName = document.querySelector('input[name="last_name"]').value.trim();
                    const username = document.querySelector('input[name="username"]').value.trim();
                    const email = document.querySelector('input[name="email"]').value.trim();
                    
                    if (!firstName || !lastName || !username || !email) {
                        e.preventDefault();
                        alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                        return false;
                    }
                    
                    // Email validation
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        e.preventDefault();
                        alert('กรุณากรอกอีเมลที่ถูกต้อง');
                        return false;
                    }
                    
                    // Password validation for add action
                    const action = document.querySelector('input[name="action"]').value;
                    if (action === 'add') {
                        const password = document.querySelector('input[name="password"]').value;
                        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                        
                        if (!password) {
                            e.preventDefault();
                            alert('กรุณากรอกรหัสผ่าน');
                            return false;
                        }
                        
                        if (password.length < 6) {
                            e.preventDefault();
                            alert('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
                            return false;
                        }
                        
                        if (password !== confirmPassword) {
                            e.preventDefault();
                            alert('รหัสผ่านไม่ตรงกัน');
                            return false;
                        }
                    } else if (action === 'edit') {
                        const newPassword = document.querySelector('input[name="new_password"]').value;
                        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                        
                        if (newPassword) {
                            if (newPassword.length < 6) {
                                e.preventDefault();
                                alert('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
                                return false;
                            }
                            
                            if (newPassword !== confirmPassword) {
                                e.preventDefault();
                                alert('รหัสผ่านใหม่ไม่ตรงกัน');
                                return false;
                            }
                        }
                    }
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span>กำลังบันทึก...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after 10 seconds as fallback
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                });
            }
        });

        // Username validation (real-time)
        document.querySelector('input[name="username"]').addEventListener('input', function(e) {
            const username = e.target.value;
            const regex = /^[a-zA-Z0-9_-]+$/;
            
            if (username && !regex.test(username)) {
                e.target.setCustomValidity('ชื่อผู้ใช้สามารถใช้ได้เฉพาะตัวอักษร ตัวเลข _ และ - เท่านั้น');
            } else {
                e.target.setCustomValidity('');
            }
        });

        // Phone number formatting
        document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
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

        // Role description update
        document.querySelector('select[name="role"]').addEventListener('change', function(e) {
            const role = e.target.value;
            const descriptions = {
                'user': 'ผู้ใช้ทั่วไป: สามารถดูข้อมูลและใช้งานฟีเจอร์พื้นฐาน',
                'editor': 'บรรณาธิการ: สามารถจัดการข่าวสาร เพิ่ม แก้ไข และลบข่าว',
                'admin': 'ผู้ดูแลระบบ: มีสิทธิ์เต็มในการจัดการระบบทั้งหมด'
            };
            
            const description = e.target.parentNode.querySelector('p');
            if (description) {
                description.textContent = descriptions[role] || '';
            }
        });

        // Password strength indicator
        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return strength;
        }

        const passwordFields = document.querySelectorAll('input[type="password"]');
        passwordFields.forEach(field => {
            field.addEventListener('input', function(e) {
                if (e.target.name.includes('password') && !e.target.name.includes('confirm')) {
                    const strength = checkPasswordStrength(e.target.value);
                    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
                    const labels = ['อ่อนมาก', 'อ่อน', 'ปานกลาง', 'แข็งแกร่ง', 'แข็งแกร่งมาก'];
                    
                    let indicator = e.target.parentNode.querySelector('.password-strength');
                    if (!indicator && e.target.value) {
                        indicator = document.createElement('div');
                        indicator.className = 'password-strength mt-2';
                        e.target.parentNode.appendChild(indicator);
                    }
                    
                    if (indicator) {
                        if (e.target.value) {
                            indicator.innerHTML = `
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="${colors[strength]} h-2 rounded-full transition-all duration-300" style="width: ${(strength + 1) * 20}%"></div>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">ความแข็งแกร่งรหัสผ่าน: ${labels[strength]}</p>
                            `;
                        } else {
                            indicator.remove();
                        }
                    }
                }
            });
        });

        console.log('👥 User management system loaded successfully!');
    </script>
</body>
</html>-lg font-semibold text-gray-800 mb-4">👤 ข้อมูลส่วนตัว</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อ *</label>
                                <input type="text" name="first_name" required 
                                       value="<?php echo htmlspecialchars($edit_user['first_name'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="กรอกชื่อ">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">นามสกุล *</label>
                                <input type="text" name="last_name" required 
                                       value="<?php echo htmlspecialchars($edit_user['last_name'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="กรอกนามสกุล">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">อีเมล *</label>
                                <input type="email" name="email" required 
                                       value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="example@hospital.com">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมายเลขโทรศัพท์</label>
                                <input type="tel" name="phone" 
                                       value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="0xx-xxx-xxxx">
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="bg-blue-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">🔐 ข้อมูลบัญชี</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อผู้ใช้ *</label>
                                <input type="text" name="username" required 
                                       value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="username">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">แผนก</label>
                                <select name="department_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">เลือกแผนก</option>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo ($edit_user['department_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="bg-yellow-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">🔒 รหัสผ่าน</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php echo $action === 'add' ? 'รหัสผ่าน *' : 'รหัสผ่านใหม่ (เว้นว่างหากไม่เปลี่ยน)'; ?>
                                </label>
                                <input type="password" name="<?php echo $action === 'add' ? 'password' : 'new_password'; ?>" 
                                       <?php echo $action === 'add' ? 'required' : ''; ?>
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="อย่างน้อย 6 ตัวอักษร">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ยืนยันรหัสผ่าน</label>
                                <input type="password" name="confirm_password" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="กรอกรหัสผ่านอีกครั้ง">
                            </div>
                        </div>
                    </div>

                    <!-- Role & Permissions -->
                    <div class="bg-purple-50 p-6 rounded-xl">
                        <h4 class="text    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white shadow-2xl sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                        <span class="text-white font-bold text-xl">👥</span>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold">จัดการผู้ใช้งาน</h1>
                        <p class="text-indigo-200 text-sm">ระบบจัดการบัญชีผู้ใช้และสิทธิ์</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-indigo-200"><?php echo date('d/m/Y H:i'); ?></p>
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
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">👥 จัดการผู้ใช้งาน</h2>
                        <p class="text-gray-200">จัดการบัญชีผู้ใช้ สิทธิ์การเข้าถึง และข้อมูลส่วนตัว</p>
                    </div>
                    <a href="?action=add" class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-xl<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Require admin role
requireAdmin('../login.php');

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'users';
$page_title = "จัดการผู้ใช้งาน";

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$error = '';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_POST && $action) {
    try {
        switch ($action) {
            case 'add':
                $username = sanitizeInput($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                $email = sanitizeInput($_POST['email'] ?? '');
                $first_name = sanitizeInput($_POST['first_name'] ?? '');
                $last_name = sanitizeInput($_POST['last_name'] ?? '');
                $role = sanitizeInput($_POST['role'] ?? 'user');
                $department_id = (int)($_POST['department_id'] ?? 0);
                $phone = sanitizeInput($_POST['phone'] ?? '');
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Validation
                if (empty($username) || empty($password) || empty($email) || empty($first_name) || empty($last_name)) {
                    $error = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
                } elseif ($password !== $confirm_password) {
                    $error = "รหัสผ่านไม่ตรงกัน";
                } elseif (strlen($password) < 6) {
                    $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
                } else {
                    // Check if username or email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    
                    if ($stmt->fetch()) {
                        $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว";
                    } else {
                        $password_hash = hashPassword($password);
                        
                        $stmt = $conn->prepare("
                            INSERT INTO users (username, password_hash, email, first_name, last_name, role, department_id, phone, is_active, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        
                        if ($stmt->execute([$username, $password_hash, $email, $first_name, $last_name, $role, $department_id ?: null, $phone, $is_active])) {
                            $user_id = $conn->lastInsertId();
                            logActivity($conn, $_SESSION['user_id'], 'user_created', 'users', $user_id);
                            $message = "เพิ่มผู้ใช้เรียบร้อยแล้ว";
                            $action = ''; // Reset action to show list
                        } else {
                            $error = "ไม่สามารถเพิ่มผู้ใช้ได้";
                        }
                    }
                }
                break;
                
            case 'edit':
                $user_id = (int)($_POST['user_id'] ?? 0);
                $username = sanitizeInput($_POST['username'] ?? '');
                $email = sanitizeInput($_POST['email'] ?? '');
                $first_name = sanitizeInput($_POST['first_name'] ?? '');
                $last_name = sanitizeInput($_POST['last_name'] ?? '');
                $role = sanitizeInput($_POST['role'] ?? 'user');
                $department_id = (int)($_POST['department_id'] ?? 0);
                $phone = sanitizeInput($_POST['phone'] ?? '');
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (!$user_id || empty($username) || empty($email) || empty($first_name) || empty($last_name)) {
                    $error = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
                } elseif ($new_password && $new_password !== $confirm_password) {
                    $error = "รหัสผ่านใหม่ไม่ตรงกัน";
                } elseif ($new_password && strlen($new_password) < 6) {
                    $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
                } else {
                    // Check if username or email already exists (excluding current user)
                    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                    $stmt->execute([$username, $email, $user_id]);
                    
                    if ($stmt->fetch()) {
                        $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว";
                    } else {
                        // Get old data for logging
                        $old_user = getRecord($conn, 'users', $user_id);
                        
                        // Prepare update query
                        if ($new_password) {
                            $password_hash = hashPassword($new_password);
                            $stmt = $conn->prepare("
                                UPDATE users 
                                SET username = ?, password_hash = ?, email = ?, first_name = ?, last_name = ?, role = ?, department_id = ?, phone = ?, is_active = ?, updated_at = NOW()
                                WHERE id = ?
                            ");
                            $params = [$username, $password_hash, $email, $first_name, $last_name, $role, $department_id ?: null, $phone, $is_active, $user_id];
                        } else {
                            $stmt = $conn->prepare("
                                UPDATE users 
                                SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, department_id = ?, phone = ?, is_active = ?, updated_at = NOW()
                                WHERE id = ?
                            ");
                            $params = [$username, $email, $first_name, $last_name, $role, $department_id ?: null, $phone, $is_active, $user_id];
                        }
                        
                        if ($stmt->execute($params)) {
                            logActivity($conn, $_SESSION['user_id'], 'user_updated', 'users', $user_id, $old_user);
                            $message = "แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว";
                            $action = ''; // Reset action to show list
                        } else {
                            $error = "ไม่สามารถแก้ไขข้อมูลผู้ใช้ได้";
                        }
                    }
                }
                break;
                
            case 'delete':
                $user_id = (int)($_POST['user_id'] ?? 0);
                if ($user_id) {
                    // Prevent deleting self
                    if ($user_id == $_SESSION['user_id']) {
                        $error = "ไม่สามารถลบบัญชีของตนเองได้";
                    } else {
                        $old_user = getRecord($conn, 'users', $user_id);
                        
                        if (deleteRecord($conn, 'users', $user_id)) {
                            logActivity($conn, $_SESSION['user_id'], 'user_deleted', 'users', $user_id, $old_user);
                            $message = "ลบผู้ใช้เรียบร้อยแล้ว";
                        } else {
                            $error = "ไม่สามารถลบผู้ใช้ได้";
                        }
                    }
                }
                break;
                
            case 'toggle_status':
                $user_id = (int)($_POST['user_id'] ?? 0);
                $new_status = (int)($_POST['new_status'] ?? 0);
                
                if ($user_id) {
                    // Prevent disabling self
                    if ($user_id == $_SESSION['user_id'] && $new_status == 0) {
                        $error = "ไม่สามารถปิดใช้งานบัญชีของตนเองได้";
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?");
                        if ($stmt->execute([$new_status, $user_id])) {
                            logActivity($conn, $_SESSION['user_id'], 'user_status_changed', 'users', $user_id);
                            $message = $new_status ? "เปิดใช้งานผู้ใช้เรียบร้อยแล้ว" : "ปิดใช้งานผู้ใช้เรียบร้อยแล้ว";
                        } else {
                            $error = "ไม่สามารถเปลี่ยนสถานะผู้ใช้ได้";
                        }
                    }
                }
                break;
        }
    } catch (Exception $e) {
        logError($e->getMessage(), __FILE__, __LINE__);
        $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
    }
}

// Get user for editing
$edit_user = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_user = getRecord($conn, 'users', (int)$_GET['id']);
    if (!$edit_user) {
        $error = "ไม่พบผู้ใช้ที่ต้องการแก้ไข";
        $action = '';
    }
}

// Get departments for form
$departments = [];
try {
    $stmt = $conn->prepare("SELECT id, name FROM departments ORDER BY name");
    $stmt->execute();
    $departments = $stmt->fetchAll();
} catch (Exception $e) {
    // Departments table might not exist
}

// Pagination and filtering for list view
if (empty($action)) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    
    // Filter options
    $role_filter = $_GET['role'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $department_filter = $_GET['department'] ?? '';
    $search = sanitizeInput($_GET['search'] ?? '');
    
    // Build query
    $where_conditions = ["1=1"];
    $params = [];
    
    if ($role_filter) {
        $where_conditions[] = "u.role = ?";
        $params[] = $role_filter;
    }
    
    if ($status_filter !== '') {
        $where_conditions[] = "u.is_active = ?";
        $params[] = (int)$status_filter;
    }
    
    if ($department_filter) {
        $where_conditions[] = "u.department_id = ?";
        $params[] = $department_filter;
    }
    
    if ($search) {
        $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_query = "
        SELECT COUNT(*) 
        FROM users u 
        LEFT JOIN departments d ON u.department_id = d.id 
        WHERE $where_clause
    ";
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetchColumn();
    
    // Get records
    $query = "
        SELECT u.*, d.name as department_name 
        FROM users u 
        LEFT JOIN departments d ON u.department_id = d.id 
        WHERE $where_clause
        ORDER BY u.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $users_list = $stmt->fetchAll();
    
    $pagination = getPagination($total_records, $per_page, $page);
}

// Get statistics
$stats = [
    'total' => getTotalUsers($conn),
    'active' => 0,
    'inactive' => 0,
    'admins' => 0
];

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
    $stmt->execute();
    $stats['active'] = $stmt->fetchColumn() ?? 0;
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE is_active = 0");
    $stmt->execute();
    $stats['inactive'] = $stmt->fetchColumn() ?? 0;
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1");
    $stmt->execute();
    $stats['admins'] = $stmt->fetchColumn() ?? 0;
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
    <nav class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white shadow-