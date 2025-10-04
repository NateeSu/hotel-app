# 🏨 Hotel Management System

ระบบจัดการโรงแรมแบบครบวงจร พัฒนาด้วย **PHP 8.2 + MySQL 8.0 + Bootstrap 5** สำหรับ XAMPP

![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)
![MySQL Version](https://img.shields.io/badge/MySQL-8.0%2B-orange)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📋 สารบัญ

- [ฟีเจอร์หลัก](#-ฟีเจอร์หลัก)
- [ข้อกำหนดระบบ](#-ข้อกำหนดระบบ)
- [การติดตั้ง](#-การติดตั้ง)
- [โครงสร้างโปรเจค](#-โครงสร้างโปรเจค)
- [Database Schema](#-database-schema)
- [User Accounts](#-user-accounts)
- [API & Routes](#-api--routes)
- [Troubleshooting](#-troubleshooting)
- [การ Deploy](#-การ-deploy)

---

## ✨ ฟีเจอร์หลัก

### 🏠 ระบบจัดการห้องพัก
- ✅ Room Board แบบ Real-time (แสดงสถานะห้องทั้งหมด)
- ✅ สถานะห้อง: Available, Occupied, Cleaning, Maintenance
- ✅ ประเภทห้อง: Single Bed, Double Bed
- ✅ จัดการห้อง 21 ห้อง (101-105, 201-216)

### 📅 ระบบจองและเช็คอิน/เอาท์
- ✅ Check-in พร้อมคำนวณราคาอัตโนมัติ
  - Short-stay (รายชั่วโมง - 3 ชม. ฿200)
  - Overnight (รายคืน - ฿350/คืน)
- ✅ Check-out พร้อมออกใบเสร็จ PDF
- ✅ Transfer ห้อง (ย้ายห้องพร้อมคำนวณส่วนต่าง)
- ✅ ระบบคำนวณค่าใช้จ่ายแบบ Dynamic

### 🧹 ระบบแม่บ้าน (Housekeeping)
- ✅ สร้างและมอบหมายงานทำความสะอาด
- ✅ ติดตามสถานะงาน (Pending, In Progress, Completed)
- ✅ Priority Management (Low, Normal, High, Urgent)
- ✅ รายงานประสิทธิภาพพนักงาน

### 💵 ระบบใบเสร็จ (Receipts)
- ✅ สร้างใบเสร็จอัตโนมัติเมื่อ Check-out
- ✅ ดาวน์โหลด PDF พร้อมข้อมูลโรงแรม
- ✅ ประวัติใบเสร็จทั้งหมด
- ✅ รองรับวิธีชำระเงิน: Cash, Card, Transfer

### 📊 ระบบรายงาน
- ✅ **รายงานการจอง**: แนวโน้มการจองตามวัน/ชั่วโมง
- ✅ **รายงานการเข้าพัก**: Occupancy Rate, Room Performance
- ✅ **รายงานยอดขาย**: Daily Sales, Revenue Analysis
- ✅ Export เป็น CSV/PDF

### 📱 การแจ้งเตือน Telegram
- ✅ แจ้งเตือนเมื่อมี Check-in/Check-out
- ✅ แจ้งเตือนเมื่อมีการ Transfer ห้อง
- ✅ แจ้งเตือนงานแม่บ้านใหม่

### 👥 ระบบจัดการผู้ใช้
- ✅ 3 ระดับสิทธิ์: Admin, Reception, Housekeeping
- ✅ Activity Logs (Audit Trail)
- ✅ User Management

---

## 💻 ข้อกำหนดระบบ

### Software Requirements
- **XAMPP** for Windows (หรือ Linux/Mac)
  - PHP 8.2 หรือสูงกว่า
  - MySQL 8.0 หรือสูงกว่า
  - Apache Web Server
- **Web Browser** (Chrome, Firefox, Edge แนะนำ Chrome)

### PHP Extensions (Required)
```
✅ pdo_mysql
✅ mbstring
✅ json
✅ curl
✅ gd
✅ xml
```

---

## 🚀 การติดตั้ง

### 1️⃣ เตรียมสิ่งแวดล้อม

```bash
# 1. คัดลอกโปรเจคไปยัง htdocs
# Windows
xcopy hotel-app D:\xampp\htdocs\hotel-app\ /E /I

# หรือ Git Clone
git clone https://github.com/your-repo/hotel-app.git D:\xampp\htdocs\hotel-app

# 2. เปิด XAMPP Control Panel
# 3. Start Apache และ MySQL
```

### 2️⃣ สร้างฐานข้อมูล

#### วิธีที่ 1: ใช้ Command Line (แนะนำ)

```bash
# เข้าไปที่โฟลเดอร์โปรเจค
cd D:\xampp\htdocs\hotel-app

# สร้างฐานข้อมูลและตาราง
D:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import Schema
D:\xampp\mysql\bin\mysql.exe -u root hotel_management < database\schema.sql

# Import ข้อมูลเริ่มต้น (Optional - สำหรับทดสอบ)
D:\xampp\mysql\bin\mysql.exe -u root hotel_management < database\seed.sql
```

#### วิธีที่ 2: ใช้ phpMyAdmin

1. เปิด `http://localhost/phpmyadmin`
2. สร้างฐานข้อมูลใหม่: `hotel_management`
   - Collation: `utf8mb4_unicode_ci`
3. Import ไฟล์: `database/schema.sql`
4. (Optional) Import: `database/seed.sql`

### 3️⃣ ตั้งค่า Configuration

```bash
# Copy .env.example เป็น .env (ถ้ามี)
copy .env.example .env

# แก้ไขไฟล์ config/db.php ถ้าต้องการเปลี่ยนค่า default
# Default: DB_NAME=hotel_management, DB_USER=root, DB_PASS=''
```

### 4️⃣ ทดสอบการติดตั้ง

1. เปิดเบราว์เซอร์ไปที่ `http://localhost/hotel-app`
2. ระบบจะ redirect ไปหน้า Login
3. Login ด้วย Account ด้านล่าง

---

## 🔐 User Accounts

### Default Admin Account
```
Username: admin
Password: admin123
Role: Administrator (เข้าถึงได้ทุกอย่าง)
```

### Reception Accounts
```
Username: reception
Password: rec123
Role: Reception (จองห้อง, Check-in/out, ออกใบเสร็จ)

Username: reception1
Password: rec123
Role: Reception
```

### Housekeeping Accounts
```
Username: housekeeping
Password: hk123
Role: Housekeeping (ดูและอัปเดตงานทำความสะอาด)

Username: housekeeper1
Password: hk123
Role: Housekeeping

Username: housekeeper2
Password: hk123
Role: Housekeeping
```

---

## 📁 โครงสร้างโปรเจค

```
hotel-app/
├── 📁 config/              # Database Configuration
│   └── db.php              # PDO Connection, Timezone Setup
│
├── 📁 includes/            # Core PHP Files
│   ├── auth.php            # Authentication & Authorization
│   ├── csrf.php            # CSRF Protection
│   ├── helpers.php         # Helper Functions
│   └── router.php          # URL Routing System
│
├── 📁 templates/           # Layout Templates
│   ├── layout/
│   │   ├── header.php      # Header + Navigation
│   │   └── footer.php      # Footer + Scripts
│   └── partials/
│       ├── navbar.php      # Top Navigation Bar
│       └── flash.php       # Flash Messages
│
├── 📁 assets/              # Static Resources
│   ├── css/                # Custom CSS
│   ├── js/                 # Custom JavaScript
│   └── images/             # Images & Icons
│
├── 📁 lib/                 # Business Logic Libraries
│   ├── receipt_generator.php   # PDF Receipt Generator
│   ├── telegram_service.php    # Telegram Bot Integration
│   ├── transfer_engine.php     # Room Transfer Logic
│   └── reports_engine.php      # Reports & Analytics
│
├── 📁 admin/               # Admin Pages
│   └── rooms.php           # Room Management
│
├── 📁 rooms/               # Room Operations
│   ├── board.php           # Room Status Board
│   ├── checkin.php         # Check-in Form
│   ├── checkout.php        # Check-out Process
│   ├── transfer.php        # Room Transfer
│   └── transfer_history.php # Transfer History
│
├── 📁 housekeeping/        # Housekeeping Module
│   ├── jobs.php            # Job List
│   ├── job.php             # Job Details
│   └── reports.php         # Housekeeping Reports
│
├── 📁 receipts/            # Receipt Management
│   ├── history.php         # Receipt History
│   └── view.php            # View/Download Receipt
│
├── 📁 reports/             # Business Reports
│   ├── bookings.php        # Booking Analytics
│   ├── occupancy.php       # Occupancy Report
│   └── sales.php           # Sales Report
│
├── 📁 system/              # System Settings
│   ├── settings.php        # Hotel Settings
│   └── rates_simple.php    # Rate Management
│
├── 📁 auth/                # Authentication
│   ├── login.php           # Login Page
│   └── logout.php          # Logout Handler
│
├── 📁 api/                 # API Endpoints
│   ├── calculate_transfer.php  # Transfer Cost Calculator
│   ├── check_notifications.php # Notification Checker
│   ├── get_room_booking.php    # Get Room Details
│   └── room_status.php         # Update Room Status
│
├── 📁 database/            # Database Scripts
│   ├── schema.sql          # Database Schema
│   └── seed.sql            # Sample Data
│
├── 📁 migrations/          # Database Migrations
│   └── *.sql               # Migration Scripts
│
├── 📄 index.php            # Application Entry Point
├── 📄 dashboard.php        # Main Dashboard
├── 📄 README.md            # Documentation (This file)
├── 📄 .htaccess            # Apache Configuration
└── 📄 .gitignore           # Git Ignore Rules
```

---

## 🗄️ Database Schema

### ตารางทั้งหมด (12 ตาราง)

| # | ตาราง | จำนวนข้อมูล | สถานะ | หมายเหตุ |
|---|--------|-------------|--------|-----------|
| 1 | users | 6 | ✅ ใช้งาน | ผู้ใช้งานระบบ |
| 2 | rooms | 21 | ✅ ใช้งาน | ห้องพัก |
| 3 | bookings | 0 | ✅ ใช้งาน | การจอง (รีเซ็ตแล้ว) |
| 4 | receipts | 0 | ✅ ใช้งาน | ใบเสร็จ (รีเซ็ตแล้ว) |
| 5 | rates | 3 | ⚠️ Legacy | ตารางเดิม (ไม่ค่อยใช้) |
| 6 | room_rates | 2 | ✅ ใช้งาน | อัตราค่าห้อง (ตารางหลัก) |
| 7 | room_transfers | 0 | ✅ ใช้งาน | การย้ายห้อง (รีเซ็ตแล้ว) |
| 8 | transfer_billing | 0 | ✅ ใช้งาน | การคิดค่าย้ายห้อง (รีเซ็ตแล้ว) |
| 9 | housekeeping_jobs | 0 | ✅ ใช้งาน | งานแม่บ้าน (รีเซ็ตแล้ว) |
| 10 | hotel_settings | 13 | ✅ ใช้งาน | ตั้งค่าโรงแรม |
| 11 | telegram_notifications | 0 | ✅ ใช้งาน | การแจ้งเตือน (รีเซ็ตแล้ว) |
| 12 | activity_logs | 2 | ✅ ใช้งาน | บันทึกการทำงาน |

---

### รายละเอียดโครงสร้างตาราง

#### 1. **users** - ผู้ใช้งานระบบ (6 users)
```sql
- id, username, password_hash, full_name
- role (admin/reception/housekeeping)
- email, phone, telegram_chat_id
- is_active, created_at, updated_at
```
**ข้อมูลปัจจุบัน**: admin, reception, reception1, housekeeping, housekeeper1, housekeeper2

#### 2. **rooms** - ห้องพัก (21 rooms)
```sql
- id, room_number, room_type (single/double)
- status (available/occupied/cleaning/maintenance)
- floor, max_occupancy
- created_at, updated_at, last_transfer_date
```
**ข้อมูลปัจจุบัน**: ห้อง 101-105 (ชั้น 1), 201-216 (ชั้น 2)

#### 3. **bookings** - การจอง
```sql
- id, booking_code, room_id
- guest_name, guest_phone, guest_id_number, guest_count
- plan_type (short/overnight)
- status (active/completed/cancelled)
- checkin_at, checkout_at
- base_amount, extra_amount, total_amount
- payment_method (cash/card/transfer)
- payment_status (pending/paid/partial)
- transfer_count, created_by
```

#### 4. **receipts** - ใบเสร็จ
```sql
- id, receipt_number, booking_id
- amount, payment_method, payment_status
- pdf_path, issued_by, issued_at
```

#### 5. **rates** - อัตราค่าห้อง (Legacy - ไม่ค่อยใช้)
```sql
- id, rate_type, description, price
- duration_hours, is_active
```
**ข้อมูลปัจจุบัน**:
- short_3h: ฿300 (3 ชม.)
- overnight: ฿800 (12 ชม.)
- extended: ฿100 (1 ชม.)

⚠️ **หมายเหตุ**: ตารางนี้เป็น legacy code ระบบใช้ `room_rates` เป็นหลัก

#### 6. **room_rates** - อัตราค่าห้อง (ตารางหลัก) ⭐
```sql
- id, rate_type (short/overnight)
- price, duration_hours, is_active
```
**ข้อมูลปัจจุบัน**:
- **Short-stay**: ฿250 (3 ชม.)
- **Overnight**: ฿400 (12 ชม.)

**ใช้งานใน**: checkin.php, checkout.php, rates_simple.php, helpers.php

#### 7. **room_transfers** - การย้ายห้อง
```sql
- id, booking_id, from_room_id, to_room_id
- transfer_reason, price_difference, total_adjustment
- transferred_by, transferred_at
- guest_notified, housekeeping_notified
- status, notes
```

#### 8. **transfer_billing** - การคิดค่าย้ายห้อง
```sql
- id, transfer_id, original_rate, new_rate
- rate_difference, nights_affected
- subtotal, tax_amount, service_charge
- total_adjustment, payment_status
```

#### 9. **housekeeping_jobs** - งานแม่บ้าน
```sql
- id, room_id, booking_id, task_type
- job_type (cleaning/maintenance/inspection)
- status (pending/in_progress/completed)
- priority (low/normal/high/urgent)
- assigned_to, started_at, completed_at
- actual_duration, telegram_sent
- created_by, created_at, updated_at
```

#### 10. **hotel_settings** - ตั้งค่าโรงแรม (13 settings)
```sql
- id, setting_key, setting_value
```
**ข้อมูลปัจจุบัน**:
- hotel_name: ชื่อโรงแรม
- hotel_address: ที่อยู่
- hotel_phone: เบอร์โทร
- tax_id: เลขผู้เสียภาษี
- และอื่นๆ

#### 11. **telegram_notifications** - การแจ้งเตือน
```sql
- id, notification_type, message
- chat_id, sent_at, status
- related_id, related_table
```

#### 12. **activity_logs** - บันทึกการทำงาน (Audit Trail)
```sql
- id, user_id, action, table_name
- record_id, old_values, new_values
- ip_address, user_agent, created_at
```

---

## 🔗 API & Routes

### Main Routes (Router System)

```php
# Dashboard
http://localhost/hotel-app/
http://localhost/hotel-app/?r=dashboard

# Room Management
?r=rooms.board              # Room Status Board
?r=rooms.checkin            # Check-in Form
?r=rooms.checkout           # Check-out
?r=rooms.transfer           # Transfer Room
?r=rooms.transfer_history   # Transfer History

# Housekeeping
?r=housekeeping.jobs        # Job List
?r=housekeeping.job&id=1    # Job Details
?r=housekeeping.reports     # Reports

# Receipts
?r=receipts.history         # Receipt History
?r=receipts.view&id=1       # View Receipt

# Reports
?r=reports.bookings         # Booking Report
?r=reports.occupancy        # Occupancy Report
?r=reports.sales            # Sales Report

# System
?r=system.settings          # Hotel Settings
?r=system.rates             # Rate Management

# Admin
?r=admin.rooms              # Manage Rooms
```

### API Endpoints

```php
# Room Status API
POST /api/room_status.php
Parameters: room_id, status

# Transfer Calculator
POST /api/calculate_transfer.php
Parameters: from_room_id, to_room_id, booking_id

# Get Room Booking
GET /api/get_room_booking.php?room_id=1

# Check Notifications
GET /api/check_notifications.php
```

---

## 🎨 UI/UX Features

- ✅ **Responsive Design** - Bootstrap 5 Grid System
- ✅ **Dark Mode Support** - CSS Variables
- ✅ **Icon Library** - Bootstrap Icons
- ✅ **Thai Font** - Noto Sans Thai
- ✅ **Color Scheme**
  - Primary: `#0d6efd` (Blue)
  - Success: `#28a745` (Green)
  - Warning: `#ffc107` (Yellow)
  - Danger: `#dc3545` (Red)

---

## 🔧 Troubleshooting

### ❌ ไม่สามารถเข้าถึงหน้าเว็บได้

**ตรวจสอบ:**
1. Apache ทำงานอยู่หรือไม่ (XAMPP Control Panel)
2. URL ถูกต้อง: `http://localhost/hotel-app`
3. ไฟล์ `.htaccess` มีอยู่ในโฟลเดอร์หลัก

### ❌ Database Connection Error

**แก้ไข:**
1. ตรวจสอบ MySQL ทำงานใน XAMPP
2. ตรวจสอบชื่อ Database = `hotel_management`
3. ตรวจสอบไฟล์ `config/db.php`

```php
// Default Configuration
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = hotel_management
DB_USER = root
DB_PASS = (ว่างเปล่า)
```

### ❌ PHP Deprecated Warnings

**สาเหตุ:** PHP 8.1+ มีการเปลี่ยนแปลง `number_format()` ไม่รับ `null`

**แก้ไข:** อัปเดตโค้ดให้ใช้ `(int)` cast หรือ `?? 0`

### ❌ รายงานไม่แสดงข้อมูล

**สาเหตุ:** ไม่มีข้อมูล Transaction ในระบบ

**แก้ไข:** ทดสอบระบบโดย:
1. Check-in ห้องอย่างน้อย 1 ห้อง
2. Check-out และออกใบเสร็จ
3. รายงานจะเริ่มแสดงข้อมูล

---

## 🚀 การ Deploy

### Deploy บน Digital Ocean (Ubuntu Server)

ดูคู่มือฉบับเต็มที่: [`DEPLOYMENT.md`](DEPLOYMENT.md)

**สรุปขั้นตอน:**

1. สร้าง Droplet (Ubuntu 22.04 LTS)
2. ติดตั้ง LAMP Stack (Apache, MySQL, PHP 8.2)
3. Clone โปรเจคจาก Git
4. สร้าง Database และ Import Schema
5. ตั้งค่า Apache Virtual Host
6. ตั้งค่า SSL Certificate (Certbot)
7. ทดสอบระบบ

---

## 🧪 การทดสอบ

### Reset Database สำหรับการทดสอบ

```bash
# สำรองข้อมูลก่อน
D:\xampp\mysql\bin\mysqldump.exe -u root hotel_management > backup.sql

# เคลียร์ข้อมูล Transaction ทั้งหมด
D:\xampp\mysql\bin\mysql.exe -u root hotel_management < reset_for_testing.sql
```

Script `reset_for_testing.sql` จะ:
- ✅ ลบข้อมูล Bookings, Receipts, Transfers ทั้งหมด
- ✅ รีเซ็ตสถานะห้องเป็น Available
- ✅ คง Users และ Rooms ไว้เหมือนเดิม

---

## 📚 เอกสารเพิ่มเติม

- [`DEPLOYMENT.md`](DEPLOYMENT.md) - คู่มือการ Deploy บน Digital Ocean
- [`cleanup_unused_tables.sql`](cleanup_unused_tables.sql) - Script ทำความสะอาด Database
- [`reset_for_testing.sql`](reset_for_testing.sql) - Reset Database สำหรับทดสอบ

---

## 🤝 การสนับสนุน

### หากพบปัญหา
1. ตรวจสอบ [Troubleshooting](#-troubleshooting) ก่อน
2. ตรวจสอบ Activity Logs ในระบบ
3. ดู Error Logs: `D:\xampp\apache\logs\error.log`

### ติดต่อผู้พัฒนา
- 📧 Email: support@hotel-system.com
- 📱 Line: @hotel-system
- 🌐 Website: https://hotel-system.com

---

## 📝 License

MIT License - ใช้งานได้อย่างอิสระ

---

## 🎯 Version History

### v1.0.0 (2025-10-03) - Phase 1 Complete
- ✅ ระบบจัดการห้องพัก
- ✅ Check-in/Check-out
- ✅ Room Transfer
- ✅ Housekeeping Management
- ✅ Receipt Generation
- ✅ Reports & Analytics
- ✅ Telegram Notifications
- ✅ Activity Logs
- ✅ Database Cleanup & Optimization

---

**พัฒนาโดย**: Hotel Management System Team
**อัปเดตล่าสุด**: 3 ตุลาคม 2025
**สถานะ**: Production Ready ✅
