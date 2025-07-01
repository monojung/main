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

            <!-- Page Header -->
            <div class="mb-8 fade-in">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-2">⚙️ ตั้งค่าระบบ</h2>
                <p class="text-gray-200">จัดการการกำหนดค่าและการตั้งค่าระบบต่างๆ</p>
            </div>

            <!-- Tabs Navigation -->
            <div class="glass-card rounded-2xl mb-8 fade-in">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" id="settings-tabs">
                        <button class="tab-button active py-4 px-2 border-b-2 border-orange-500 font-medium text-orange-600" data-tab="general">
                            🏠 ทั่วไป
                        </button>
                        <button class="tab-button py-4 px-2 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="security">
                            🔒 ความปลอดภัย
                        </button>
                        <button class="tab-button py-4 px-2 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="email">
                            📧 อีเมล
                        </button>
                        <button class="tab-button py-4 px-2 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="backup">
                            💾 สำรองข้อมูล
                        </button>
                        <button class="tab-button py-4 px-2 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="maintenance">
                            🔧 บำรุงรักษา
                        </button>
                        <button class="tab-button py-4 px-2 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="system">
                            💻 ข้อมูลระบบ
                        </button>
                    </nav>
                </div>

                <!-- General Settings Tab -->
                <div id="general-tab" class="tab-content active p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">🏠 การตั้งค่าทั่วไป</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="general">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อเว็บไซต์ *</label>
                                <input type="text" name="site_name" required 
                                       value="<?php echo htmlspecialchars($settings['site_name']); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">อีเมลผู้ดูแลระบบ *</label>
                                <input type="email" name="admin_email" required 
                                       value="<?php echo htmlspecialchars($settings['admin_email']); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">คำอธิบายเว็บไซต์</label>
                                <textarea name="site_description" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                          placeholder="อธิบายเกี่ยวกับเว็บไซต์"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">เขตเวลา</label>
                                <select name="timezone" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="Asia/Bangkok" <?php echo $settings['timezone'] === 'Asia/Bangkok' ? 'selected' : ''; ?>>Asia/Bangkok</option>
                                    <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">จำนวนรายการต่อหน้า</label>
                                <input type="number" name="per_page" min="5" max="100" 
                                       value="<?php echo $settings['per_page']; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-orange-600 text-white hover:bg-orange-700 px-6 py-3 rounded-xl transition duration-300 font-medium">
                                💾 บันทึก
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Settings Tab -->
                <div id="security-tab" class="tab-content p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">🔒 การตั้งค่าความปลอดภัย</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="security">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">เวลาหมดอายุเซสชัน (วินาที)</label>
                                <input type="number" name="session_timeout" min="300" max="86400" 
                                       value="<?php echo $settings['session_timeout']; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <p class="text-sm text-gray-500 mt-1">ค่าปัจจุบัน: <?php echo $settings['session_timeout']; ?> วินาที (<?php echo round($settings['session_timeout']/60); ?> นาที)</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">จำนวนครั้งที่พยายามเข้าสู่ระบบ</label>
                                <input type="number" name="max_login_attempts" min="3" max="10" 
                                       value="<?php echo $settings['max_login_attempts']; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">เวลาล็อคบัญชี (วินาที)</label>
                                <input type="number" name="lockout_time" min="60" max="3600" 
                                       value="<?php echo $settings['lockout_time']; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <p class="text-sm text-gray-500 mt-1">ค่าปัจจุบัน: <?php echo round($settings['lockout_time']/60); ?> นาที</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ความยาวรหัสผ่านขั้นต่ำ</label>
                                <input type="number" name="password_min_length" min="4" max="20" 
                                       value="<?php echo $settings['password_min_length']; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <div class="bg-gray-50 p-4 rounded-xl">
                                    <h4 class="font-medium text-gray-800 mb-3">🔐 ตั้งค่าเพิ่มเติม</h4>
                                    <div class="space-y-3">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="require_email_verification" value="1" 
                                                   <?php echo $settings['require_email_verification'] ? 'checked' : ''; ?>
                                                   class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            <span class="ml-3 text-sm font-medium text-gray-700">ต้องยืนยันอีเมลก่อนใช้งาน</span>
                                        </label>
                                        
                                        <label class="flex items-center">
                                            <input type="checkbox" name="enable_2fa" value="1" 
                                                   <?php echo $settings['enable_2fa'] ? 'checked' : ''; ?>
                                                   class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            <span class="ml-3 text-sm font-medium text-gray-700">เปิดใช้งานการยืนยันตัวตนสองขั้นตอน (2FA)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-orange-600 text-white hover:bg-orange-700 px-6 py-3 rounded-xl transition duration-300 font-medium">
                                🔒 บันทึก
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Email Settings Tab -->
                <div id="email-tab" class="tab-content p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">📧 การตั้งค่าอีเมล</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="email">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                                <input type="text" name="smtp_host" 
                                       value="<?php echo htmlspecialchars($settings['smtp_host']); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="smtp.gmail.com">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                                <input type="number" name="smtp_port" min="1" max="65535" 
                                       value="<?php echo $settings['smtp_port']; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                                <input type="text" name="smtp_username" 
                                       value="<?php echo htmlspecialchars($settings['smtp_username']); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                                <input type="password" name="smtp_password" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       placeholder="เว้นว่างหากไม่เปลี่ยน">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">การเข้ารหัส</label>
                                <select name="smtp_encryption" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="" <?php echo $settings['smtp_encryption'] === '' ? 'selected' : ''; ?>>ไม่เข้ารหัส</option>
                                    <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">อีเมลผู้ส่ง</label>
                                <input type="email" name="from_email" 
                                       value="<?php echo htmlspecialchars($settings['from_email']); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อผู้ส่ง</label>
                                <input type="text" name="from_name" 
                                       value="<?php echo htmlspecialchars($settings['from_name']); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                        </div>
                        
                        <div class="flex justify-between mt-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">ทดสอบการส่งอีเมล</h4>
                                <div class="flex space-x-2">
                                    <input type="email" name="test_email" placeholder="อีเมลสำหรับทดสอบ" 
                                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" name="action" value="test_email" 
                                            class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg transition duration-300">
                                        📨 ทดสอบ
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="bg-orange-600 text-white hover:bg-orange-700 px-6 py-3 rounded-xl transition duration-300 font-medium">
                                📧 บันทึก
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Backup Settings Tab -->
                <div id="backup-tab" class="tab-content p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">💾 การตั้งค่าสำรองข้อมูล</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="backup">
                        <div class="space-y-6">
                            <div class="bg-blue-50 p-4 rounded-xl">
                                <label class="flex items-center">
                                    <input type="checkbox" name="auto_backup" value="1" 
                                           <?php echo $settings['auto_backup'] ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700">เปิดใช้งานการสำรองข้อมูลอัตโนมัติ</span>
                                </label>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ความถี่ในการสำรองข้อมูล</label>
                                    <select name="backup_frequency" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                        <option value="daily" <?php echo $settings['backup_frequency'] === 'daily' ? 'selected' : ''; ?>>รายวัน</option>
                                        <option value="weekly" <?php echo $settings['backup_frequency'] === 'weekly' ? 'selected' : ''; ?>>รายสัปดาห์</option>
                                        <option value="monthly" <?php echo $settings['backup_frequency'] === 'monthly' ? 'selected' : ''; ?>>รายเดือน</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">เก็บข้อมูลสำรอง (วัน)</label>
                                    <input type="number" name="backup_retention" min="1" max="365" 
                                           value="<?php echo $settings['backup_retention']; ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">โฟลเดอร์เก็บข้อมูลสำรอง</label>
                                    <input type="text" name="backup_path" 
                                           value="<?php echo htmlspecialchars($settings['backup_path']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-orange-600 text-white hover:bg-orange-700 px-6 py-3 rounded-xl transition duration-300 font-medium">
                                💾 บันทึก
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Maintenance Tab -->
                <div id="maintenance-tab" class="tab-content p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">🔧 โหมดบำรุงรักษา</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="maintenance">
                        <div class="space-y-6">
                            <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-200">
                                <label class="flex items-center">
                                    <input type="checkbox" name="maintenance_mode" value="1" 
                                           <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700">เปิดใช้งานโหมดบำรุงรักษา</span>
                                </label>
                                <p class="text-sm text-yellow-700 mt-2">⚠️ ผู้ใช้ทั่วไปจะไม่สามารถเข้าถึงเว็บไซต์ได้</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ข้อความแจ้งผู้ใช้</label>
                                <textarea name="maintenance_message" rows="4" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                          placeholder="ข้อความที่แสดงให้ผู้ใช้เห็นในโหมดบำรุงรักษา"><?php echo htmlspecialchars($settings['maintenance_message']); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">IP Address ที่อนุญาต</label>
                                <textarea name="allowed_ips" rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                          placeholder="192.168.1.1&#10;10.0.0.1&#10;(หนึ่ง IP ต่อบรรทัด)"><?php echo htmlspecialchars($settings['allowed_ips']); ?></textarea>
                                <p class="text-sm text-gray-500 mt-2">IP Address เหล่านี้จะสามารถเข้าถึงเว็บไซต์ได้แม้ในโหมดบำรุงรักษา</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-orange-600 text-white hover:bg-orange-700 px-6 py-3 rounded-xl transition duration-300 font-medium">
                                🔧 บันทึก
                            </button>
                        </div>
                    </form>
                </div>

                <!-- System Info Tab -->
                <div id="system-tab" class="tab-content p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">💻 ข้อมูลระบบ</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-blue-50 p-4 rounded-xl">
                            <h4 class="font-semibold text-blue-800 mb-3">🖥️ เซิร์ฟเวอร์</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>PHP Version:</span>
                                    <span class="font-mono"><?php echo $system_info['php_version']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Server Software:</span>
                                    <span class="font-mono text-xs"><?php echo $system_info['server_software']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>MySQL Version:</span>
                                    <span class="font-mono"><?php echo $system_info['mysql_version']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-xl">
                            <h4 class="font-semibold text-green-800 mb-3">⚙️ การกำหนดค่า</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Max Upload Size:</span>
                                    <span class="font-mono"><?php echo $system_info['max_upload_size']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Max Execution Time:</span>
                                    <span class="font-mono"><?php echo $system_info['max_execution_time']; ?>s</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Memory Limit:</span>
                                    <span class="font-mono"><?php echo $system_info['memory_limit']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Timezone:</span>
                                    <span class="font-mono"><?php echo $system_info['timezone']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-xl">
                            <h4 class="font-semibold text-purple-800 mb-3">💾 พื้นที่เก็บข้อมูล</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Disk Free Space:</span>
                                    <span class="font-mono"><?php echo $system_info['disk_space']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-orange-50 p-4 rounded-xl">
                            <h4 class="font-semibold text-orange-800 mb-3">🔧 เครื่องมือ</h4>
                            <div class="space-y-2">
                                <form method="POST" class="inline">
                                    <button type="submit" name="action" value="clear_cache" 
                                            class="bg-orange-600 text-white hover:bg-orange-700 px-4 py-2 rounded-lg transition duration-300 text-sm">
                                        🗑️ ล้างแคช
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional System Status -->
                    <div class="bg-gray-50 p-6 rounded-xl">
                        <h4 class="font-semibold text-gray-800 mb-4">📊 สถานะระบบ</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-green-100 rounded-lg">
                                <div class="text-2xl mb-2">✅</div>
                                <div class="text-sm font-medium text-green-800">ฐานข้อมูล</div>
                                <div class="text-xs text-green-600">เชื่อมต่อปกติ</div>
                            </div>
                            
                            <div class="text-center p-4 bg-blue-100 rounded-lg">
                                <div class="text-2xl mb-2">🌐</div>
                                <div class="text-sm font-medium text-blue-800">เว็บเซิร์ฟเวอร์</div>
                                <div class="text-xs text-blue-600">ทำงานปกติ</div>
                            </div>
                            
                            <div class="text-center p-4 bg-purple-100 rounded-lg">
                                <div class="text-2xl mb-2">📁</div>
                                <div class="text-sm font-medium text-purple-800">ระบบไฟล์</div>
                                <div class="text-xs text-purple-600">พร้อมใช้งาน</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'border-orange-500', 'text-orange-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    });
                    
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Add active class to clicked tab and corresponding content
                    this.classList.add('active', 'border-orange-500', 'text-orange-600');
                    this.classList.remove('border-transparent', 'text-gray-500');
                    
                    const targetContent = document.getElementById(targetTab + '-tab');
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
        });

        // Form validation
        function validateForm(form) {
            const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            return isValid;
        }

        // Add form submission handlers
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                    alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                    return false;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
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
        });

        // Email validation
        document.querySelectorAll('input[type="email"]').forEach(input => {
            input.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.classList.add('border-red-500');
                    this.setCustomValidity('กรุณากรอกอีเมลที่ถูกต้อง');
                } else {
                    this.classList.remove('border-red-500');
                    this.setCustomValidity('');
                }
            });
        });

        // Number input validation
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                const min = parseInt(this.getAttribute('min'));
                const max = parseInt(this.getAttribute('max'));
                const value = parseInt(this.value);
                
                if (value < min || value > max) {
                    this.classList.add('border-red-500');
                } else {
                    this.classList.remove('border-red-500');
                }
            });
        });

        // Auto-save functionality (optional)
        let autoSaveTimeout;
        function autoSave(form) {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                // Implementation for auto-save
                console.log('Auto-saving settings...');
            }, 5000);
        }

        // Add auto-save to form inputs
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', function() {
                const form = this.closest('form');
                if (form) {
                    autoSave(form);
                }
            });
        });

        // Confirmation for maintenance mode
        document.querySelector('input[name="maintenance_mode"]').addEventListener('change', function() {
            if (this.checked) {
                if (!confirm('คุณต้องการเปิดโหมดบำรุงรักษาหรือไม่? ผู้ใช้ทั่วไปจะไม่สามารถเข้าถึงเว็บไซต์ได้')) {
                    this.checked = false;
                }
            }
        });

        // Show/hide backup settings based on auto backup checkbox
        document.querySelector('input[name="auto_backup"]').addEventListener('change', function() {
            const backupSettings = document.querySelectorAll('select[name="backup_frequency"], input[name="backup_retention"], input[name="backup_path"]');
            backupSettings.forEach(setting => {
                setting.disabled = !this.checked;
                setting.closest('div').style.opacity = this.checked ? '1' : '0.5';
            });
        });

        // Real-time session timeout display
        document.querySelector('input[name="session_timeout"]').addEventListener('input', function() {
            const minutes = Math.round(this.value / 60);
            const display = this.parentNode.querySelector('p');
            if (display) {
                display.textContent = `ค่าปัจจุบัน: ${this.value} วินาที (${minutes} นาที)`;
            }
        });

        // Real-time lockout time display
        document.querySelector('input[name="lockout_time"]').addEventListener('input', function() {
            const minutes = Math.round(this.value / 60);
            const display = this.parentNode.querySelector('p');
            if (display) {
                display.textContent = `ค่าปัจจุบัน: ${minutes} นาที`;
            }
        });

        // Test email functionality
        document.querySelector('button[value="test_email"]').addEventListener('click', function(e) {
            const emailInput = document.querySelector('input[name="test_email"]');
            if (!emailInput.value) {
                e.preventDefault();
                alert('กรุณากรอกอีเมลสำหรับทดสอบ');
                emailInput.focus();
            }
        });

        // Clear cache confirmation
        document.querySelector('button[value="clear_cache"]').addEventListener('click', function(e) {
            if (!confirm('คุณต้องการล้างแคชระบบหรือไม่?')) {
                e.preventDefault();
            }
        });

        // Settings backup/restore functionality
        function exportSettings() {
            const settings = {};
            document.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.name && input.type !== 'submit' && input.type !== 'button') {
                    if (input.type === 'checkbox') {
                        settings[input.name] = input.checked;
                    } else {
                        settings[input.name] = input.value;
                    }
                }
            });
            
            const blob = new Blob([JSON.stringify(settings, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'settings-backup-' + new Date().toISOString().split('T')[0] + '.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Add export button functionality (could be added to UI)
        if (document.querySelector('.export-settings-btn')) {
            document.querySelector('.export-settings-btn').addEventListener('click', exportSettings);
        }

        console.log('⚙️ Settings system loaded successfully!');
    </script>
</body>
</html><?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once 'functions.php';

// Require admin role
requireAdmin('../login.php');

// กำหนดหน้าปัจจุบันสำหรับ sidebar
$current_page = 'settings';
$page_title = "ตั้งค่าระบบ";

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'general':
                $site_name = sanitizeInput($_POST['site_name'] ?? '');
                $site_description = sanitizeInput($_POST['site_description'] ?? '');
                $admin_email = sanitizeInput($_POST['admin_email'] ?? '');
                $timezone = sanitizeInput($_POST['timezone'] ?? 'Asia/Bangkok');
                $per_page = (int)($_POST['per_page'] ?? 10);
                
                if (empty($site_name) || empty($admin_email)) {
                    $error = "กรุณากรอกชื่อเว็บไซต์และอีเมลผู้ดูแล";
                } else {
                    $settings = [
                        'site_name' => $site_name,
                        'site_description' => $site_description,
                        'admin_email' => $admin_email,
                        'timezone' => $timezone,
                        'per_page' => $per_page
                    ];
                    
                    $success = true;
                    foreach ($settings as $key => $value) {
                        if (!setSystemConfig($conn, $key, $value)) {
                            $success = false;
                            break;
                        }
                    }
                    
                    if ($success) {
                        logActivity($conn, $_SESSION['user_id'], 'settings_updated', 'system_config', null);
                        $message = "บันทึกการตั้งค่าทั่วไปเรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถบันทึกการตั้งค่าได้";
                    }
                }
                break;
                
            case 'security':
                $session_timeout = (int)($_POST['session_timeout'] ?? 1800);
                $max_login_attempts = (int)($_POST['max_login_attempts'] ?? 5);
                $lockout_time = (int)($_POST['lockout_time'] ?? 300);
                $password_min_length = (int)($_POST['password_min_length'] ?? 6);
                $require_email_verification = isset($_POST['require_email_verification']) ? 1 : 0;
                $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
                
                $settings = [
                    'session_timeout' => $session_timeout,
                    'max_login_attempts' => $max_login_attempts,
                    'lockout_time' => $lockout_time,
                    'password_min_length' => $password_min_length,
                    'require_email_verification' => $require_email_verification,
                    'enable_2fa' => $enable_2fa
                ];
                
                $success = true;
                foreach ($settings as $key => $value) {
                    if (!setSystemConfig($conn, $key, $value)) {
                        $success = false;
                        break;
                    }
                }
                
                if ($success) {
                    logActivity($conn, $_SESSION['user_id'], 'security_settings_updated', 'system_config', null);
                    $message = "บันทึกการตั้งค่าความปลอดภัยเรียบร้อยแล้ว";
                } else {
                    $error = "ไม่สามารถบันทึกการตั้งค่าได้";
                }
                break;
                
            case 'email':
                $smtp_host = sanitizeInput($_POST['smtp_host'] ?? '');
                $smtp_port = (int)($_POST['smtp_port'] ?? 587);
                $smtp_username = sanitizeInput($_POST['smtp_username'] ?? '');
                $smtp_password = $_POST['smtp_password'] ?? '';
                $smtp_encryption = sanitizeInput($_POST['smtp_encryption'] ?? 'tls');
                $from_email = sanitizeInput($_POST['from_email'] ?? '');
                $from_name = sanitizeInput($_POST['from_name'] ?? '');
                
                $settings = [
                    'smtp_host' => $smtp_host,
                    'smtp_port' => $smtp_port,
                    'smtp_username' => $smtp_username,
                    'smtp_encryption' => $smtp_encryption,
                    'from_email' => $from_email,
                    'from_name' => $from_name
                ];
                
                // Only update password if provided
                if (!empty($smtp_password)) {
                    $settings['smtp_password'] = base64_encode($smtp_password); // Simple encoding
                }
                
                $success = true;
                foreach ($settings as $key => $value) {
                    if (!setSystemConfig($conn, $key, $value)) {
                        $success = false;
                        break;
                    }
                }
                
                if ($success) {
                    logActivity($conn, $_SESSION['user_id'], 'email_settings_updated', 'system_config', null);
                    $message = "บันทึกการตั้งค่าอีเมลเรียบร้อยแล้ว";
                } else {
                    $error = "ไม่สามารถบันทึกการตั้งค่าได้";
                }
                break;
                
            case 'backup':
                $auto_backup = isset($_POST['auto_backup']) ? 1 : 0;
                $backup_frequency = sanitizeInput($_POST['backup_frequency'] ?? 'daily');
                $backup_retention = (int)($_POST['backup_retention'] ?? 7);
                $backup_path = sanitizeInput($_POST['backup_path'] ?? '../backups/');
                
                $settings = [
                    'auto_backup' => $auto_backup,
                    'backup_frequency' => $backup_frequency,
                    'backup_retention' => $backup_retention,
                    'backup_path' => $backup_path
                ];
                
                $success = true;
                foreach ($settings as $key => $value) {
                    if (!setSystemConfig($conn, $key, $value)) {
                        $success = false;
                        break;
                    }
                }
                
                if ($success) {
                    logActivity($conn, $_SESSION['user_id'], 'backup_settings_updated', 'system_config', null);
                    $message = "บันทึกการตั้งค่าสำรองข้อมูลเรียบร้อยแล้ว";
                } else {
                    $error = "ไม่สามารถบันทึกการตั้งค่าได้";
                }
                break;
                
            case 'maintenance':
                $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
                $maintenance_message = sanitizeInput($_POST['maintenance_message'] ?? '');
                $allowed_ips = sanitizeInput($_POST['allowed_ips'] ?? '');
                
                $settings = [
                    'maintenance_mode' => $maintenance_mode,
                    'maintenance_message' => $maintenance_message,
                    'allowed_ips' => $allowed_ips
                ];
                
                $success = true;
                foreach ($settings as $key => $value) {
                    if (!setSystemConfig($conn, $key, $value)) {
                        $success = false;
                        break;
                    }
                }
                
                if ($success) {
                    logActivity($conn, $_SESSION['user_id'], 'maintenance_settings_updated', 'system_config', null);
                    $message = "บันทึกการตั้งค่าบำรุงรักษาเรียบร้อยแล้ว";
                } else {
                    $error = "ไม่สามารถบันทึกการตั้งค่าได้";
                }
                break;
                
            case 'test_email':
                $test_email = sanitizeInput($_POST['test_email'] ?? '');
                if (!empty($test_email)) {
                    // Simple email test
                    $subject = "ทดสอบการส่งอีเมล - " . (getSystemConfig($conn, 'site_name') ?: 'โรงพยาบาลทุ่งหัวช้าง');
                    $message_body = "นี่คือการทดสอบการส่งอีเมลจากระบบ<br>เวลา: " . date('Y-m-d H:i:s');
                    
                    if (sendEmail($test_email, $subject, $message_body)) {
                        $message = "ส่งอีเมลทดสอบเรียบร้อยแล้ว";
                    } else {
                        $error = "ไม่สามารถส่งอีเมลทดสอบได้";
                    }
                }
                break;
                
            case 'clear_cache':
                // Clear any cache files
                $cache_cleared = 0;
                $cache_dirs = ['../cache/', '../temp/'];
                
                foreach ($cache_dirs as $dir) {
                    if (is_dir($dir)) {
                        $files = glob($dir . '*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                unlink($file);
                                $cache_cleared++;
                            }
                        }
                    }
                }
                
                logActivity($conn, $_SESSION['user_id'], 'cache_cleared', 'system', null);
                $message = "ล้างแคชเรียบร้อยแล้ว (ลบ $cache_cleared ไฟล์)";
                break;
        }
    } catch (Exception $e) {
        logError($e->getMessage(), __FILE__, __LINE__);
        $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
    }
}

// Get current settings
$current_settings = getSystemConfig($conn);

// Default values
$defaults = [
    'site_name' => 'โรงพยาบาลทุ่งหัวช้าง',
    'site_description' => 'ระบบจัดการโรงพยาบาล',
    'admin_email' => 'admin@tunghuachang-hospital.com',
    'timezone' => 'Asia/Bangkok',
    'per_page' => 10,
    'session_timeout' => 1800,
    'max_login_attempts' => 5,
    'lockout_time' => 300,
    'password_min_length' => 6,
    'require_email_verification' => 0,
    'enable_2fa' => 0,
    'smtp_host' => '',
    'smtp_port' => 587,
    'smtp_username' => '',
    'smtp_encryption' => 'tls',
    'from_email' => '',
    'from_name' => '',
    'auto_backup' => 1,
    'backup_frequency' => 'daily',
    'backup_retention' => 7,
    'backup_path' => '../backups/',
    'maintenance_mode' => 0,
    'maintenance_message' => 'ระบบอยู่ระหว่างการบำรุงรักษา กรุณาลองใหม่ภายหลัง',
    'allowed_ips' => ''
];

// Merge with current settings
$settings = array_merge($defaults, $current_settings);

// Get system info
$system_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'mysql_version' => $conn->query("SELECT VERSION() as version")->fetch()['version'] ?? 'Unknown',
    'max_upload_size' => ini_get('upload_max_filesize'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'timezone' => date_default_timezone_get(),
    'disk_space' => disk_free_space('.') ? formatFileSize(disk_free_space('.')) : 'Unknown'
];
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
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-orange-600 to-red-700 text-white shadow-2xl sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                        <span class="text-white font-bold text-xl">⚙️</span>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold">ตั้งค่าระบบ</h1>
                        <p class="text-orange-200 text-sm">การกำหนดค่าและการจัดการระบบ</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm">สวัสดี, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-orange-200"><?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl transition duration-300 shadow-lg">
                        ออกจากระบบ
                    </a>