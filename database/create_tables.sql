CREATE DATABASE IF NOT EXISTS thc_hospital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thc_hospital;

-- ตารางข่าวสาร
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    category ENUM('general', 'announcement', 'procurement', 'service', 'health_tips') DEFAULT 'general',
    featured_image VARCHAR(255),
    author_id INT NULL,
    publish_date DATETIME,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    views INT DEFAULT 0,
    tags TEXT,
    meta_description VARCHAR(160),
    is_featured BOOLEAN DEFAULT FALSE,
    is_urgent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_publish (status, publish_date),
    INDEX idx_category (category),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางผู้ใช้ระบบ
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'doctor', 'nurse', 'staff') NOT NULL,
    department_id INT NULL,
    phone VARCHAR(20),
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางการตั้งค่าระบบ
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางบันทึกการดำเนินการ
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, created_at),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางไฟล์แนบ
CREATE TABLE attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    related_table VARCHAR(50),
    related_id INT,
    uploaded_by INT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_related (related_table, related_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- แทรกข้อมูลเริ่มต้น

-- แผนกต่างๆ
INSERT INTO departments (code, name, name_en, description, location) VALUES
('GEN', 'แพทย์ทั่วไป', 'General Medicine', 'บริการตรวจรักษาโรคทั่วไป', 'ชั้น 1 อาคารผู้ป่วยนอก'),
('PED', 'กุมารเวชกรรม', 'Pediatrics', 'บริการตรวจรักษาเด็กและวัยรุ่น', 'ชั้น 1 อาคารผู้ป่วยนอก'),
('OBS', 'สูติ-นรีเวชกรรม', 'Obstetrics & Gynecology', 'การดูแลสุขภาพสตรี การฝากครรภ์ การคลอด', 'ชั้น 2 อาคารผู้ป่วยใน'),
('SUR', 'ศัลยกรรม', 'Surgery', 'การผ่าตัดทั่วไป การรักษาบาดแผล', 'ชั้น 3 อาคารผู้ป่วยใน'),
('ORT', 'ออร์โธปิดิกส์', 'Orthopedics', 'การรักษากระดูกและข้อ การฟื้นฟูสมรรถภาพ', 'ชั้น 2 อาคารผู้ป่วยนอก'),
('DEN', 'ทันตกรรม', 'Dentistry', 'การรักษาฟัน การขูดหินปูน การถอนฟัน', 'ชั้น 1 อาคารผู้ป่วยนอก'),
('EMR', 'แผนกฉุกเฉิน', 'Emergency', 'บริการฉุกเฉินตลอด 24 ชั่วโมง', 'ชั้น 1 อาคารฉุกเฉิน'),
('LAB', 'ห้องปฏิบัติการ', 'Laboratory', 'ตรวจวิเคราะห์ทางห้องปฏิบัติการ', 'ชั้น 1 อาคารบริการ');




-- ข้อมูลตัวอย่างแพทย์
INSERT INTO doctors (employee_id, title, first_name, last_name, specialization, department_id, phone, email) VALUES
('D001', 'นพ.', 'สมชาย', 'ใจดี', 'อายุรศาสตร์ทั่วไป', 1, '053-580-101', 'somchai@thchospital.go.th'),
('D002', 'นพ.', 'วิชัย', 'รักษาดี', 'กุมารเวชกรรม', 2, '053-580-102', 'wichai@thchospital.go.th'),
('D003', 'พญ.', 'สุนีย์', 'เอาใจใส่', 'สูติ-นรีเวชกรรม', 3, '053-580-103', 'sunee@thchospital.go.th'),
('D004', 'นพ.', 'ประชา', 'ช่วยเหลือ', 'ศัลยกรรมทั่วไป', 4, '053-580-104', 'pracha@thchospital.go.th'),
('D005', 'นพ.', 'อานนท์', 'บำบัดดี', 'ออร์โธปิดิกส์', 5, '053-580-105', 'anon@thchospital.go.th'),
('D006', 'ทพ.', 'มาลัย', 'ยิ้มสวย', 'ทันตกรรมทั่วไป', 6, '053-580-106', 'malay@thchospital.go.th'),
('D007', 'นพ.', 'ฉุกเฉิน', 'พร้อมช่วย', 'เวชศาสตร์ฉุกเฉิน', 7, '053-580-107', 'emergency@thchospital.go.th');


-- แผนกต่างๆ
INSERT INTO departments (code, name, name_en, description, location) VALUES
('GEN', 'แพทย์ทั่วไป', 'General Medicine', 'บริการตรวจรักษาโรคทั่วไป', 'ชั้น 1 อาคารผู้ป่วยนอก'),
('PED', 'กุมารเวชกรรม', 'Pediatrics', 'บริการตรวจรักษาเด็กและวัยรุ่น', 'ชั้น 1 อาคารผู้ป่วยนอก'),
('OBS', 'สูติ-นรีเวชกรรม', 'Obstetrics & Gynecology', 'การดูแลสุขภาพสตรี การฝากครรภ์ การคลอด', 'ชั้น 2 อาคารผู้ป่วยใน'),
('SUR', 'ศัลยกรรม', 'Surgery', 'การผ่าตัดทั่วไป การรักษาบาดแผล', 'ชั้น 3 อาคารผู้ป่วยใน'),
('ORT', 'ออร์โธปิดิกส์', 'Orthopedics', 'การรักษากระดูกและข้อ การฟื้นฟูสมรรถภาพ', 'ชั้น 2 อาคารผู้ป่วยนอก'),
('DEN', 'ทันตกรรม', 'Dentistry', 'การรักษาฟัน การขูดหินปูน การถอนฟัน', 'ชั้น 1 อาคารผู้ป่วยนอก'),
('EMR', 'แผนกฉุกเฉิน', 'Emergency', 'บริการฉุกเฉินตลอด 24 ชั่วโมง', 'ชั้น 1 อาคารฉุกเฉิน'),
('LAB', 'ห้องปฏิบัติการ', 'Laboratory', 'ตรวจวิเคราะห์ทางห้องปฏิบัติการ', 'ชั้น 1 อาคารบริการ');


-- ผู้ใช้ระบบเริ่มต้น (password: admin123, staff123, doctor123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role, department_id) VALUES
('admin', 'admin@thchospital.go.th', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแล', 'ระบบ', 'admin', NULL),




-- ข้อมูลตัวอย่างข่าวสาร
INSERT INTO news (title, slug, content, excerpt, category, publish_date, status, is_featured) VALUES
('ประกาศจังหวัดลำพูน เรื่องประกาศผู้ชนะการเสนอราคาจัดซื้อระบบบริหารจัดการผู้ป่วยใน (IPD Paperless)', 
 'ipd-paperless-winner-announcement', 
 'โรงพยาบาลทุ่งหัวช้าง ขอประกาศผู้ชนะการเสนอราคาจัดซื้อระบบบริหารจัดการผู้ป่วยใน (IPD Paperless) จำนวน 1 งาน ด้วยวิธีประกวดราคาอิเล็กทรอนิกส์ (e-bidding) ตามรายละเอียดดังนี้...', 
 'ประกาศผู้ชนะการเสนอราคาจัดซื้อระบบ IPD Paperless ด้วยวิธี e-bidding', 
 'procurement', 
 '2025-06-15 10:00:00', 
 'published', 
 TRUE),

('แผนจัดซื้อจัดจ้าง ปี งบประมาณ 2568', 
 'procurement-plan-2568', 
 'โรงพยาบาลทุ่งหัวช้าง ขอเผยแพร่แผนจัดซื้อจัดจ้าง ประจำปีงบประมาณ 2568 เพื่อให้ประชาชนและผู้ที่สนใจสามารถตรวจสอบได้ ตามหลักธรรมาภิบาลและความโปร่งใส...', 
 'เผยแพร่แผนจัดซื้อจัดจ้าง ประจำปีงบประมาณ 2568', 
 'procurement', 
 '2025-06-10 14:30:00', 
 'published', 
 FALSE),

('รายงานทางการเงินประจำปีงบประมาณ 2567', 
 'financial-report-2567', 
 'โรงพยาบาลทุ่งหัวช้าง ขอรายงานผลการดำเนินงานทางการเงิน ประจำปีงบประมาณ 2567 ข้อมูล ณ วันที่ 30 กันยายน พ.ศ. 2567...', 
 'รายงานผลการดำเนินงานทางการเงิน ประจำปีงบประมาณ 2567', 
 'general', 
 '2024-09-30 16:00:00', 
 'published', 
 FALSE),

('การให้บริการฉีดวัคซีนไข้หวัดใหญ่ ประจำปี 2568', 
 'flu-vaccine-2568', 
 'โรงพยาบาลทุ่งหัวช้าง เปิดให้บริการฉีดวัคซีนป้องกันไข้หวัดใหญ่ ประจำปี 2568 สำหรับผู้ที่มีอายุ 6 เดือนขึ้นไป โดยเฉพาะกลุ่มเสี่ยง เช่น ผู้สูงอายุ เด็กเล็ก ผู้ป่วยโรคเรื้อรัง...', 
 'เปิดให้บริการฉีดวัคซีนป้องกันไข้หวัดใหญ่ ประจำปี 2568', 
 'service', 
 '2025-06-01 09:00:00', 
 'published', 
 TRUE);

-- สร้าง Views สำหรับการค้นหาและรายงาน

-- View สำหรับข้อมูลการนัดหมายแบบละเอียด
CREATE VIEW appointment_details AS
SELECT 
    a.id,
    a.appointment_number,
    a.appointment_date,
    a.appointment_time,
    a.patient_name,
    a.patient_phone,
    a.symptoms,
    a.status,
    a.priority,
    a.queue_number,
    d.name as department_name,
    doc.title as doctor_title,
    doc.first_name as doctor_first_name,
    doc.last_name as doctor_last_name,
    CONCAT(doc.title, doc.first_name, ' ', doc.last_name) as doctor_full_name,
    a.created_at,
    a.confirmed_at,
    a.checked_in_at,
    a.completed_at
FROM appointments a
LEFT JOIN departments d ON a.department_id = d.id
LEFT JOIN doctors doc ON a.doctor_id = doc.id;

-- View สำหรับสถิติการนัดหมายรายวัน
CREATE VIEW daily_appointment_stats AS
SELECT 
    appointment_date,
    department_id,
    d.name as department_name,
    COUNT(*) as total_appointments,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
    SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show_count
FROM appointments a
LEFT JOIN departments d ON a.department_id = d.id
GROUP BY appointment_date, department_id, d.name;

-- View สำหรับข้อมูลผู้ป่วยแบบละเอียด
CREATE VIEW patient_summary AS
SELECT 
    p.id,
    p.patient_id,
    CONCAT(p.title, p.first_name, ' ', p.last_name) as full_name,
    p.birth_date,
    YEAR(CURDATE()) - YEAR(p.birth_date) as age,
    p.gender,
    p.phone,
    p.email,
    p.insurance_type,
    COUNT(v.id) as total_visits,
    MAX(v.visit_date) as last_visit_date,
    COUNT(a.id) as total_appointments,
    MAX(a.appointment_date) as last_appointment_date
FROM patients p
LEFT JOIN visits v ON p.id = v.patient_id
LEFT JOIN appointments a ON p.id = a.patient_id
WHERE p.is_active = TRUE
GROUP BY p.id;

-- สร้าง Indexes เพิ่มเติมเพื่อเพิ่มประสิทธิภาพ
CREATE INDEX idx_appointments_patient_phone ON appointments(patient_phone);
CREATE INDEX idx_appointments_patient_name ON appointments(patient_name);
CREATE INDEX idx_patients_phone ON patients(phone);
CREATE INDEX idx_patients_id_card ON patients(id_card);
CREATE INDEX idx_news_category_status ON news(category, status);
CREATE INDEX idx_news_publish_date ON news(publish_date DESC);
CREATE INDEX idx_visits_patient_date ON visits(patient_id, visit_date);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at DESC);

-- สร้าง Stored Procedures สำหรับการทำงานทั่วไป

DELIMITER $

-- Procedure สำหรับการสร้างหมายเลขนัดหมาย
CREATE PROCEDURE GenerateAppointmentNumber(
    IN dept_code VARCHAR(10),
    OUT appointment_num VARCHAR(20)
)
BEGIN
    DECLARE num_count INT;
    DECLARE date_str VARCHAR(8);
    
    SET date_str = DATE_FORMAT(CURDATE(), '%Y%m%d');
    
    SELECT COUNT(*) + 1 INTO num_count
    FROM appointments 
    WHERE appointment_date = CURDATE() 
    AND appointment_number LIKE CONCAT(dept_code, date_str, '%');
    
    SET appointment_num = CONCAT(dept_code, date_str, LPAD(num_count, 3, '0'));
END$

-- Procedure สำหรับการสร้างหมายเลขผู้ป่วย
CREATE PROCEDURE GeneratePatientId(
    OUT patient_num VARCHAR(20)
)
BEGIN
    DECLARE num_count INT;
    DECLARE year_str VARCHAR(4);
    
    SET year_str = YEAR(CURDATE());
    
    SELECT COUNT(*) + 1 INTO num_count
    FROM patients 
    WHERE YEAR(created_at) = YEAR(CURDATE());
    
    SET patient_num = CONCAT('P', year_str, LPAD(num_count, 6, '0'));
END$

-- Procedure สำหรับการอัพเดทสถานะนัดหมาย
CREATE PROCEDURE UpdateAppointmentStatus(
    IN apt_id INT,
    IN new_status VARCHAR(20),
    IN user_id INT
)
BEGIN
    DECLARE old_status VARCHAR(20);
    
    SELECT status INTO old_status FROM appointments WHERE id = apt_id;
    
    UPDATE appointments 
    SET status = new_status,
        confirmed_by = CASE WHEN new_status = 'confirmed' THEN user_id ELSE confirmed_by END,
        confirmed_at = CASE WHEN new_status = 'confirmed' THEN NOW() ELSE confirmed_at END,
        checked_in_at = CASE WHEN new_status = 'in_progress' THEN NOW() ELSE checked_in_at END,
        completed_at = CASE WHEN new_status = 'completed' THEN NOW() ELSE completed_at END,
        updated_at = NOW()
    WHERE id = apt_id;
    
    -- Log the activity
    INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values, created_at)
    VALUES (user_id, 'update_status', 'appointments', apt_id, 
            JSON_OBJECT('status', old_status), 
            JSON_OBJECT('status', new_status), 
            NOW());
END$

DELIMITER ;

-- สร้าง Triggers สำหรับ Audit Trail

-- Trigger สำหรับ appointments
CREATE TRIGGER appointments_audit 
AFTER UPDATE ON appointments
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO activity_logs (action, table_name, record_id, old_values, new_values, created_at)
        VALUES ('status_change', 'appointments', NEW.id,
                JSON_OBJECT('status', OLD.status),
                JSON_OBJECT('status', NEW.status),
                NOW());
    END IF;
END;

-- สิทธิ์การใช้งาน (ควรปรับแต่งตามความต้องการ)
-- GRANT SELECT, INSERT, UPDATE ON thc_hospital.* TO 'hospital_user'@'localhost';
-- GRANT ALL PRIVILEGES ON thc_hospital.* TO 'hospital_admin'@'localhost';

-- สำเร็จ! ฐานข้อมูลพร้อมใช้งาน