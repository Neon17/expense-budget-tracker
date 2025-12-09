# 💰 Expense & Budget Management System<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>



A comprehensive full-stack expense tracking and budget management application built with **Laravel 12** and **Filament 4**, featuring family accounts, real-time analytics, and a complete REST API.<p align="center">

<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>

![Filament](https://img.shields.io/badge/Filament-4-FDAE4B?style=flat-square)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>

![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)</p>



## ✨ Features## About Laravel



### 📊 Core FunctionalityLaravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- **Expense Tracking** - Log and categorize all expenses with notes and dates

- **Income Management** - Track multiple income sources- [Simple, fast routing engine](https://laravel.com/docs/routing).

- **Budget Planning** - Set monthly budgets with automatic alerts at 70%, 90%, and 100% thresholds- [Powerful dependency injection container](https://laravel.com/docs/container).

- **Category Management** - Custom categories with colors and icons- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.

- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).

### 👨‍👩‍👧‍👦 Family Accounts System- Database agnostic [schema migrations](https://laravel.com/docs/migrations).

- **Parent-Child Users** - Parents can create and manage child accounts- [Robust background job processing](https://laravel.com/docs/queues).

- **Scalable Permissions** - JSON-based permissions system (view, add, edit, delete expenses/incomes)- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

- **Family Statistics** - Combined expense and income tracking across all family members

- **Access Control** - Child users have restricted access based on assigned permissionsLaravel is accessible, powerful, and provides tools required for large, robust applications.



### 📈 Analytics & Reporting## Learning Laravel

- **Dashboard Widgets** - Real-time stats overview with trend indicators

- **Monthly Reports** - Detailed financial reports with Chart.js visualizationsLaravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

- **Category Breakdown** - Pie charts showing spending by category

- **Budget vs Actual** - Compare planned vs actual spendingIf you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

- **Superset Integration** - Advanced analytics via Apache Superset API

## Laravel Sponsors

### 🔔 Notifications & Alerts

- **Budget Alerts** - Automatic notifications when approaching budget limitsWe would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

- **Email + Database** - Dual-channel notifications

- **Filament Bell Icon** - In-app notification center### Premium Partners



### 🔌 REST API- **[Vehikl](https://vehikl.com)**

- **60+ Endpoints** - Complete API for mobile apps- **[Tighten Co.](https://tighten.co)**

- **Sanctum Authentication** - Secure token-based auth- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**

- **Standardized Responses** - Consistent JSON format- **[64 Robots](https://64robots.com)**

- **Postman Collection** - Ready-to-import API documentation- **[Curotec](https://www.curotec.com/services/technologies/laravel)**

- **[DevSquad](https://devsquad.com/hire-laravel-developers)**

## 🚀 Quick Start- **[Redberry](https://redberry.international/laravel-development)**

- **[Active Logic](https://activelogic.com)**

### Prerequisites

- PHP 8.2+## Contributing

- Composer

- Node.js & NPMThank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

- SQLite/MySQL

## Code of Conduct

### Installation

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

```bash

# Clone the repository## Security Vulnerabilities

git clone https://github.com/Neon17/expense-budget-tracker.git

cd expense-budget-trackerIf you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.



# Install dependencies## License

composer install

npm installThe Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed  # Optional: seed sample data

# Build assets
npm run build

# Start development server
php artisan serve
```

### Access Points
| URL | Description |
|-----|-------------|
| `http://localhost:8000` | Welcome Page |
| `http://localhost:8000/admin` | Filament Admin Panel |
| `http://localhost:8000/api` | REST API Base |

## 📁 Project Structure

```
├── app/
│   ├── Filament/
│   │   ├── Pages/           # MonthlyReport, FamilySettings
│   │   ├── Resources/       # CRUD Resources
│   │   └── Widgets/         # Dashboard Widgets
│   ├── Http/
│   │   ├── Controllers/Api/ # REST API Controllers
│   │   ├── Resources/       # API Resources
│   │   └── Traits/          # ApiResponse Trait
│   ├── Models/              # Eloquent Models
│   ├── Notifications/       # Budget Alerts
│   └── Observers/           # Expense Observer
├── database/migrations/
├── resources/views/
├── routes/
│   ├── api.php              # API Routes
│   └── web.php              # Web Routes
└── postman_collection.json  # API Documentation
```

## 🔑 API Overview

### Authentication
```http
POST /api/register    # Create account
POST /api/login       # Get auth token
POST /api/logout      # Revoke token
GET  /api/user        # Get current user
```

### Resources
```http
# Expenses
GET|POST /api/expenses
GET|PUT|DELETE /api/expenses/{id}

# Incomes
GET|POST /api/incomes
GET|PUT|DELETE /api/incomes/{id}

# Categories
GET|POST /api/categories
GET|PUT|DELETE /api/categories/{id}

# Budgets
GET|POST /api/budgets
GET /api/budget/current
PUT /api/budget
```

### Family Users (Parent-Child)
```http
PUT  /api/family/update-family          # Set family name
GET  /api/family/statistics             # Family stats
GET  /api/family/permissions            # Available permissions

GET|POST /api/family/children           # List/create children
GET|PUT|DELETE /api/family/children/{id}
PUT  /api/family/children/{id}/permissions
POST /api/family/children/{id}/reactivate
```

### Analytics
```http
GET /api/analytics/dashboard
GET /api/analytics/monthly-trend
GET /api/analytics/category-breakdown
GET /api/analytics/budget-vs-actual
GET /api/analytics/income-vs-expense
```

## 📊 Response Format

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": [...]
}
```

### Error Response
```json
{
    "success": false,
    "data": [],
    "errors": {"field": ["error message"]},
    "message": "Validation Error"
}
```

## 🏗️ Architecture

### API & Filament Side-by-Side
```
┌─────────────────┐     ┌─────────────────┐
│   Mobile App    │     │   Web Browser   │
└────────┬────────┘     └────────┬────────┘
         │                       │
         ▼                       ▼
┌─────────────────┐     ┌─────────────────┐
│  /api/* routes  │     │ /admin/* routes │
│   (Sanctum)     │     │   (Session)     │
└────────┬────────┘     └────────┬────────┘
         │                       │
         └──────────┬────────────┘
                    ▼
          ┌─────────────────┐
          │  Shared Layer   │
          │  - Models       │
          │  - Observers    │
          │  - Notifications│
          └─────────────────┘
```

### Observer Pattern
The `ExpenseObserver` automatically triggers budget checks when expenses are created or updated, sending notifications through both email and database channels.

## 🔐 Permissions System

Child users can have any combination of these permissions:
| Permission | Description |
|------------|-------------|
| `view_expenses` | View expense records |
| `add_expenses` | Create new expenses |
| `edit_expenses` | Edit own expenses |
| `delete_expenses` | Delete own expenses |
| `view_incomes` | View income records |
| `add_incomes` | Create new incomes |
| `view_budgets` | View budget information |
| `view_analytics` | Access analytics |
| `view_categories` | View categories |
| `manage_categories` | Create/edit categories |

## 🛠️ Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | Laravel 12, PHP 8.2+ |
| Admin Panel | Filament 4 |
| Database | SQLite (dev) / MySQL (prod) |
| API Auth | Laravel Sanctum |
| Charts | Chart.js |
| Analytics | Apache Superset (optional) |
| CSS | Tailwind CSS |

## 📝 Documentation

- **API Documentation**: Import `postman_collection.json` into Postman
- **Workflow Documentation**: See `workflow.md` for detailed architecture docs

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👤 Author

**Neon17**
- GitHub: [@Neon17](https://github.com/Neon17)

---

<p align="center">Made with ❤️ using Laravel & Filament</p>
