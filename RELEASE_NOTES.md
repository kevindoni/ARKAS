# Release Notes - ARKAS v1.1.0

## 🎉 What's New in v1.1.0

### 🐛 Critical Bug Fixes

-   **Fixed HTML Duplicate Attributes**: Removed duplicate `name` attributes in registration forms that could cause form submission issues
-   **Fixed Email Configuration Error**: Resolved `An email must have a "From" or a "Sender" header` error during user registration
-   **Fixed Composer Installation Issues**: Resolved ZIP extraction problems by implementing `--prefer-source` installation method
-   **Fixed MySQL Authentication**: Configured proper MySQL connection compatibility with MySQL 8.4+ using modern authentication methods

### 🔒 Security Enhancements

-   **Enhanced File Upload Security**: Added strict MIME type validation (`mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`) for Excel file uploads
-   **Fixed Race Condition in Admin Management**: Implemented database transactions with `lockForUpdate()` to prevent concurrent admin role modifications
-   **Improved Input Validation**: Enhanced validation rules across all controllers to prevent malicious input
-   **Better Error Logging**: Added structured logging for debugging without exposing sensitive information

### ⚡ Performance Optimizations

-   **Optimized Database Queries**: Changed inefficient `take()` to `limit()` and reduced query limits from 100 to 50 records for better memory usage
-   **Better Memory Management**: Prevented potential memory leaks in `getTaxKeywordsFromDatabase()` method
-   **Cached Configurations**: Implemented proper config and route caching for faster application startup

### 🎯 Enhanced User Experience

-   **Improved 2FA Error Messages**: Added detailed error logging and user-friendly messages in SecurityController
-   **Better File Upload Feedback**: Enhanced error messages for invalid files and upload failures
-   **Fixed Email Verification Flow**: Properly configured email settings or disabled verification to prevent registration blocks
-   **Responsive Design Improvements**: Ensured proper display across all devices

### 🗃️ Database & Infrastructure

-   **Migration Fixes**: Resolved decimal column precision issues in BKU master entry tables
-   **Proper Foreign Key Constraints**: Enhanced data integrity with proper relationships between tables
-   **Optimized Database Indexes**: Improved query performance with strategic indexing

### 📟 Version Management

-   **Application Versioning System**: Added comprehensive version tracking with helper functions
-   **Build Number Support**: Implemented automatic build numbering based on release dates
-   **Version Configuration**: Added `APP_VERSION` environment variable for easy version management

---

## 🚀 Installation & Upgrade

### Fresh Installation

```bash
git clone https://github.com/kevindoni/ARKAS.git
cd ARKAS
composer install --prefer-source
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
php artisan serve
```

### Upgrading from v1.0.0

```bash
git pull origin main
composer install --prefer-source
php artisan migrate
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔧 System Requirements

-   **PHP**: >= 8.4.0
-   **Laravel**: >= 12.x
-   **Database**: MySQL 8.0+ or PostgreSQL 13+
-   **Web Server**: Apache/Nginx
-   **Extensions**: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, Zip

---

## 🏷️ Version Information

-   **Version**: 1.1.0
-   **Release Date**: September 23, 2025
-   **Build**: 2025.09.23
-   **Database Schema**: 13 migrations
-   **Breaking Changes**: None (backward compatible)

---

## 📝 Migration Guide

This release is fully backward compatible with v1.0.0. No breaking changes were introduced.

### Configuration Updates

Add to your `.env` file:

```env
APP_VERSION=1.1.0
MAIL_FROM_ADDRESS=admin@yourdomain.com
MAIL_MAILER=log
```

---

## 🎯 What's Coming Next (v1.2.0)

-   Enhanced Excel import validation
-   Advanced financial reporting features
-   Multi-language support (Indonesian/English)
-   API endpoints for mobile app integration
-   Advanced user permission system

---

## 🙏 Acknowledgments

Thanks to all contributors and users who reported issues and provided feedback to make this release possible.

**Full Changelog**: https://github.com/kevindoni/ARKAS/compare/v1.0.0...v1.1.0
