# Changelog

All notable changes to ARKAS BKU System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-09-23

### Added ✨

-   Application versioning system with helper functions (`app_version()`, `app_build()`, `app_info()`)
-   Version configuration in `config/app.php` and environment variables
-   Comprehensive troubleshooting guide in README
-   Enhanced error handling with detailed logging
-   Database transaction support for critical operations
-   Strict MIME type validation for file uploads
-   VERSION file for formal version tracking
-   Helper functions autoloaded via composer.json

### Fixed 🐛

-   HTML duplicate attributes in registration forms (`name="name" name="name"`)
-   Email configuration errors during user registration ("From" header missing)
-   Composer installation issues with ZIP extraction (using `--prefer-source`)
-   MySQL 8.4+ authentication compatibility
-   Race conditions in admin role management
-   Memory optimization in database queries (`getTaxKeywordsFromDatabase()`)
-   Decimal column precision issues in BKU master entry tables

### Changed 🔄

-   Improved file upload security validation with `mimetypes` rule
-   Enhanced error messages for better user experience
-   Optimized database query performance (`take()` → `limit()`, reduced limits)
-   Updated documentation with installation troubleshooting
-   Better structured logging without exposing sensitive data

### Security 🔒

-   Added `lockForUpdate()` for admin operations to prevent race conditions
-   Enhanced file upload MIME type validation beyond just extensions
-   Improved input validation across all controllers
-   Better error logging practices to avoid information disclosure

### Performance ⚡

-   Reduced database query limits from 100 to 50 records for memory efficiency
-   Implemented proper config and route caching
-   Better memory management in tax keywords processing
-   Optimized database indexes for improved query performance

## [1.0.0] - 2025-09-20

### Added ✨

-   Initial release of ARKAS BKU System
-   Multi-tenant architecture with complete data isolation
-   BKU (Buku Kas Umum) financial management system
-   Tunai (Cash) financial tracking
-   Excel import/export functionality for BKU data
-   Two-factor authentication (2FA) support
-   Role-based access control (Admin vs User)
-   Admin dashboard with subscription monitoring
-   User dashboard with financial overview
-   School management system
-   Settings management for mail configuration
-   Responsive AdminLTE 3 interface
-   Professional financial reporting
-   Indonesian Rupiah currency formatting
-   Real-time saldo calculations
-   Tax status tracking and categorization
-   Complete authentication system with Laravel Fortify
-   Database migrations for all system tables
-   Seeders for admin user creation
-   Comprehensive validation rules
-   Error handling and logging system

### Security 🔒

-   Multi-tenant data isolation (users can only see their own data)
-   Two-factor authentication for enhanced security
-   Role-based permissions system
-   CSRF protection on all forms
-   Encrypted sensitive data storage
-   Secure password hashing with bcrypt

### Performance ⚡

-   Optimized database queries with proper indexing
-   Efficient data pagination (25 records per page)
-   Cached financial calculations for better performance
-   Proper eager loading to prevent N+1 queries
-   Session-based data storage for Excel import previews

---

## Versioning Strategy

This project follows [Semantic Versioning](https://semver.org/):

-   **MAJOR** version when making incompatible API changes
-   **MINOR** version when adding functionality in a backwards compatible manner
-   **PATCH** version when making backwards compatible bug fixes

## Release Process

1. Update version in `config/app.php`, `.env`, and `VERSION` file
2. Update `CHANGELOG.md` with new version details
3. Commit changes with descriptive message
4. Create annotated git tag: `git tag -a vX.X.X -m "Release vX.X.X"`
5. Push commits and tags: `git push origin main && git push origin vX.X.X`
6. Create GitHub Release with release notes
7. Update documentation if needed

## Support

For questions about specific releases or upgrade paths, please:

-   Check the [Installation Troubleshooting](README.md#troubleshooting-guide) section
-   Review [Release Notes](RELEASE_NOTES.md) for detailed changes
-   Open an [Issue](https://github.com/kevindoni/ARKAS/issues) on GitHub
