🚀 Project Workflow: Expense & Budget Management System with Filament
📋 Project Overview
A full-stack Laravel + Filament application with Superset integration for expense tracking, budget management, and financial analytics.

🛠️ Tech Stack
Backend: Laravel 10+ with Sanctum API

Admin Panel: Filament PHP v3

Database: MySQL / PostgreSQL

Analytics: Apache Superset (via REST API)

Frontend: Filament Dashboard + Blade Components

Auth: Laravel Sanctum (API Tokens)

📁 Project Structure
text
expense-budget-system/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Expense.php
│   │   ├── Income.php
│   │   ├── Category.php
│   │   └── Budget.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── ExpenseController.php
│   │   │   │   ├── IncomeController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── BudgetController.php
│   │   │   │   └── AnalyticsController.php
│   │   │   └── Filament/
│   │   │       └── Resources/
│   │   └── Resources/
│   │       └── Api/
│   ├── Services/
│   │   ├── BudgetService.php
│   │   └── AnalyticsService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── config/
├── routes/
│   ├── api.php
│   └── web.php
└── filament/
    └── Resources/
🔄 Workflow Steps
Phase 1: Setup & Core Models
Install Laravel + Filament

bash
composer create-project laravel/laravel expense-budget-system
composer require filament/filament:"^3.0" -W
composer require laravel/sanctum
Setup Database & Migrations

Create database

Run default migrations

Add currency field to users table

Create Core Models with Relationships

php
// User → Expense (One to Many)
// User → Income (One to Many)
// User → Category (One to Many)
// User → Budget (One to One, monthly)
Phase 2: Filament Admin Panel
Setup Filament Admin

bash
php artisan filament:install --panels
Create Filament Resources for:

Users

Expenses

Income

Categories

Budgets

Customize Resource Forms/Tables

Add filters (date range, category, search)

Add pagination

Color-coded categories

Budget progress indicators

Phase 3: API Development (Sanctum)
Setup Sanctum

bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
Create API Routes & Controllers

Authentication endpoints

CRUD endpoints for all models

Analytics endpoints for Superset

API Documentation Structure

Use Postman/Swagger for documentation

Create API resource classes

Phase 4: Dashboard & Analytics
Create Main Dashboard

Summary cards (monthly expense, income, savings)

Quick insights widgets

Small embedded charts

Superset Integration

Create API endpoints for datasets

Configure Superset to consume APIs

Embed Superset dashboards via iframe/API

Phase 5: Additional Features
Multi-user support

Dark mode toggle

Notification system

Budget alerts (70%, 90%, 100%)

Monthly reports

📡 API Endpoints Specification
Authentication (Sanctum)
http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password",
    "currency": "NPR"
}
http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password"
}
Response (Both):

json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "currency": "NPR"
    },
    "token": "API_TOKEN_HERE"
}
Expenses API
http
GET /api/expenses
Headers: Authorization: Bearer {token}
Query: ?month=2024-01&category=food&search=restaurant
http
POST /api/expenses
Headers: Authorization: Bearer {token}
Content-Type: application/json

{
    "amount": 1500.00,
    "category_id": 3,
    "date": "2024-01-15",
    "note": "Lunch with team",
    "currency": "NPR"
}
Response:

json
{
    "id": 1,
    "user_id": 1,
    "amount": 1500.00,
    "category": {
        "id": 3,
        "name": "Food",
        "color": "#FF5733"
    },
    "date": "2024-01-15",
    "note": "Lunch with team",
    "created_at": "2024-01-15T10:30:00Z"
}
Income API
http
POST /api/incomes
Headers: Authorization: Bearer {token}

{
    "amount": 50000.00,
    "source": "Freelance Project",
    "date": "2024-01-05",
    "currency": "NPR"
}
Categories API
http
GET /api/categories
Headers: Authorization: Bearer {token}
http
POST /api/categories
Headers: Authorization: Bearer {token}

{
    "name": "Entertainment",
    "color": "#9C27B0",
    "icon": "film"
}
Budget API
http
GET /api/budget/current
Headers: Authorization: Bearer {token}
http
PUT /api/budget
Headers: Authorization: Bearer {token}

{
    "monthly_limit": 30000.00,
    "currency": "NPR"
}
Response:

json
{
    "monthly_limit": 30000.00,
    "spent_this_month": 18500.00,
    "remaining": 11500.00,
    "usage_percentage": 61.67,
    "status": "safe" // safe, warning, exceeded
}
Analytics API (For Superset)
http
GET /api/analytics/monthly-trend?year=2024
Headers: Authorization: Bearer {token}
http
GET /api/analytics/category-breakdown?month=2024-01
Headers: Authorization: Bearer {token}
http
GET /api/analytics/budget-vs-actual?year=2024
Headers: Authorization: Bearer {token}
🎯 Filament-Specific Implementation
Creating a Filament Resource for Expenses
bash
php artisan make:filament-resource Expense
Example Resource Configuration:

php
protected static string $resource = ExpenseResource::class;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Select::make('category_id')
                ->relationship('category', 'name')
                ->required(),
            TextInput::make('amount')
                ->numeric()
                ->required(),
            DatePicker::make('date')
                ->default(now()),
            Textarea::make('note')
                ->maxLength(500),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('category.name')
                ->color(fn ($record) => $record->category->color),
            TextColumn::make('amount')
                ->money('NPR'),
            TextColumn::make('date')
                ->date(),
        ])
        ->filters([
            SelectFilter::make('category'),
            DateRangeFilter::make('date'),
        ])
        ->actions([
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}
Dashboard Widgets
php
// In Filament PanelProvider
protected function getDashboardWidgets(): array
{
    return [
        Dashboard\Widgets\StatsOverviewWidget::class,
        Dashboard\Widgets\ExpenseChartWidget::class,
        Dashboard\Widgets\BudgetProgressWidget::class,
    ];
}
📊 Superset Integration Workflow
Create API endpoints for datasets

Monthly expense trend

Category breakdown

Income vs Expense comparison

Configure Superset

Add database connection to your API

Create datasets from API endpoints

Build dashboards with charts

Embed in Filament

php
protected function getHeaderWidgets(): array
{
    return [
        Dashboard\Widgets\EmbedWidget::make()
            ->url('https://superset.example.com/dashboard/1')
            ->height('600px'),
    ];
}
📅 Timeline & Milestones
Week	Tasks
1	Project setup, Models, Migrations, Filament install
2	Filament Resources (CRUD), Basic UI
3	API Development (Sanctum), Authentication
4	Dashboard widgets, Budget tracking
5	Superset integration, Analytics APIs
6	Additional features (dark mode, notifications)
7	Testing, Polish, Documentation
8	Deployment & Final Review
✅ Deliverables Checklist
Working Laravel + Filament application

Complete REST API with Sanctum auth

All Filament resources (CRUD operations)

Dashboard with summary cards and widgets

Budget tracking with visual indicators

Superset dashboards connected to real data

API documentation (Postman/Swagger)

Responsive design

Git repository with proper commits

Deployment ready

🐛 Testing Strategy
API Testing: Postman collection for all endpoints

Filament Testing: Test CRUD operations in admin panel

Budget Logic: Test monthly reset and tracking

Multi-user: Test data isolation between users

Superset: Verify data pipelines and charts

🚨 Notes for Development
Currency Handling: Store all amounts in base currency (NPR), convert if needed

Monthly Reset: Use Laravel Scheduler to reset budgets on 1st of each month

Data Privacy: Ensure users only see their own data

Performance: Index database fields for filtering (date, category_id, user_id)

Error Handling: Consistent API error responses

📞 Support & Collaboration
Use GitHub Issues for bug tracking

Create feature branches for new developments

Weekly sync meetings to review progress

Document all API changes in /docs/api folder