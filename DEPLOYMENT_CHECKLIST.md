# 🚀 Ubuntu Server Deployment Checklist

## ปัญหาที่พบและแก้แล้ว

### ✅ 1. Re-initialization Error (หน้าขาว)
- **ปัญหา:** ไฟล์ route load core files ซ้ำ → Fatal Error
- **แก้ไข:** เพิ่ม `if (!defined('APP_INIT'))` ในทุกไฟล์ route
- **สถานะ:** ✅ แก้แล้วทุกไฟล์

### ⚠️ 2. ไม่ Redirect ไป Login (กำลังแก้)
- **ปัญหา:** เปิด http://hotelapp.udoncoop.com ไม่ redirect ไป login
- **สาเหตุที่เป็นไปได้:**
  - .htaccess ไม่ทำงาน
  - mod_rewrite ไม่ได้เปิด
  - AllowOverride ไม่ถูกตั้ง

### ⚠️ 3. Login ไม่ผ่าน (กำลังแก้)
- **ปัญหา:** กรอก username/password แล้ว login ไม่ผ่าน
- **สาเหตุที่เป็นไปได้:**
  - Password hash ไม่ตรง
  - CSRF token ไม่ถูกต้อง
  - Session ไม่ทำงาน

---

## 📋 Pre-deployment Steps

### 1. Apache Configuration
```bash
# Enable required modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo systemctl restart apache2
```

### 2. VirtualHost Configuration
```apache
<VirtualHost *:80>
    ServerName hotelapp.udoncoop.com
    DocumentRoot /var/www/hotelapp

    <Directory /var/www/hotelapp>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hotelapp_error.log
    CustomLog ${APACHE_LOG_DIR}/hotelapp_access.log combined
</VirtualHost>
```

### 3. File Permissions
```bash
cd /var/www/hotelapp

# Set ownership
sudo chown -R www-data:www-data .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;

# Set file permissions
sudo find . -type f -exec chmod 644 {} \;

# Make storage writable
sudo chmod -R 775 storage/
sudo chmod 664 error.log
```

### 4. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hotelapp'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON hotel_management.* TO 'hotelapp'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u hotelapp -p hotel_management < database/schema.sql
```

### 5. Environment Configuration
```bash
# Copy .env file
cp .env.example .env

# Edit configuration
nano .env
```

Update these values in `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://hotelapp.udoncoop.com

DB_HOST=localhost
DB_DATABASE=hotel_management
DB_USERNAME=hotelapp
DB_PASSWORD=your-secure-password
```

### 6. Create Admin User
```sql
INSERT INTO users (username, password_hash, full_name, role, email, is_active, created_at)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Administrator',
    'admin',
    'admin@hotel.local',
    1,
    NOW()
);
```

---

## 🧪 Testing Steps

### Step 1: Test Basic PHP
```bash
# Create test file
echo "<?php phpinfo(); ?>" > /var/www/hotelapp/info.php

# Access: http://hotelapp.udoncoop.com/info.php
# Should show PHP info page
# Delete after testing: rm /var/www/hotelapp/info.php
```

### Step 2: Test Debug Page
```bash
# Access: http://hotelapp.udoncoop.com/debug.php
# Check:
# - All extensions loaded
# - Database connection works
# - Files are readable
```

### Step 3: Test Routing Debug
```bash
# Access: http://hotelapp.udoncoop.com/test_routing.php
# Check:
# - mod_rewrite enabled
# - .htaccess exists
# - baseUrl correct
# - $_GET['r'] works
```

### Step 4: Test Main App
```bash
# Test 1: Root URL
# Access: http://hotelapp.udoncoop.com/
# Expected: Redirect to login page OR show login page

# Test 2: Direct Login
# Access: http://hotelapp.udoncoop.com/?r=auth.login
# Expected: Show login form

# Test 3: Login
# Username: admin
# Password: password (or admin123 if you created custom user)
# Expected: Redirect to dashboard
```

---

## 🔍 Debugging Commands

### Check Apache Error Logs
```bash
# Real-time log monitoring
sudo tail -f /var/log/apache2/error.log

# Application error log
tail -f /var/www/hotelapp/error.log

# Search for specific errors
grep -i "fatal\|warning\|error" /var/www/hotelapp/error.log
```

### Check Apache Configuration
```bash
# Test configuration
sudo apache2ctl configtest

# Check loaded modules
apache2ctl -M | grep rewrite

# Check virtual hosts
apache2ctl -S
```

### Check File Permissions
```bash
# Check if www-data can read
sudo -u www-data cat /var/www/hotelapp/index.php

# Check .htaccess
ls -la /var/www/hotelapp/.htaccess
```

### Test Database Connection
```bash
# Test from command line
mysql -u hotelapp -p hotel_management -e "SELECT COUNT(*) FROM users;"
```

---

## 🔧 Common Issues & Solutions

### Issue 1: 404 on all routes
**Symptom:** http://hotelapp.udoncoop.com/ → 404 Not Found

**Solutions:**
```bash
# 1. Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# 2. Check VirtualHost AllowOverride
sudo nano /etc/apache2/sites-available/hotelapp.conf
# Make sure: AllowOverride All

# 3. Restart Apache
sudo systemctl restart apache2
```

### Issue 2: .htaccess not working
**Symptom:** Routes work only with index.php prefix

**Solutions:**
```bash
# Check if .htaccess is being read
sudo grep -r "AllowOverride" /etc/apache2/sites-available/

# Make sure it's set to All, not None
# Then restart Apache
```

### Issue 3: Login fails silently
**Symptom:** Submit login form, nothing happens

**Solutions:**
```bash
# 1. Check error log
tail -f /var/www/hotelapp/error.log

# 2. Check CSRF token
# Look for: "CSRF token verification failed"

# 3. Check database connection
# Look for: "Database connection failed"

# 4. Check password hash
# In MySQL:
SELECT username, password_hash FROM users WHERE username='admin';
```

### Issue 4: White screen (Blank page)
**Symptom:** Blank white page, no error shown

**Solutions:**
```bash
# 1. Enable error display temporarily
# In index.php, line 27-29:
error_reporting(E_ALL);
ini_set('display_errors', 1);

# 2. Check PHP error log
sudo tail -f /var/log/apache2/error.log

# 3. Check file permissions
ls -la /var/www/hotelapp/
```

---

## 📊 Performance Optimization (Optional)

### Enable OPcache
```bash
sudo nano /etc/php/8.x/apache2/php.ini

# Add/uncomment:
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000

sudo systemctl restart apache2
```

### Enable Caching Headers
Already configured in `.htaccess`:
- Static assets cached for 1 year
- CSS/JS cached for 1 month

---

## 🔒 Security Checklist

- [ ] Change default admin password
- [ ] Remove debug.php and test_routing.php in production
- [ ] Set APP_DEBUG=false in .env
- [ ] Enable HTTPS (uncomment in .htaccess)
- [ ] Set secure session cookie settings
- [ ] Regularly backup database
- [ ] Keep PHP and Apache updated
- [ ] Review file permissions

---

## 📝 Final Production Steps

### Before Going Live:
```bash
# 1. Remove debug files
rm /var/www/hotelapp/debug.php
rm /var/www/hotelapp/test_routing.php
rm /var/www/hotelapp/info.php

# 2. Disable debug mode
nano /var/www/hotelapp/.env
# Set: APP_DEBUG=false

# 3. Remove debug logging from index.php
# Comment out lines 27-29 (display_errors)
# Comment out lines 49-87 (debug error_log calls)

# 4. Clear error log
> /var/www/hotelapp/error.log

# 5. Set proper permissions
sudo chown -R www-data:www-data /var/www/hotelapp
sudo chmod 644 /var/www/hotelapp/error.log
```

---

## 📞 Support

If issues persist:
1. Check error.log: `tail -100 /var/www/hotelapp/error.log`
2. Check Apache log: `sudo tail -100 /var/log/apache2/error.log`
3. Verify all steps in this checklist
4. Test with debug pages first

**Current Status:**
- ✅ Code fixed for Ubuntu compatibility
- ⚠️ Awaiting deployment test results
- 🔍 Debug tools ready (debug.php, test_routing.php)
