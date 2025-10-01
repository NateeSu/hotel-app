# คู่มือการติดตั้งระบบ Hotel Management บน Digital Ocean

## สารบัญ
1. [การสร้าง Droplet](#1-การสร้าง-droplet)
2. [การติดตั้ง LAMP Stack](#2-การติดตั้ง-lamp-stack)
3. [การติดตั้ง phpMyAdmin](#3-การติดตั้ง-phpmyadmin)
4. [การ Deploy โปรเจ็ค](#4-การ-deploy-โปรเจ็ค)
5. [การตั้งค่า Database](#5-การตั้งค่า-database)
6. [การตั้งค่า Security](#6-การตั้งค่า-security)
7. [การทดสอบระบบ](#7-การทดสอบระบบ)

---

## 1. การสร้าง Droplet

### 1.1 เข้าสู่ Digital Ocean
- เข้า [https://cloud.digitalocean.com](https://cloud.digitalocean.com)
- Login เข้าสู่บัญชี

### 1.2 สร้าง Droplet ใหม่
1. คลิก **"Create"** → **"Droplets"**
2. เลือก **Image**:
   - Distribution: **Ubuntu 22.04 LTS**
3. เลือก **Droplet Type**:
   - Basic Plan
   - CPU Options: Regular
   - Size: **$6/month** (1 GB RAM / 1 CPU)
4. เลือก **Datacenter Region**:
   - Singapore (SGP1) - ใกล้ไทยที่สุด
5. **Authentication**:
   - เลือก **Password**
   - ตั้งรหัสผ่าน root ที่แข็งแรง (เก็บไว้ดี!)
6. **Hostname**: ตั้งชื่อ เช่น `hotel-management`
7. คลิก **"Create Droplet"**

### 1.3 รอจนสร้างเสร็จ
- รอประมาณ 1-2 นาที
- จดบันทึก **IP Address** ของ Droplet (เช่น `159.89.xxx.xxx`)

---

## 2. การติดตั้ง LAMP Stack

### 2.1 เชื่อมต่อ SSH
เปิด Terminal (Mac/Linux) หรือ PowerShell (Windows) แล้วพิมพ์:
```bash
ssh root@YOUR_DROPLET_IP
```
- แทน `YOUR_DROPLET_IP` ด้วย IP ของ Droplet
- ใส่รหัสผ่านที่ตั้งไว้

### 2.2 Update System
```bash
apt update && apt upgrade -y
```

### 2.3 ติดตั้ง Apache
```bash
apt install apache2 -y
systemctl start apache2
systemctl enable apache2
```

**ทดสอบ**: เปิดเว็บเบราว์เซอร์ไปที่ `http://YOUR_DROPLET_IP` ควรเห็นหน้า Apache2 Ubuntu Default Page

### 2.4 ติดตั้ง MySQL
```bash
apt install mysql-server -y
```

#### ตั้งค่า MySQL Security
```bash
mysql_secure_installation
```

ตอบคำถามดังนี้:
```
- Validate Password Component? → n
- Set root password? → Y (ตั้งรหัสผ่าน MySQL root)
- Remove anonymous users? → Y
- Disallow root login remotely? → Y
- Remove test database? → Y
- Reload privilege tables? → Y
```

### 2.5 ติดตั้ง PHP
```bash
apt install php libapache2-mod-php php-mysql php-xml php-mbstring php-json php-curl php-gd php-zip -y
```

**ตรวจสอบเวอร์ชัน**:
```bash
php -v
```

---

## 3. การติดตั้ง phpMyAdmin

### 3.1 ติดตั้ง phpMyAdmin
```bash
apt install phpmyadmin -y
```

ระหว่างติดตั้งจะมีคำถาม:
```
- Web server to configure: เลือก apache2 (กด Space เพื่อเลือก, Enter เพื่อยืนยัน)
- Configure database with dbconfig-common? → Yes
- MySQL application password: ตั้งรหัสผ่านสำหรับ phpMyAdmin (จดไว้)
```

### 3.2 Enable Apache Modules
```bash
a2enconf phpmyadmin.conf
systemctl reload apache2
```

### 3.3 สร้าง MySQL User สำหรับ phpMyAdmin
```bash
mysql -u root -p
```

ใส่รหัสผ่าน MySQL root แล้วรันคำสั่งใน MySQL:
```sql
CREATE USER 'phpmyadmin'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EXIT;
```

**ทดสอบ**: เปิด `http://YOUR_DROPLET_IP/phpmyadmin`
- Username: `phpmyadmin`
- Password: รหัสผ่านที่ตั้งไว้

---

## 4. การ Deploy โปรเจ็ค

### 4.1 ติดตั้ง Git (ถ้ายังไม่มี)
```bash
apt install git -y
```

### 4.2 Clone โปรเจ็ค
```bash
cd /var/www/html
git clone YOUR_REPOSITORY_URL hotel-app
```

**หรือ** ถ้าไม่ใช้ Git สามารถ Upload ผ่าน SCP:
```bash
# จากเครื่อง Local (เปิด Terminal ใหม่)
scp -r /path/to/hotel-app root@YOUR_DROPLET_IP:/var/www/html/
```

### 4.3 ตั้งค่า Permissions
```bash
cd /var/www/html/hotel-app
chown -R www-data:www-data .
chmod -R 755 .
```

### 4.4 สร้างไฟล์ .env
```bash
cd /var/www/html/hotel-app
cp .env.example .env
nano .env
```

แก้ไขค่าในไฟล์ `.env`:
```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_DROPLET_IP/hotel-app

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=hotel_management
DB_USER=hotel_user
DB_PASS=your_db_password

# Session
SESSION_LIFETIME=7200
SESSION_TIMEOUT=1800
```

กด `Ctrl+O` → `Enter` → `Ctrl+X` เพื่อบันทึก

### 4.5 ตั้งค่า Apache Virtual Host (Optional - แนะนำ)
```bash
nano /etc/apache2/sites-available/hotel-app.conf
```

เพิ่มเนื้อหา:
```apache
<VirtualHost *:80>
    ServerAdmin admin@example.com
    DocumentRoot /var/www/html/hotel-app

    <Directory /var/www/html/hotel-app>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hotel-app-error.log
    CustomLog ${APACHE_LOG_DIR}/hotel-app-access.log combined
</VirtualHost>
```

Enable site และ rewrite module:
```bash
a2ensite hotel-app.conf
a2enmod rewrite
systemctl reload apache2
```

---

## 5. การตั้งค่า Database

### 5.1 สร้าง Database และ User
```bash
mysql -u root -p
```

รันคำสั่ง SQL:
```sql
-- สร้าง Database
CREATE DATABASE hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- สร้าง User
CREATE USER 'hotel_user'@'localhost' IDENTIFIED BY 'your_strong_db_password';

-- ให้สิทธิ์
GRANT ALL PRIVILEGES ON hotel_management.* TO 'hotel_user'@'localhost';
FLUSH PRIVILEGES;

-- ตรวจสอบ
SHOW DATABASES;
EXIT;
```

### 5.2 Import Database Schema
```bash
cd /var/www/html/hotel-app
mysql -u hotel_user -p hotel_management < migrations/001_initial_schema.sql
mysql -u hotel_user -p hotel_management < migrations/002_add_room_rates.sql
mysql -u hotel_user -p hotel_management < migrations/003_create_settings_and_receipts_tables.sql
```

### 5.3 ตรวจสอบการ Import
เข้า phpMyAdmin: `http://YOUR_DROPLET_IP/phpmyadmin`
- Login ด้วย user `hotel_user`
- ตรวจสอบว่ามีตารางครบทุกตาราง

### 5.4 สร้าง Admin User (ผ่าน phpMyAdmin)
1. เข้า phpMyAdmin
2. เลือก database `hotel_management`
3. เลือกตาราง `users`
4. คลิก **Insert**
5. กรอกข้อมูล:
   ```
   username: admin
   password_hash: (ใช้เครื่องมือ hash หรือดูวิธีด้านล่าง)
   full_name: Administrator
   role: admin
   is_active: 1
   ```

**วิธีสร้าง password hash**:
```bash
php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
```
- คัดลอกผลลัพธ์ไปใส่ในช่อง `password_hash`

---

## 6. การตั้งค่า Security

### 6.1 ตั้งค่า Firewall (UFW)
```bash
ufw allow OpenSSH
ufw allow 'Apache Full'
ufw enable
ufw status
```

### 6.2 ปิดการแสดง PHP Version
```bash
nano /etc/php/8.1/apache2/php.ini
```

ค้นหาและแก้ไข:
```ini
expose_php = Off
```

Restart Apache:
```bash
systemctl restart apache2
```

### 6.3 ป้องกัน phpMyAdmin
```bash
nano /etc/apache2/conf-available/phpmyadmin.conf
```

เพิ่มการจำกัด IP (ถ้าต้องการ):
```apache
<Directory /usr/share/phpmyadmin>
    <RequireAny>
        Require ip YOUR_LOCAL_IP
        Require ip 127.0.0.1
    </RequireAny>
</Directory>
```

หรือเปลี่ยน URL ของ phpMyAdmin:
```bash
nano /etc/apache2/conf-available/phpmyadmin.conf
```

เปลี่ยน:
```apache
Alias /phpmyadmin /usr/share/phpmyadmin
```

เป็น:
```apache
Alias /secret-admin /usr/share/phpmyadmin
```

Reload Apache:
```bash
systemctl reload apache2
```

### 6.4 ตั้งค่า HTTPS (แนะนำ - ใช้ Certbot)
```bash
apt install certbot python3-certbot-apache -y

# ถ้ามี domain
certbot --apache -d your-domain.com
```

---

## 7. การทดสอบระบบ

### 7.1 ทดสอบการเข้าถึงเว็บไซต์
เปิดเว็บเบราว์เซอร์:
- ถ้าใช้ Virtual Host: `http://YOUR_DROPLET_IP`
- ถ้าไม่ได้ตั้ง Virtual Host: `http://YOUR_DROPLET_IP/hotel-app`

### 7.2 ทดสอบ Login
- Username: `admin`
- Password: รหัสผ่านที่ตั้งไว้

### 7.3 ทดสอบฟังก์ชันหลัก
- ✅ Dashboard แสดงสถิติ
- ✅ Room Board แสดงห้องพัก
- ✅ Check-in/Check-out
- ✅ ออกใบเสร็จ
- ✅ รายงาน

### 7.4 ตรวจสอบ Error Logs
```bash
# Apache Error Log
tail -f /var/log/apache2/error.log

# PHP Error Log
tail -f /var/log/apache2/hotel-app-error.log

# MySQL Error Log
tail -f /var/log/mysql/error.log
```

---

## 8. การบำรุงรักษา

### 8.1 Backup Database
```bash
# Backup แบบ manual
mysqldump -u hotel_user -p hotel_management > backup_$(date +%Y%m%d).sql

# ตั้ง Cron Job สำหรับ backup อัตโนมัติ
crontab -e
```

เพิ่มบรรทัด (backup ทุกวันเวลา 2:00 น.):
```cron
0 2 * * * mysqldump -u hotel_user -p'your_password' hotel_management > /root/backups/hotel_$(date +\%Y\%m\%d).sql
```

### 8.2 Update โปรเจ็ค
```bash
cd /var/www/html/hotel-app
git pull origin main
chown -R www-data:www-data .
systemctl reload apache2
```

### 8.3 Monitor Resources
```bash
# ดู CPU และ Memory
htop

# ดู Disk Space
df -h

# ดู Apache Status
systemctl status apache2

# ดู MySQL Status
systemctl status mysql
```

---

## 9. การแก้ไขปัญหาที่พบบ่อย

### 9.1 ไม่สามารถเข้าถึงเว็บไซต์
```bash
# ตรวจสอบ Apache
systemctl status apache2
systemctl restart apache2

# ตรวจสอบ Firewall
ufw status
```

### 9.2 Database Connection Error
```bash
# ตรวจสอบ MySQL
systemctl status mysql
systemctl restart mysql

# ตรวจสอบ credentials ใน config/db.php
nano /var/www/html/hotel-app/config/db.php
```

### 9.3 Permission Denied
```bash
cd /var/www/html/hotel-app
chown -R www-data:www-data .
chmod -R 755 .
```

### 9.4 ไม่สามารถเข้า phpMyAdmin
```bash
# ตรวจสอบ config
nano /etc/apache2/conf-available/phpmyadmin.conf

# Reload Apache
systemctl reload apache2

# ตรวจสอบ URL
# http://YOUR_IP/phpmyadmin
```

---

## 10. ข้อมูลสำคัญที่ต้องจดบันทึก

✅ **Server Information**
- Droplet IP: `_______________`
- SSH Password: `_______________`
- Root Password: `_______________`

✅ **MySQL Information**
- MySQL Root Password: `_______________`
- Database Name: `hotel_management`
- DB User: `hotel_user`
- DB Password: `_______________`

✅ **phpMyAdmin Information**
- URL: `http://YOUR_IP/phpmyadmin`
- Username: `phpmyadmin` หรือ `hotel_user`
- Password: `_______________`

✅ **Application Information**
- URL: `http://YOUR_IP/hotel-app`
- Admin Username: `admin`
- Admin Password: `_______________`

---

## 11. Resources และเอกสารเพิ่มเติม

- Digital Ocean Documentation: https://docs.digitalocean.com
- Apache Documentation: https://httpd.apache.org/docs/
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/

---

**หมายเหตุ**:
- เก็บรหัสผ่านทั้งหมดไว้ในที่ปลอดภัย
- แนะนำให้ใช้ HTTPS สำหรับ Production
- Backup database เป็นประจำ
- อัพเดท System และ Dependencies เป็นประจำ

**สร้างโดย**: Hotel Management System Team
**วันที่**: <?php echo date('d/m/Y'); ?>
**เวอร์ชัน**: 1.0
