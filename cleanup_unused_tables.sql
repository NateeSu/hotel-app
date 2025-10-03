-- ========================================
-- Hotel Management System - Database Cleanup Script
-- ลบตารางที่ไม่ได้ใช้งานออกจากระบบ
--
-- วันที่สร้าง: 2025-10-03
-- สถานะ: สำหรับทำความสะอาด Database หลัง Debug
-- ========================================

USE hotel_management;

-- สำรองข้อมูลก่อนลบ (optional - uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS hotel_management_backup;
-- mysqldump hotel_management > backup_before_cleanup_$(date +%Y%m%d).sql

-- ========================================
-- ตรวจสอบตารางที่มีอยู่
-- ========================================
SHOW TABLES;

-- ========================================
-- STEP 1: ลบ VIEW ที่ไม่ใช้งาน
-- ========================================

-- ⚠️ สำคัญ: ตารางเหล่านี้เป็น VIEW ไม่ใช่ TABLE
-- ต้องใช้ DROP VIEW แทน DROP TABLE

DROP VIEW IF EXISTS `daily_transfer_stats`;
-- เหตุผล: ไม่มีโค้ดใช้งาน, เป็น view สถิติที่สร้างระหว่าง debug
-- ประเภท: VIEW

DROP VIEW IF EXISTS `transfer_summary`;
-- เหตุผล: มีการใช้งานที่ transfer_engine.php:503 แต่เป็นแค่ SELECT เท่านั้น
-- ไม่มี INSERT/UPDATE และไม่มีหน้าแสดงข้อมูล
-- ประเภท: VIEW

DROP VIEW IF EXISTS `housekeeping_performance`;
-- เหตุผล: ไม่มีโค้ดใช้งาน, เป็น view performance tracking ที่ไม่ได้ implement
-- ประเภท: VIEW


-- ========================================
-- STEP 2: ตรวจสอบตารางที่เหลือ
-- ========================================

-- รายการตารางที่ควรเหลืออยู่:
-- ✅ users - ใช้งานจริง
-- ✅ rooms - ใช้งานจริง
-- ✅ bookings - ใช้งานจริง
-- ✅ rates - ใช้งานจริง (แม้ว่าจะมี room_rates เพิ่มเติม)
-- ✅ room_rates - ใช้งานจริง (ใน rates_simple.php, transfer_engine.php)
-- ✅ receipts - ใช้งานจริง
-- ✅ hotel_settings - ใช้งานจริง
-- ✅ housekeeping_jobs - ใช้งานจริง
-- ✅ room_transfers - ใช้งานจริง (transfer_engine.php, transfer_history.php)
-- ✅ transfer_billing - ใช้งานจริง (transfer_engine.php)
-- ✅ telegram_notifications - ใช้งานจริง (telegram_service.php)
-- ✅ activity_logs - ใช้งานจริง (helpers.php)

SHOW TABLES;

-- ========================================
-- STEP 3: ตรวจสอบจำนวนข้อมูลในแต่ละตาราง
-- ========================================

SELECT 'users' as table_name, COUNT(*) as row_count FROM users
UNION ALL
SELECT 'rooms', COUNT(*) FROM rooms
UNION ALL
SELECT 'bookings', COUNT(*) FROM bookings
UNION ALL
SELECT 'rates', COUNT(*) FROM rates
UNION ALL
SELECT 'room_rates', COUNT(*) FROM room_rates
UNION ALL
SELECT 'receipts', COUNT(*) FROM receipts
UNION ALL
SELECT 'hotel_settings', COUNT(*) FROM hotel_settings
UNION ALL
SELECT 'housekeeping_jobs', COUNT(*) FROM housekeeping_jobs
UNION ALL
SELECT 'room_transfers', COUNT(*) FROM room_transfers
UNION ALL
SELECT 'transfer_billing', COUNT(*) FROM transfer_billing
UNION ALL
SELECT 'telegram_notifications', COUNT(*) FROM telegram_notifications
UNION ALL
SELECT 'activity_logs', COUNT(*) FROM activity_logs
ORDER BY table_name;

-- ========================================
-- สรุปการทำความสะอาด
-- ========================================

-- VIEW ที่ถูกลบ (3 views):
-- 1. daily_transfer_stats - stats view ไม่ใช้งาน
-- 2. transfer_summary - summary view ไม่ใช้งาน (มี SELECT ใน code แต่ไม่ได้ใช้จริง)
-- 3. housekeeping_performance - performance tracking view ไม่ใช้งาน

-- ตารางที่เหลือ (12 ตาราง):
-- ✅ users - ผู้ใช้งานระบบ
-- ✅ rooms - ห้องพัก
-- ✅ bookings - การจอง (46 records)
-- ✅ rates - อัตราค่าห้อง
-- ✅ room_rates - อัตราค่าห้องแต่ละห้อง
-- ✅ receipts - ใบเสร็จ (27 records)
-- ✅ hotel_settings - ตั้งค่าโรงแรม
-- ✅ housekeeping_jobs - งานแม่บ้าน (48 records)
-- ✅ room_transfers - การย้ายห้อง (4 records)
-- ✅ transfer_billing - การคิดค่าใช้จ่ายการย้ายห้อง
-- ✅ telegram_notifications - การแจ้งเตือน Telegram
-- ✅ activity_logs - บันทึกการทำงาน (audit trail)

-- ========================================
-- คำเตือน
-- ========================================

-- ⚠️ ก่อนรัน script นี้:
-- 1. สำรองฐานข้อมูลก่อน: mysqldump hotel_management > backup.sql
-- 2. ตรวจสอบว่าไม่มีตารางที่จำเป็นถูกลบ
-- 3. ทดสอบระบบหลังจากลบตาราง

-- ========================================
-- วิธีการรัน
-- ========================================

-- ใน Command Line:
-- mysql -u root -p hotel_management < cleanup_unused_tables.sql

-- ใน XAMPP/phpMyAdmin:
-- 1. เลือก database hotel_management
-- 2. ไปที่แท็บ SQL
-- 3. Copy script นี้แล้ว Execute

-- ========================================
-- Rollback Plan (กรณีต้องการกู้คืน)
-- ========================================

-- หากต้องการกู้คืนข้อมูล:
-- mysql -u root -p hotel_management < backup_before_cleanup_YYYYMMDD.sql
