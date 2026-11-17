# 🏦 bKash Loan Management System

একটি সম্পূর্ণ bKash loan management system যা automatic database setup এবং admin panel সহ আসে।

## 🚀 Live Server এ Deployment Instructions

### ধাপ ১: ফাইল আপলোড
1. সমস্ত project files আপনার hosting server এর root directory তে আপলোড করুন
2. File structure এইরকম হওয়া উচিত:
```
your-domain.com/
├── setup.php (প্রথমে এটি run করুন)
├── admin_panel.php
├── lon_request.php
├── Index.html
├── script.js
├── style.css
├── config/
│   └── database.php
└── img/
    └── (সব image files)
```

### ধাপ ২: Database Setup
1. আপনার browser এ যান: `https://your-domain.com/setup.php`
2. Setup script automatically:
   - Database তৈরি করবে
   - প্রয়োজনীয় tables তৈরি করবে
   - Default admin user তৈরি করবে
   - Sample data insert করবে

### ধাপ ৩: Database Credentials Update
**setup.php ফাইলে এই values গুলো আপনার hosting provider এর অনুযায়ী update করুন:**

```php
// Database configuration - UPDATE THESE VALUES
$host = 'localhost';              // Usually 'localhost'
$username = 'your_db_username';   // Your cPanel database username
$password = 'your_db_password';   // Your cPanel database password
$database = 'bkash_loan_db';      // Your database name

// Admin credentials - CHANGE THESE FOR SECURITY
$admin_username = 'admin';
$admin_password = 'admin123';     // CHANGE THIS PASSWORD!
```

### ধাপ ৪: Security
1. Setup সম্পন্ন হওয়ার পর `setup.php` ফাইল delete করুন
2. Admin password পরিবর্তন করুন
3. Database credentials secure রাখুন

## 🔐 Default Admin Credentials

**⚠️ প্রথম login এর পর অবশ্যই password পরিবর্তন করুন!**

- **Username:** `admin`
- **Password:** `admin123`
- **Admin Panel:** `https://your-domain.com/admin_panel.php`

## 📱 System Features

### User Features:
- 💰 Loan request form
- 📱 Mobile number verification
- 🔢 OTP verification
- 🔐 PIN verification
- ⏳ Processing status page
- ✅ Success/Failed pages

### Admin Features:
- 📊 Real-time loan requests dashboard
- 📱 Mobile responsive design
- 📋 Copy-to-clipboard functionality
- ✅ Approve/Reject loans
- 🗑️ Delete processed loans
- 📄 Pagination support
- 🔄 Auto-refresh every 5 seconds

## 🗄️ Database Structure

### `loan_requests` Table:
- `id` - Primary key
- `mobile_number` - User's mobile number
- `loan_amount` - Requested loan amount
- `loan_category` - Type of loan
- `otp_code` - Generated OTP
- `pin_code` - User's PIN (optional)
- `admin_action` - pending/approved/rejected
- `created_at` - Request timestamp
- `updated_at` - Last update timestamp

### `admin_users` Table:
- `id` - Primary key
- `username` - Admin username
- `password` - Hashed password
- `created_at` - Account creation time
- `last_login` - Last login timestamp

## 🔧 Technical Requirements

- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Web Server:** Apache/Nginx
- **Extensions:** PDO, PDO_MySQL

## 📂 File Structure

```
bKash-Loan-System/
├── 📄 Index.html              # Homepage
├── 📄 lon_request.php         # Loan request form
├── 📄 number_verification.php # Mobile verification
├── 📄 otp_verification.php    # OTP verification
├── 📄 pin_verification.php    # PIN verification
├── 📄 processing.php          # Processing status
├── 📄 loan_success.php        # Success page
├── 📄 loan_failed.php         # Failed page
├── 📄 admin_panel.php         # Admin dashboard
├── 📄 admin_login.php         # Admin login
├── 📄 admin_logout.php        # Admin logout
├── 📄 script.js               # JavaScript functionality
├── 📄 style.css               # Custom styles
├── 📄 setup.php               # Database setup script
├── 📁 config/
│   └── 📄 database.php        # Database configuration
└── 📁 img/                    # Images and assets
```


---

**🎉 আপনার bKash Loan Management System এখন live server এ ready!**

**Important:** Security এর জন্য setup.php delete করতে ভুলবেন না!