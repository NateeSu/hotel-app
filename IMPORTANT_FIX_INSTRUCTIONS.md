# 🔴 CRITICAL FIX REQUIRED

## Problem Found
หน้าขาวเกิดจากไฟล์ route pages กำลัง **re-initialize** ระบบซ้ำ ทำให้เกิด **"function already declared"** error!

## Root Cause
ไฟล์ทั้งหมดใน routes (rooms/, auth/, dashboard.php, etc.) มีโค้ดแบบนี้:

```php
// ❌ WRONG - จะ load ไฟล์ซ้ำถ้าถูกเรียกจาก index.php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
```

เมื่อ `index.php` โหลดไฟล์เหล่านี้แล้ว แล้ว route file load ซ้ำอีกครั้ง = **FATAL ERROR!**

---

## ✅ Solution Pattern

เปลี่ยนจาก:
```php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
```

เป็น:
```php
// Only initialize if not already loaded by index.php
if (!defined('APP_INIT')) {
    define('APP_INIT', true);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Define base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl = $protocol . '://' . $host . $scriptPath;
    $GLOBALS['baseUrl'] = $baseUrl;

    // Load required files
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/helpers.php';
    require_once __DIR__ . '/../includes/csrf.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/router.php';
} else {
    // Already initialized by index.php
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}
```

---

## 📋 Files That MUST Be Fixed

### ✅ Already Fixed:
- [x] auth/login.php
- [x] dashboard.php

### ❌ Need to Fix:
- [ ] housekeeping/jobs.php
- [ ] housekeeping/reports.php
- [ ] system/settings.php
- [ ] system/rates_simple.php
- [ ] rooms/board.php
- [ ] rooms/checkin.php
- [ ] rooms/checkout.php
- [ ] rooms/checkoutSuccess.php
- [ ] rooms/move.php
- [ ] rooms/cleanDone.php
- [ ] rooms/transfer.php
- [ ] rooms/transfer_history.php
- [ ] receipts/view.php
- [ ] receipts/history.php
- [ ] reports/bookings.php
- [ ] reports/occupancy.php
- [ ] reports/sales.php

---

## 🚀 Quick Fix Steps

### For each file:

1. **Open the file**
2. **Find** the initialization section (usually top 20-30 lines)
3. **Wrap** initialization code with `if (!defined('APP_INIT'))`
4. **Add** `else` block to use existing `$baseUrl`
5. **Save** and test

### Example for `rooms/board.php`:

```php
<?php
/**
 * Room Board
 */

// Only initialize if not already loaded by index.php
if (!defined('APP_INIT')) {
    define('APP_INIT', true);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set('Asia/Bangkok');

    // Define base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Go up one level from /rooms
    $baseUrl = $protocol . '://' . $host . $scriptPath;
    $GLOBALS['baseUrl'] = $baseUrl;

    // Load required files
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/helpers.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/router.php';
    require_once __DIR__ . '/../includes/csrf.php';
} else {
    // Already initialized by index.php
    $baseUrl = $GLOBALS['baseUrl'] ?? '';
}

// Rest of the file...
requireLogin(['reception', 'admin']);
// ... etc
```

---

## ⚠️ Important Notes

1. **Path adjustment**: ไฟล์ใน subdirectories (rooms/, auth/) ต้องใช้ `__DIR__ . '/../...'`
2. **Base URL calculation**: ต้องคำนวณ path ให้ถูกต้องตาม directory level
3. **Order matters**: ต้อง load `config/db.php` ก่อน `auth.php` เสมอ
4. **Test each file**: หลังแก้แต่ละไฟล์ ต้องทดสอบว่าเข้าได้

---

## 🧪 Testing

After fixing all files:

1. Clear browser cache
2. Go to: `http://your-server/hotel-app/`
3. Should show login page
4. Login and test each menu
5. Check error.log for any remaining issues

---

## 📞 Status

- **Dashboard & Login**: ✅ Fixed
- **Other Routes**: ❌ Need fixing
- **Estimated time**: 15-20 minutes to fix all files
