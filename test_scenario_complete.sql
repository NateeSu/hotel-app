-- สร้างสถานการณ์ทดสอบ T008 แบบครบวงจร
USE hotel_management;

-- 1. ตั้งค่า Telegram Bot (ใส่ค่าจริง)
UPDATE hotel_settings SET setting_value = 'YOUR_REAL_BOT_TOKEN' WHERE setting_key = 'telegram_bot_token';
UPDATE hotel_settings SET setting_value = 'YOUR_REAL_CHAT_ID' WHERE setting_key = 'default_housekeeping_chat_id';

-- 2. สร้างห้องทดสอบ
INSERT IGNORE INTO rooms (room_number, room_type, status, floor, max_occupancy) VALUES
('TEST01', 'short', 'available', 1, 2);

-- 3. สร้างการจองที่กำลังเข้าพัก
INSERT INTO bookings (room_id, guest_name, guest_phone, plan_type, status, checkin_at, base_amount, payment_status, created_by)
SELECT
    r.id,
    'คุณทดสอบ ระบบ',
    '081-234-5678',
    'short',
    'active',
    NOW() - INTERVAL 1 HOUR,
    300.00,
    'paid',
    1
FROM rooms r WHERE r.room_number = 'TEST01';

-- 4. อัปเดตสถานะห้องเป็น occupied
UPDATE rooms SET status = 'occupied' WHERE room_number = 'TEST01';

-- 5. ตรวจสอบข้อมูล
SELECT 'Test data ready!' as status;
SELECT r.room_number, r.status, b.guest_name, b.status as booking_status
FROM rooms r
LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'active'
WHERE r.room_number = 'TEST01';