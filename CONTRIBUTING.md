# Contributing to ARKAS BKU System

First off, thank you for considering contributing to ARKAS! It's people like you that make ARKAS such a great tool for Indonesian educational institutions.

## 📋 Table of Contents

-   [Code of Conduct](#code-of-conduct)
-   [Getting Started](#getting-started)
-   [How Can I Contribute?](#how-can-i-contribute)
-   [Development Setup](#development-setup)
-   [Pull Request Process](#pull-request-process)
-   [Style Guidelines](#style-guidelines)
-   [Release Process](#release-process)

## 🤝 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## 🚀 Getting Started

### Prerequisites

-   PHP >= 8.4.0
-   Composer
-   MySQL 8.0+ or PostgreSQL 13+
-   Node.js & NPM (for asset compilation)
-   Git

### Development Setup

1. **Fork and Clone**

    ```bash
    git clone https://github.com/YOUR-USERNAME/ARKAS.git
    cd ARKAS
    ```

2. **Install Dependencies**

    ```bash
    composer install --prefer-source
    npm install
    ```

3. **Environment Setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Database Setup**

    ```bash
    # Configure your database in .env
    php artisan migrate
    php artisan db:seed --class=AdminUserSeeder
    ```

5. **Start Development Server**
    ```bash
    php artisan serve
    npm run dev
    ```

## 🛠️ How Can I Contribute?

### 🐛 Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. When creating a bug report, include:

-   **Clear title** describing the issue
-   **Steps to reproduce** the behavior
-   **Expected behavior** vs **actual behavior**
-   **Screenshots** if applicable
-   **Environment details** (PHP version, OS, browser)
-   **Error messages** or log outputs

### ✨ Suggesting Features

Feature suggestions are welcome! Please provide:

-   **Clear description** of the proposed feature
-   **Use case** - why would this be useful?
-   **Implementation ideas** if you have any
-   **Mockups or examples** if applicable

### 🔧 Code Contributions

1. **Check existing issues** for something to work on
2. **Create an issue** for new features before starting work
3. **Fork the repository** and create a feature branch
4. **Write tests** for your changes
5. **Follow coding standards** (see Style Guidelines)
6. **Submit a pull request** with clear description

## 💻 Development Guidelines

### 🏗️ Architecture Principles

-   **Multi-tenant**: Each user's data must remain completely isolated
-   **Security First**: Always validate inputs and sanitize outputs
-   **Performance**: Consider database query optimization
-   **Maintainability**: Write clean, documented code

### 🗃️ Database Guidelines

-   Use proper foreign key constraints
-   Index frequently queried columns
-   Use transactions for critical operations
-   Follow Laravel migration best practices
-   Add `rollback` methods to migrations

### 🔒 Security Guidelines

-   **Never expose sensitive data** in logs or error messages
-   **Validate all inputs** with Laravel form requests
-   **Use proper authentication** and authorization
-   **Sanitize file uploads** with strict validation
-   **Use database transactions** for critical operations

## 📝 Style Guidelines

### PHP Code Style

We follow **PSR-12** coding standard with Laravel conventions:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function index(Request $request): View
    {
        // Use type hints
        // Use meaningful variable names
        // Add docblocks for complex methods
        return view('example.index');
    }
}
```

### Git Commit Messages

Use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Types:**

-   `feat`: New feature
-   `fix`: Bug fix
-   `docs`: Documentation changes
-   `style`: Code style changes (formatting, etc.)
-   `refactor`: Code refactoring
-   `test`: Adding or updating tests
-   `chore`: Maintenance tasks

**Examples:**

```
feat(bku): add Excel validation for import files
fix(auth): resolve email configuration error
docs(readme): update installation instructions
```

## 🔄 Pull Request Process

1. **Create Feature Branch**

    ```bash
    git checkout -b feature/your-feature-name
    ```

2. **Make Your Changes**

    - Write clean, tested code
    - Follow coding standards
    - Update documentation if needed

3. **Test Your Changes**

    ```bash
    php artisan test
    composer run-script cs-fix  # Code style fixing
    ```

4. **Commit Your Changes**

    ```bash
    git add .
    git commit -m "feat: add your feature description"
    ```

5. **Push and Create PR**
    ```bash
    git push origin feature/your-feature-name
    ```
    Then create a pull request on GitHub.

### PR Requirements

-   [ ] **Clear title** and description
-   [ ] **Tests pass** locally
-   [ ] **Code follows** style guidelines
-   [ ] **Documentation** updated if needed
-   [ ] **No merge conflicts** with main branch
-   [ ] **Screenshots** for UI changes

## 🏷️ Release Process

For maintainers releasing new versions:

1. **Update Version Numbers**

    - `config/app.php`
    - `.env.example`
    - `VERSION` file

2. **Update Documentation**

    - `CHANGELOG.md`
    - `RELEASE_NOTES.md`
    - `README.md` if needed

3. **Create Release**

    ```bash
    git tag -a v1.x.x -m "Release v1.x.x description"
    git push origin main
    git push origin v1.x.x
    ```

4. **GitHub Release**
    - Create release from tag
    - Add release notes
    - Upload any release assets

## ❓ Getting Help

-   **General Questions**: Open a [Discussion](https://github.com/kevindoni/ARKAS/discussions)
-   **Bug Reports**: Create an [Issue](https://github.com/kevindoni/ARKAS/issues)
-   **Security Issues**: Email kevindoni17@gmail.com privately

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

## 🙏 Thank You

Your contributions make ARKAS better for Indonesian educational institutions everywhere. Every bug report, feature suggestion, and code contribution helps improve financial transparency in schools across Indonesia.
