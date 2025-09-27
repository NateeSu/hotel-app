# Hotel Management System

ระบบจัดการโรงแรมด้วย PHP 8.2 + MySQL + Bootstrap 5 สำหรับ XAMPP

## การติดตั้ง

### ข้อกำหนดระบบ
- XAMPP for Windows (PHP 8.2+, MySQL 8.0+, Apache)
- Web Browser (Chrome, Firefox, Edge)

### ขั้นตอนการติดตั้ง

#### 1. เตรียมสิ่งแวดล้อม
1. Copy โปรเจคไปยัง `C:\xampp\htdocs\hotel-app\`
2. เปิด XAMPP Control Panel
3. เริ่ม **Apache** และ **MySQL**
4. ตรวจสอบว่า Apache ทำงานที่ port 80 และ MySQL ที่ port 3306

#### 2. สร้างฐานข้อมูล (Database Setup)
##### วิธีที่ 1: ใช้ Migration Script (แนะนำ)
```bash
# เปิด Command Prompt ไปที่โฟลเดอร์โปรเจค
cd C:\xampp\htdocs\hotel-app\database

# รันการสร้างตาราง (ใช้เวอร์ชั่น fixed)
php migrate_fixed.php

# รันการเพิ่มข้อมูลตัวอย่าง
php seed.php
```

##### วิธีที่ 2: ใช้ phpMyAdmin
1. เปิดเบราว์เซอร์ไปที่ `http://localhost/phpmyadmin`
2. สร้างฐานข้อมูลใหม่ชื่อ `hotel_db` (charset: utf8mb4_0900_ai_ci)
3. Import ไฟล์ `database/schema.sql`
4. Import ไฟล์ `database/seed.sql`

##### วิธีที่ 3: ใช้เว็บเบราว์เซอร์
1. ไปที่ `http://localhost/hotel-app/database/migrate_fixed.php`
2. ไปที่ `http://localhost/hotel-app/database/seed.php`

#### 3. ทดสอบการติดตั้ง
1. เปิดเบราว์เซอร์ไปที่ `http://localhost/hotel-app`
2. ระบบจะ redirect ไปหน้า login
3. ใช้ account ทดสอบด้านล่าง

### Account ทดสอบ
- **Admin:** admin / admin123
- **Reception:** reception / rec123
- **Housekeeping:** housekeeping / hk123

### การแก้ไขปัญหา (Troubleshooting)

#### ปัญหาการเชื่อมต่อฐานข้อมูล
หาก migrate.php แสดง error การเชื่อมต่อ:
1. ตรวจสอบ MySQL ทำงานใน XAMPP Control Panel
2. ตรวจสอบ username/password ใน `config/db.php`
3. ตรวจสอบชื่อฐานข้อมูลเป็น `hotel_db`

#### ข้อมูลตัวอย่างไม่ปรากฏ
หาก seed.php ไม่มี error แต่ไม่เห็นข้อมูล:
1. ตรวจสอบใน phpMyAdmin ว่ามีตาราง 9 ตาราง
2. ตรวจสอบตาราง users มี 3 records
3. ตรวจสอบตาราง rooms มี 20 records

#### หน้าเว็บแสดง PHP Error
หากเห็น PHP errors บนหน้าเว็บ:
1. ตรวจสอบ PHP version >= 8.0 ใน XAMPP
2. Enable PHP extensions: pdo_mysql, mbstring
3. ตรวจสอบไฟล์ .htaccess ในโฟลเดอร์หลัก

## โครงสร้างโปรเจค

```
hotel-app/
├── config/                 # การตั้งค่าระบบ
├── includes/               # ไฟล์ PHP ที่ใช้ร่วมกัน
├── templates/              # Template หน้าเว็บ
├── assets/                 # Static files (CSS, JS, Images)
├── database/               # SQL scripts
├── admin/                  # หน้าสำหรับ Admin
├── rooms/                  # จัดการห้องพัก
├── customers/              # จัดการลูกค้า
├── bookings/               # ระบบจองห้อง
├── checkin/                # เช็คอิน
├── checkout/               # เช็คเอาท์
├── reports/                # รายงาน
└── auth/                   # Authentication
```

## ฟีเจอร์หลัก
- ✅ ระบบจัดการห้องพัก
- ✅ ระบบจองห้อง
- ✅ ระบบลูกค้า
- ✅ ระบบเช็คอิน/เอาท์
- ✅ ระบบรายงาน
- ✅ ระบบจัดการผู้ใช้

## การพัฒนา

### การรันการทดสอบ
- ไม่มี automated tests (Manual testing เท่านั้น)

### การ Deploy
1. Copy ไฟล์ทั้งหมดไปยัง production server
2. สร้างฐานข้อมูลและ import SQL
3. แก้ไขการตั้งค่าในไฟล์ config
4. ตั้งค่า permissions สำหรับโฟลเดอร์ uploads (ถ้ามี)

## การสนับสนุน
สำหรับคำถามหรือปัญหา กรุณาตรวจสอบเอกสาร TASKS.md และ ACCEPTANCE.md