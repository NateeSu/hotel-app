-- ========================================
-- Hotel Management System - Reset Database for Testing
-- เคลียร์ข้อมูล Transaction ทั้งหมดเพื่อเริ่มทดสอบใหม่
--
-- วันที่สร้าง: 2025-10-03
-- วัตถุประสงค์: เตรียมระบบสำหรับ Tester
-- ========================================

USE hotel_management;

-- ========================================
-- ตรวจสอบข้อมูลก่อนลบ
-- ========================================

SELECT '=== ข้อมูลก่อนเคลียร์ ===' as message;

SELECT
    'bookings' as table_name, COUNT(*) as records FROM bookings
UNION ALL
SELECT 'receipts', COUNT(*) FROM receipts
UNION ALL
SELECT 'room_transfers', COUNT(*) FROM room_transfers
UNION ALL
SELECT 'transfer_billing', COUNT(*) FROM transfer_billing
UNION ALL
SELECT 'housekeeping_jobs', COUNT(*) FROM housekeeping_jobs
UNION ALL
SELECT 'telegram_notifications', COUNT(*) FROM telegram_notifications
UNION ALL
SELECT 'activity_logs', COUNT(*) FROM activity_logs
UNION ALL
SELECT '--- Master Data ---', 0
UNION ALL
SELECT 'rooms', COUNT(*) FROM rooms
UNION ALL
SELECT 'users', COUNT(*) FROM users
UNION ALL
SELECT 'hotel_settings', COUNT(*) FROM hotel_settings
ORDER BY table_name;

-- ========================================
-- STEP 1: ปิด Foreign Key Checks ชั่วคราว
-- ========================================

SET FOREIGN_KEY_CHECKS = 0;

-- ========================================
-- STEP 2: ลบข้อมูล Transaction Tables
-- ========================================

-- ลบตามลำดับจากตารางที่ไม่มี dependency ก่อน

-- 1. Activity Logs (ไม่มี FK ไปที่ตารางอื่น)
TRUNCATE TABLE activity_logs;

-- 2. Telegram Notifications (ไม่มี FK ไปที่ตารางอื่น)
TRUNCATE TABLE telegram_notifications;

-- 3. Transfer Billing (FK ไป transfer_id)
TRUNCATE TABLE transfer_billing;

-- 4. Receipts (FK ไป booking_id)
TRUNCATE TABLE receipts;

-- 5. Room Transfers (FK ไป booking_id และ room_id)
TRUNCATE TABLE room_transfers;

-- 6. Housekeeping Jobs (FK ไป room_id)
TRUNCATE TABLE housekeeping_jobs;

-- 7. Bookings (FK ไป room_id และ user_id)
TRUNCATE TABLE bookings;

-- ========================================
-- STEP 3: รีเซ็ต Auto Increment
-- ========================================

ALTER TABLE bookings AUTO_INCREMENT = 1;
ALTER TABLE receipts AUTO_INCREMENT = 1;
ALTER TABLE room_transfers AUTO_INCREMENT = 1;
ALTER TABLE transfer_billing AUTO_INCREMENT = 1;
ALTER TABLE housekeeping_jobs AUTO_INCREMENT = 1;
ALTER TABLE telegram_notifications AUTO_INCREMENT = 1;
ALTER TABLE activity_logs AUTO_INCREMENT = 1;

-- ========================================
-- STEP 4: รีเซ็ตสถานะห้องทั้งหมด
-- ========================================

-- เปลี่ยนห้องทั้งหมดเป็น available
UPDATE rooms SET
    status = 'available',
    last_transfer_date = NULL,
    updated_at = NOW()
WHERE status IN ('occupied', 'cleaning', 'maintenance');

-- ========================================
-- STEP 5: เปิด Foreign Key Checks กลับ
-- ========================================

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- ตรวจสอบผลลัพธ์หลังเคลียร์
-- ========================================

SELECT '=== ข้อมูลหลังเคลียร์ ===' as message;

-- ตรวจสอบว่าตาราง Transaction ว่างหมด
SELECT
    'bookings' as table_name, COUNT(*) as records FROM bookings
UNION ALL
SELECT 'receipts', COUNT(*) FROM receipts
UNION ALL
SELECT 'room_transfers', COUNT(*) FROM room_transfers
UNION ALL
SELECT 'transfer_billing', COUNT(*) FROM transfer_billing
UNION ALL
SELECT 'housekeeping_jobs', COUNT(*) FROM housekeeping_jobs
UNION ALL
SELECT 'telegram_notifications', COUNT(*) FROM telegram_notifications
UNION ALL
SELECT 'activity_logs', COUNT(*) FROM activity_logs
ORDER BY table_name;

SELECT '=== Master Data ยังคงอยู่ ===' as message;

-- ตรวจสอบ Master Data ยังครบถ้วน
SELECT
    'users' as table_name, COUNT(*) as records FROM users
UNION ALL
SELECT 'rooms', COUNT(*) FROM rooms
UNION ALL
SELECT 'rates', COUNT(*) FROM rates
UNION ALL
SELECT 'room_rates', COUNT(*) FROM room_rates
UNION ALL
SELECT 'hotel_settings', COUNT(*) FROM hotel_settings
ORDER BY table_name;

SELECT '=== สถานะห้องพัก ===' as message;

-- ตรวจสอบสถานะห้องทั้งหมด
SELECT
    status,
    COUNT(*) as room_count
FROM rooms
GROUP BY status
ORDER BY status;

-- แสดงห้องทั้งหมด
SELECT
    room_number,
    room_type,
    status,
    floor
FROM rooms
ORDER BY room_number;

-- ========================================
-- สรุปผลการดำเนินการ
-- ========================================

SELECT '
========================================
✅ เคลียร์ข้อมูลสำเร็จ!
========================================

📊 ข้อมูลที่ถูกลบ:
- ❌ Bookings (การจองทั้งหมด)
- ❌ Receipts (ใบเสร็จทั้งหมด)
- ❌ Room Transfers (ประวัติการย้ายห้อง)
- ❌ Transfer Billing (การคิดค่าย้ายห้อง)
- ❌ Housekeeping Jobs (งานแม่บ้าน)
- ❌ Telegram Notifications (ประวัติแจ้งเตือน)
- ❌ Activity Logs (บันทึกการทำงาน)

✅ ข้อมูลที่ยังคงอยู่:
- ✅ Users (ผู้ใช้งานทั้งหมด)
- ✅ Rooms (ห้องพักทั้งหมด - สถานะ available)
- ✅ Rates (อัตราค่าห้อง)
- ✅ Room Rates (อัตราค่าห้องแต่ละห้อง)
- ✅ Hotel Settings (ตั้งค่าโรงแรม)

🎯 ระบบพร้อมสำหรับการทดสอบใหม่!

========================================
' as summary;

-- ========================================
-- วิธีการรัน Script นี้
-- ========================================

-- ใน Command Line:
-- mysql -u root -p hotel_management < reset_for_testing.sql

-- ใน XAMPP/phpMyAdmin:
-- 1. เลือก database hotel_management
-- 2. ไปที่แท็บ SQL
-- 3. Copy script นี้แล้ว Execute

-- ========================================
-- Rollback Plan (กรณีต้องการกู้คืน)
-- ========================================

-- หากต้องการกู้คืนข้อมูล:
-- mysql -u root -p hotel_management < backup_before_reset_20251003.sql
