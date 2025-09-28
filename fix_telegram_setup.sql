-- แก้ไข Chat ID ให้เป็นค่าจริง
-- หลังจากที่ลูกค้าได้ Chat ID จริงแล้ว

USE hotel_management;

-- วิธีที่ 1: ใช้ default chat ID (แนะนำ)
UPDATE hotel_settings
SET setting_value = 'YOUR_REAL_CHAT_ID_HERE'
WHERE setting_key = 'default_housekeeping_chat_id';

-- วิธีที่ 2: ใส่ Chat ID ให้พนักงานแต่ละคน
UPDATE users
SET telegram_chat_id = 'HOUSEKEEPER1_CHAT_ID'
WHERE username = 'housekeeper1';

UPDATE users
SET telegram_chat_id = 'HOUSEKEEPER2_CHAT_ID'
WHERE username = 'housekeeper2';

-- ตรวจสอบการตั้งค่า
SELECT * FROM hotel_settings WHERE setting_key LIKE '%telegram%';
SELECT username, full_name, telegram_chat_id FROM users WHERE role = 'housekeeping';