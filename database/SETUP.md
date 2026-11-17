# bKash Loan Management System - Setup Guide

## Database Setup

### 1. Create Database
First, create a MySQL database named `bkash_loan_system`:

```sql
CREATE DATABASE bkash_loan_system;
USE bkash_loan_system;
```

### 2. Import Database Tables
Run the SQL script located in `database/create_tables.sql`:

```bash
mysql -u root -p bkash_loan_system < database/create_tables.sql
```

Or manually execute the SQL commands from the file in your MySQL client.

### 3. Database Configuration
Update the database connection settings in `config/database.php` if needed:

```php
$host = 'localhost';
$dbname = 'bkash_loan_system';
$username = 'root';
$password = '';
```

## Admin Access

### Default Admin Credentials
- **Username:** admin
- **Password:** admin123

### Admin Panel Access
1. Navigate to `admin_login.php`
2. Login with the default credentials
3. Access the admin panel at `admin_panel.php`

## File Structure

```
bkash-loan-system/
├── config/
│   └── database.php          # Database configuration
├── database/
│   └── create_tables.sql     # Database schema
├── img/                      # Images and assets
├── admin_login.php           # Admin login page
├── admin_panel.php           # Admin dashboard
├── admin_update.php          # API for real-time updates
├── lon_request.php           # Loan request form
├── number_verification.php   # Mobile number verification
├── otp_verification.php      # OTP verification
├── pin_verification.php      # PIN verification
├── processing.php            # Loan processing page
├── loan_success.php          # Success page
├── loan_failed.php           # Rejection page
├── script.js                 # Admin panel JavaScript
└── style.css                 # Styles
```

## Features

### User Flow
1. **Loan Request** - User selects amount and category
2. **Mobile Verification** - Enter and verify mobile number
3. **OTP Verification** - Enter 6-digit OTP code
4. **PIN Verification** - Enter 5-digit PIN
5. **Processing** - Wait for admin approval
6. **Result** - Success or rejection page

### Admin Features
- Real-time loan application monitoring
- Approve/reject loan applications
- View application statistics
- Filter applications by status
- Auto-refresh dashboard
- Mobile responsive design

### Real-time Features
- Live status updates
- Auto-refresh admin panel
- Real-time notifications
- Status change tracking

## Technical Details

### Technologies Used
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Styling:** Tailwind CSS
- **Icons:** Font Awesome

### Security Features
- Session management
- SQL injection prevention (PDO)
- Input validation
- Admin authentication
- CSRF protection

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **Admin Login Issues**
   - Ensure admin_users table has data
   - Check password hashing
   - Verify session configuration

3. **Real-time Updates Not Working**
   - Check JavaScript console for errors
   - Verify admin_update.php is accessible
   - Ensure proper CORS headers

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Support

For technical support or questions, please check the code comments or contact the development team.