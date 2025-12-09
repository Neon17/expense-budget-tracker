# 🚀 Expense & Budget Management System# 🚀 Expense & Budget Management System



## 📋 Project Overview## 📋 Project Overview

A full-stack Laravel 12 + Filament 4 application with Superset integration for expense tracking, budget management, family accounts, and financial analytics.A full-stack Laravel 12 + Filament 4 application with Superset integration for expense tracking, budget management, family accounts, and financial analytics.



------



## 🛠️ Tech Stack## 🛠️ Tech Stack

- **Backend**: Laravel 12 with PHP 8.5- **Backend**: Laravel 12 with PHP 8.5

- **Admin Panel**: Filament PHP v4- **Admin Panel**: Filament PHP v4

- **Database**: SQLite (dev) / MySQL (production)- **Database**: SQLite (dev) / MySQL (production)

- **Analytics**: Apache Superset (via REST API) + Chart.js- **Analytics**: Apache Superset (via REST API) + Chart.js

- **Frontend**: Filament Dashboard + Blade Components- **Frontend**: Filament Dashboard + Blade Components

- **Auth**: Laravel Sanctum (API Tokens)- **Auth**: Laravel Sanctum (API Tokens)

- **Default Currency**: NPR (Nepalese Rupee)- **Default Currency**: NPR (Nepalese Rupee)



------



## 🏗️ Architecture Overview## ✅ Completed Features



### API & Filament Side-by-Side### 1. Core Models & Migrations

This application runs **API routes** and **Filament Admin Panel** simultaneously:- ✅ User (with currency preference)

- ✅ Category (expense/income types with icons & colors)

| Component | URL Path | Purpose |- ✅ Expense (with category, date, amount, currency)

|-----------|----------|---------|- ✅ Income (with category, source, date, amount)

| **API** | `/api/*` | REST API for mobile apps, external integrations |- ✅ Budget (monthly limits with alerts)

| **Filament Admin** | `/admin/*` | Web-based admin panel for management |- ✅ FamilyGroup (shared accounts with invite codes)

| **Welcome Page** | `/` | Public landing page |

### 2. Filament 4 Admin Panel

**How they coexist:**- ✅ CategoryResource - CRUD with icon picker & color selection

1. **Routes**: API routes defined in `routes/api.php`, Web routes in `routes/web.php`- ✅ ExpenseResource - CRUD with category filtering

2. **Controllers**: API controllers in `App\Http\Controllers\Api\`, Filament uses Resources- ✅ IncomeResource - CRUD with recurring income support

3. **Authentication**: API uses Sanctum tokens, Filament uses session-based auth- ✅ BudgetResource - Monthly budget management

4. **Both share**: Models, Observers, Notifications, Services- ✅ FamilyGroupResource - Family/shared account management



### Request Flow Diagram### 3. Dashboard Widgets

```- ✅ StatsOverviewWidget - Quick stats (expenses, income, savings)

┌─────────────────┐     ┌─────────────────┐- ✅ ExpenseChartWidget - Monthly expense trends

│   Mobile App    │     │   Web Browser   │- ✅ CategoryBreakdownWidget - Pie chart by category

└────────┬────────┘     └────────┬────────┘- ✅ BudgetProgressWidget - Budget utilization progress

         │                       │- ✅ RecentExpensesWidget - Latest transactions

         ▼                       ▼- ✅ SupersetDashboardWidget - Advanced analytics with Chart.js

┌─────────────────┐     ┌─────────────────┐

│   /api/* routes │     │  /admin/* routes│### 4. API Endpoints (Mobile App Ready)

│   (Sanctum)     │     │  (Session)      │

└────────┬────────┘     └────────┬────────┘#### Authentication

         │                       │```

         ▼                       ▼POST   /api/register          - Register new user

┌─────────────────┐     ┌─────────────────┐POST   /api/login             - Login & get token

│ API Controllers │     │Filament Resources│POST   /api/logout            - Logout (revoke token)

└────────┬────────┘     └────────┬────────┘GET    /api/user              - Get authenticated user

         │                       │PUT    /api/user/profile      - Update user profile

         └──────────┬────────────┘```

                    ▼

          ┌─────────────────┐#### Expenses

          │  Shared Models  │```

          │  Observers      │GET    /api/expenses          - List expenses (paginated)

          │  Notifications  │POST   /api/expenses          - Create expense

          │  Services       │GET    /api/expenses/{id}     - Get single expense

          └─────────────────┘PUT    /api/expenses/{id}     - Update expense

```DELETE /api/expenses/{id}     - Delete expense

GET    /api/expenses-summary  - Monthly expense summary

---```



## 🔔 Observers & Notifications System#### Incomes

```

### How Observers Work with FilamentGET    /api/incomes           - List incomes (paginated)

POST   /api/incomes           - Create income

**Location**: `App\Observers\ExpenseObserver`GET    /api/incomes/{id}      - Get single income

PUT    /api/incomes/{id}      - Update income

**Registration**: `App\Providers\AppServiceProvider.php`DELETE /api/incomes/{id}      - Delete income

```phpGET    /api/incomes-summary   - Monthly income summary

public function boot(): void```

{

    Expense::observe(ExpenseObserver::class);#### Categories

}```

```GET    /api/categories        - List categories

POST   /api/categories        - Create category

**Observer automatically triggers when:**GET    /api/categories/{id}   - Get single category

- ✅ Creating expense via API (`POST /api/expenses`)PUT    /api/categories/{id}   - Update category

- ✅ Creating expense via Filament Admin PanelDELETE /api/categories/{id}   - Delete category

- ✅ Updating expense via API (`PUT /api/expenses/{id}`)```

- ✅ Updating expense via Filament Admin Panel

- ✅ Any Eloquent model operation (create, update, delete)#### Budgets

```

**ExpenseObserver Flow:**GET    /api/budgets           - List budgets

```POST   /api/budgets           - Create budget

User creates/updates expenseGET    /api/budgets/{id}      - Get single budget

         │PUT    /api/budgets/{id}      - Update budget

         ▼DELETE /api/budgets/{id}      - Delete budget

┌─────────────────────────┐GET    /api/budget/current    - Get current month budget

│   ExpenseObserver       │PUT    /api/budget            - Update current budget

│   created() / updated() │```

└───────────┬─────────────┘

            │#### Analytics

            ▼```

┌─────────────────────────┐GET    /api/analytics/dashboard          - Dashboard summary

│  checkBudgetAlert()     │GET    /api/analytics/monthly-trend      - 12-month trend

│  - Get user's budget    │GET    /api/analytics/category-breakdown - Category breakdown

│  - Calculate percentage │GET    /api/analytics/budget-vs-actual   - Budget comparison

│  - Check thresholds     │GET    /api/analytics/income-vs-expense  - Income vs expense

└───────────┬─────────────┘GET    /api/analytics/weekly-stats       - Weekly statistics

            │GET    /api/analytics/category-stats     - Category-level stats

     ┌──────┴──────┐GET    /api/analytics/savings-rate       - Savings rate analysis

     │ Threshold?  │```

     └──────┬──────┘

            │#### Superset Integration APIs

    ┌───────┼───────┐```

    ▼       ▼       ▼GET    /api/analytics/superset/expenses          - Flat expense dataset

  70%     90%    100%+GET    /api/analytics/superset/incomes           - Flat income dataset

warning critical exceededGET    /api/analytics/superset/monthly-aggregate - Monthly aggregates

    │       │       │```

    └───────┴───────┘

            │#### Family Groups (Shared Accounts)

            ▼```

┌─────────────────────────┐GET    /api/family-groups                        - List my family groups

│ BudgetAlertNotification │POST   /api/family-groups                        - Create family group

│ - via mail              │POST   /api/family-groups/join                   - Join with invite code

│ - via database (Filament)│GET    /api/family-groups/{id}                   - Get family group details

└─────────────────────────┘PUT    /api/family-groups/{id}                   - Update family group

```DELETE /api/family-groups/{id}                   - Delete family group

POST   /api/family-groups/{id}/leave             - Leave family group

### Notifications in FilamentPOST   /api/family-groups/{id}/regenerate-code   - Regenerate invite code

POST   /api/family-groups/{id}/transfer-ownership - Transfer ownership

**Location**: `App\Notifications\BudgetAlertNotification`GET    /api/family-groups/{id}/statistics        - Family group stats

DELETE /api/family-groups/{id}/members/{userId}  - Remove member

**Filament Integration**: `AdminPanelProvider.php`PUT    /api/family-groups/{id}/members/{userId}/role - Update member role

```php```

->databaseNotifications()

```### 5. Notifications & Alerts

- ✅ BudgetAlertNotification - Email/database alerts

**Notification Channels:**- ✅ ExpenseObserver - Auto-check budget on expense creation

1. **Mail**: Sends email to user- ✅ Threshold alerts at 80%, 90%, and 100% budget usage

2. **Database**: Stores in `notifications` table, displayed in Filament bell icon

### 6. Filament Pages

**Filament-specific format** in `toArray()`:- ✅ MonthlyReport - Detailed monthly financial report with charts

```php

return [### 7. Welcome Page

    'format' => 'filament',           // Tells Filament to render this- ✅ Modern landing page with features, testimonials, pricing

    'title' => 'Budget Warning',      // Notification title

    'body' => 'You have used 70%...', // Notification body### 8. Dark Mode

    'icon' => 'heroicon-o-bell-alert', // Heroicon for display- ✅ Full dark mode support in Filament and welcome page

    'iconColor' => 'warning',         // Filament color scheme

    // ... additional data---

];

```## 🚀 Running the Application



**Alert Thresholds:**### Prerequisites

| Percentage | Alert Level | Icon | Color |- PHP 8.2+

|------------|-------------|------|-------|- Composer

| 70%+ | Warning | bell-alert | warning (yellow) |- Node.js & NPM (for assets)

| 90%+ | Critical | exclamation-triangle | danger (red) |

| 100%+ | Exceeded | exclamation-circle | danger (red) |### Setup Commands

```bash

---# Install dependencies

composer install

## 👨‍👩‍👧‍👦 Family Accounts System (Parent-Child)npm install



### Overview# Setup environment

Parent users can create and manage child accounts within their family. This is separate from Family Groups (which use invite codes).cp .env.example .env

php artisan key:generate

### User Roles

| Role | Description | Filament Access |# Database

|------|-------------|-----------------|php artisan migrate

| `user` | Regular user (default) | ✅ Yes |php artisan db:seed  # Optional: seed sample data

| `parent` | User with child accounts | ✅ Yes |

| `child` | Account created by parent | ❌ No |# Build assets

npm run build

### Database Schema (users table additions)

```sql# Start server

parent_id    - FK to users.id (nullable)php artisan serve

family_name  - Family display name```

role         - 'user', 'parent', 'child'

permissions  - JSON array of permissions### Access

is_active    - Boolean for soft-disable- **Welcome Page**: http://localhost:8000

```- **Admin Panel**: http://localhost:8000/admin

- **API Base**: http://localhost:8000/api

### Permissions System (Scalable)

Permissions are stored as JSON array, making it easy to add new permissions:---



**Available Permissions:**## 📁 Project Structure

```php```

'view_expenses'     - View expensesexpense-budget-system/

'add_expenses'      - Add expenses├── app/

'edit_expenses'     - Edit own expenses│   ├── Filament/

'delete_expenses'   - Delete own expenses│   │   ├── Pages/

'view_incomes'      - View incomes│   │   │   └── MonthlyReport.php

'add_incomes'       - Add incomes│   │   ├── Resources/

'edit_incomes'      - Edit own incomes│   │   │   ├── CategoryResource.php

'delete_incomes'    - Delete own incomes│   │   │   ├── ExpenseResource.php

'view_categories'   - View categories│   │   │   ├── IncomeResource.php

'manage_categories' - Manage categories│   │   │   ├── BudgetResource.php

'view_budgets'      - View budgets│   │   │   └── FamilyGroups/

'view_analytics'    - View analytics│   │   │       └── FamilyGroupResource.php

'view_family_data'  - View family members data│   │   └── Widgets/

```│   │       ├── StatsOverviewWidget.php

│   │       ├── ExpenseChartWidget.php

**Default child permissions:** `['view_expenses', 'add_expenses']`│   │       ├── CategoryBreakdownWidget.php

│   │       ├── BudgetProgressWidget.php

### Family User API Endpoints│   │       ├── RecentExpensesWidget.php

│   │       └── SupersetDashboardWidget.php

#### Set/Update Family Name│   ├── Http/Controllers/Api/

```http│   │   ├── AuthController.php

PUT /api/family/update-family│   │   ├── ExpenseController.php

Authorization: Bearer {token}│   │   ├── IncomeController.php

Content-Type: application/json│   │   ├── CategoryController.php

│   │   ├── BudgetController.php

{│   │   ├── AnalyticsController.php

    "family_name": "Smith Family"│   │   └── FamilyGroupController.php

}│   ├── Models/

```│   │   ├── User.php

│   │   ├── Category.php

#### Create Child Account│   │   ├── Expense.php

```http│   │   ├── Income.php

POST /api/family/children│   │   ├── Budget.php

Authorization: Bearer {token}│   │   └── FamilyGroup.php

Content-Type: application/json│   ├── Notifications/

│   │   └── BudgetAlertNotification.php

{│   ├── Observers/

    "name": "John Jr",│   │   └── ExpenseObserver.php

    "email": "jr@example.com",│   └── Services/

    "password": "password123",│       └── AnalyticsService.php

    "permissions": ["view_expenses", "add_expenses", "view_budgets"]├── database/migrations/

}├── resources/views/

```│   ├── welcome-new.blade.php

│   └── filament/

#### List Children│       ├── pages/monthly-report.blade.php

```http│       └── widgets/superset-dashboard.blade.php

GET /api/family/children└── routes/

Authorization: Bearer {token}    ├── api.php

```    └── web.php

```

#### Update Child└── filament/

```http    └── Resources/

PUT /api/family/children/{childId}🔄 Workflow Steps

Authorization: Bearer {token}Phase 1: Setup & Core Models

Content-Type: application/jsonInstall Laravel + Filament



{bash

    "name": "Updated Name",composer create-project laravel/laravel expense-budget-system

    "permissions": ["view_expenses", "add_expenses"],composer require filament/filament:"^3.0" -W

    "is_active": truecomposer require laravel/sanctum

}Setup Database & Migrations

```

Create database

#### Update Child Permissions

```httpRun default migrations

PUT /api/family/children/{childId}/permissions

Authorization: Bearer {token}Add currency field to users table

Content-Type: application/json

Create Core Models with Relationships

{

    "permissions": ["view_expenses", "add_expenses", "view_incomes"]php

}// User → Expense (One to Many)

```// User → Income (One to Many)

// User → Category (One to Many)

#### Deactivate Child (Soft Delete)// User → Budget (One to One, monthly)

```httpPhase 2: Filament Admin Panel

DELETE /api/family/children/{childId}Setup Filament Admin

Authorization: Bearer {token}

```bash

php artisan filament:install --panels

#### Permanently Delete ChildCreate Filament Resources for:

```http

DELETE /api/family/children/{childId}/forceUsers

Authorization: Bearer {token}

```Expenses



#### Reactivate ChildIncome

```http

POST /api/family/children/{childId}/reactivateCategories

Authorization: Bearer {token}

```Budgets



#### Get Family StatisticsCustomize Resource Forms/Tables

```http

GET /api/family/statisticsAdd filters (date range, category, search)

Authorization: Bearer {token}

```Add pagination



#### Get Available PermissionsColor-coded categories

```http

GET /api/family/permissionsBudget progress indicators

Authorization: Bearer {token}

```Phase 3: API Development (Sanctum)

Setup Sanctum

---

bash

## 📡 API Response Formatphp artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

php artisan migrate

### Success ResponseCreate API Routes & Controllers

```json

{Authentication endpoints

    "success": true,

    "message": "Operation successful",CRUD endpoints for all models

    "data": [

        { "id": 1, "name": "..." }Analytics endpoints for Superset

    ]

}API Documentation Structure

```

Use Postman/Swagger for documentation

### Validation Error Response

```jsonCreate API resource classes

{

    "success": false,Phase 4: Dashboard & Analytics

    "data": [],Create Main Dashboard

    "errors": {

        "name": ["The name field is required."],Summary cards (monthly expense, income, savings)

        "email": ["The email has already been taken."]

    },Quick insights widgets

    "message": "Validation Error"

}Small embedded charts

```

Superset Integration

### General Error Response

```jsonCreate API endpoints for datasets

{

    "success": false,Configure Superset to consume APIs

    "data": [],

    "errors": [],Embed Superset dashboards via iframe/API

    "message": "Resource not found"

}Phase 5: Additional Features

```Multi-user support



---Dark mode toggle



## ✅ Completed FeaturesNotification system



### 1. Core Models & MigrationsBudget alerts (70%, 90%, 100%)

- ✅ User (with currency preference, parent-child relationships)

- ✅ Category (expense/income types with icons & colors)Monthly reports

- ✅ Expense (with category, date, amount, currency)

- ✅ Income (with category, source, date, amount)📡 API Endpoints Specification

- ✅ Budget (monthly limits with alerts)Authentication (Sanctum)

- ✅ FamilyGroup (shared accounts with invite codes)http

POST /api/register

### 2. Filament 4 Admin PanelContent-Type: application/json

- ✅ CategoryResource - CRUD with icon picker & color selection

- ✅ ExpenseResource - CRUD with category filtering{

- ✅ IncomeResource - CRUD with recurring income support    "name": "John Doe",

- ✅ BudgetResource - Monthly budget management    "email": "john@example.com",

- ✅ FamilyGroupResource - Family/shared account management    "password": "password",

    "password_confirmation": "password",

### 3. Dashboard Widgets    "currency": "NPR"

- ✅ StatsOverviewWidget - Quick stats (expenses, income, savings)}

- ✅ ExpenseChartWidget - Monthly expense trendshttp

- ✅ CategoryBreakdownWidget - Pie chart by categoryPOST /api/login

- ✅ BudgetProgressWidget - Budget utilization progressContent-Type: application/json

- ✅ RecentExpensesWidget - Latest transactions

- ✅ SupersetDashboardWidget - Advanced analytics with Chart.js{

    "email": "john@example.com",

### 4. API Endpoints (Mobile App Ready)    "password": "password"

}

#### AuthenticationResponse (Both):

```

POST   /api/register          - Register new userjson

POST   /api/login             - Login & get token{

POST   /api/logout            - Logout (revoke token)    "user": {

GET    /api/user              - Get authenticated user        "id": 1,

PUT    /api/user/profile      - Update user profile        "name": "John Doe",

```        "email": "john@example.com",

        "currency": "NPR"

#### Expenses    },

```    "token": "API_TOKEN_HERE"

GET    /api/expenses          - List expenses (paginated)}

POST   /api/expenses          - Create expenseExpenses API

GET    /api/expenses/{id}     - Get single expensehttp

PUT    /api/expenses/{id}     - Update expenseGET /api/expenses

DELETE /api/expenses/{id}     - Delete expenseHeaders: Authorization: Bearer {token}

GET    /api/expenses-summary  - Monthly expense summaryQuery: ?month=2024-01&category=food&search=restaurant

```http

POST /api/expenses

#### IncomesHeaders: Authorization: Bearer {token}

```Content-Type: application/json

GET    /api/incomes           - List incomes (paginated)

POST   /api/incomes           - Create income{

GET    /api/incomes/{id}      - Get single income    "amount": 1500.00,

PUT    /api/incomes/{id}      - Update income    "category_id": 3,

DELETE /api/incomes/{id}      - Delete income    "date": "2024-01-15",

GET    /api/incomes-summary   - Monthly income summary    "note": "Lunch with team",

```    "currency": "NPR"

}

#### CategoriesResponse:

```

GET    /api/categories        - List categoriesjson

POST   /api/categories        - Create category{

GET    /api/categories/{id}   - Get single category    "id": 1,

PUT    /api/categories/{id}   - Update category    "user_id": 1,

DELETE /api/categories/{id}   - Delete category    "amount": 1500.00,

```    "category": {

        "id": 3,

#### Budgets        "name": "Food",

```        "color": "#FF5733"

GET    /api/budgets           - List budgets    },

POST   /api/budgets           - Create budget    "date": "2024-01-15",

GET    /api/budgets/{id}      - Get single budget    "note": "Lunch with team",

PUT    /api/budgets/{id}      - Update budget    "created_at": "2024-01-15T10:30:00Z"

DELETE /api/budgets/{id}      - Delete budget}

GET    /api/budget/current    - Get current month budgetIncome API

PUT    /api/budget            - Update current budgethttp

```POST /api/incomes

Headers: Authorization: Bearer {token}

#### Analytics

```{

GET    /api/analytics/dashboard          - Dashboard summary    "amount": 50000.00,

GET    /api/analytics/monthly-trend      - 12-month trend    "source": "Freelance Project",

GET    /api/analytics/category-breakdown - Category breakdown    "date": "2024-01-05",

GET    /api/analytics/budget-vs-actual   - Budget comparison    "currency": "NPR"

GET    /api/analytics/income-vs-expense  - Income vs expense}

GET    /api/analytics/weekly-stats       - Weekly statisticsCategories API

GET    /api/analytics/category-stats     - Category-level statshttp

GET    /api/analytics/savings-rate       - Savings rate analysisGET /api/categories

```Headers: Authorization: Bearer {token}

http

#### Superset Integration APIsPOST /api/categories

```Headers: Authorization: Bearer {token}

GET    /api/analytics/superset/expenses          - Flat expense dataset

GET    /api/analytics/superset/incomes           - Flat income dataset{

GET    /api/analytics/superset/monthly-aggregate - Monthly aggregates    "name": "Entertainment",

```    "color": "#9C27B0",

    "icon": "film"

#### Family Groups (Shared Accounts with Invite Codes)}

```Budget API

GET    /api/family-groups                        - List my family groupshttp

POST   /api/family-groups                        - Create family groupGET /api/budget/current

POST   /api/family-groups/join                   - Join with invite codeHeaders: Authorization: Bearer {token}

GET    /api/family-groups/{id}                   - Get family group detailshttp

PUT    /api/family-groups/{id}                   - Update family groupPUT /api/budget

DELETE /api/family-groups/{id}                   - Delete family groupHeaders: Authorization: Bearer {token}

POST   /api/family-groups/{id}/leave             - Leave family group

POST   /api/family-groups/{id}/regenerate-code   - Regenerate invite code{

POST   /api/family-groups/{id}/transfer-ownership - Transfer ownership    "monthly_limit": 30000.00,

GET    /api/family-groups/{id}/statistics        - Family group stats    "currency": "NPR"

DELETE /api/family-groups/{id}/members/{userId}  - Remove member}

PUT    /api/family-groups/{id}/members/{userId}/role - Update member roleResponse:

```

json

#### Family Users (Parent-Child Accounts){

```    "monthly_limit": 30000.00,

GET    /api/family/permissions                   - List available permissions    "spent_this_month": 18500.00,

PUT    /api/family/update-family                 - Set/update family name    "remaining": 11500.00,

GET    /api/family/statistics                    - Get family statistics    "usage_percentage": 61.67,

GET    /api/family/children                      - List child accounts    "status": "safe" // safe, warning, exceeded

POST   /api/family/children                      - Create child account}

GET    /api/family/children/{id}                 - Get child detailsAnalytics API (For Superset)

PUT    /api/family/children/{id}                 - Update child accounthttp

DELETE /api/family/children/{id}                 - Deactivate child (soft)GET /api/analytics/monthly-trend?year=2024

DELETE /api/family/children/{id}/force           - Delete child (permanent)Headers: Authorization: Bearer {token}

POST   /api/family/children/{id}/reactivate      - Reactivate childhttp

PUT    /api/family/children/{id}/permissions     - Update child permissionsGET /api/analytics/category-breakdown?month=2024-01

```Headers: Authorization: Bearer {token}

http

### 5. Notifications & AlertsGET /api/analytics/budget-vs-actual?year=2024

- ✅ BudgetAlertNotification - Email/database alertsHeaders: Authorization: Bearer {token}

- ✅ ExpenseObserver - Auto-check budget on expense creation🎯 Filament-Specific Implementation

- ✅ Threshold alerts at 70%, 90%, and 100% budget usageCreating a Filament Resource for Expenses

- ✅ Filament database notifications with iconsbash

php artisan make:filament-resource Expense

### 6. Filament PagesExample Resource Configuration:

- ✅ MonthlyReport - Detailed monthly financial report with charts

php

### 7. Welcome Pageprotected static string $resource = ExpenseResource::class;

- ✅ Modern landing page with features, testimonials, pricing

public static function form(Form $form): Form

### 8. Dark Mode{

- ✅ Full dark mode support in Filament and welcome page    return $form

        ->schema([

---            Select::make('category_id')

                ->relationship('category', 'name')

## 🚀 Running the Application                ->required(),

            TextInput::make('amount')

### Prerequisites                ->numeric()

- PHP 8.2+                ->required(),

- Composer            DatePicker::make('date')

- Node.js & NPM (for assets)                ->default(now()),

            Textarea::make('note')

### Setup Commands                ->maxLength(500),

```bash        ]);

# Install dependencies}

composer install

npm installpublic static function table(Table $table): Table

{

# Setup environment    return $table

cp .env.example .env        ->columns([

php artisan key:generate            TextColumn::make('category.name')

                ->color(fn ($record) => $record->category->color),

# Database            TextColumn::make('amount')

php artisan migrate                ->money('NPR'),

php artisan db:seed  # Optional: seed sample data            TextColumn::make('date')

                ->date(),

# Build assets        ])

npm run build        ->filters([

            SelectFilter::make('category'),

# Start server            DateRangeFilter::make('date'),

php artisan serve        ])

```        ->actions([

            EditAction::make(),

### Access            DeleteAction::make(),

- **Welcome Page**: http://localhost:8000        ])

- **Admin Panel**: http://localhost:8000/admin        ->bulkActions([

- **API Base**: http://localhost:8000/api            BulkActionGroup::make([

                DeleteBulkAction::make(),

---            ]),

        ]);

## 📁 Project Structure}

```Dashboard Widgets

expense-budget-system/php

├── app/// In Filament PanelProvider

│   ├── Filament/protected function getDashboardWidgets(): array

│   │   ├── Pages/{

│   │   │   └── MonthlyReport.php    return [

│   │   ├── Resources/        Dashboard\Widgets\StatsOverviewWidget::class,

│   │   │   ├── CategoryResource.php        Dashboard\Widgets\ExpenseChartWidget::class,

│   │   │   ├── ExpenseResource.php        Dashboard\Widgets\BudgetProgressWidget::class,

│   │   │   ├── IncomeResource.php    ];

│   │   │   ├── BudgetResource.php}

│   │   │   └── FamilyGroups/📊 Superset Integration Workflow

│   │   │       └── FamilyGroupResource.phpCreate API endpoints for datasets

│   │   └── Widgets/

│   │       ├── StatsOverviewWidget.phpMonthly expense trend

│   │       ├── ExpenseChartWidget.php

│   │       ├── CategoryBreakdownWidget.phpCategory breakdown

│   │       ├── BudgetProgressWidget.php

│   │       ├── RecentExpensesWidget.phpIncome vs Expense comparison

│   │       └── SupersetDashboardWidget.php

│   ├── Http/Configure Superset

│   │   ├── Controllers/Api/

│   │   │   ├── AuthController.phpAdd database connection to your API

│   │   │   ├── ExpenseController.php

│   │   │   ├── IncomeController.phpCreate datasets from API endpoints

│   │   │   ├── CategoryController.php

│   │   │   ├── BudgetController.phpBuild dashboards with charts

│   │   │   ├── AnalyticsController.php

│   │   │   ├── FamilyGroupController.phpEmbed in Filament

│   │   │   └── FamilyUserController.php

│   │   ├── Resources/php

│   │   │   ├── UserResource.phpprotected function getHeaderWidgets(): array

│   │   │   ├── UserCollection.php{

│   │   │   ├── ExpenseResource.php    return [

│   │   │   ├── IncomeResource.php        Dashboard\Widgets\EmbedWidget::make()

│   │   │   ├── CategoryResource.php            ->url('https://superset.example.com/dashboard/1')

│   │   │   └── BudgetResource.php            ->height('600px'),

│   │   └── Traits/    ];

│   │       └── ApiResponse.php}

│   ├── Models/📅 Timeline & Milestones

│   │   ├── User.phpWeek	Tasks

│   │   ├── Category.php1	Project setup, Models, Migrations, Filament install

│   │   ├── Expense.php2	Filament Resources (CRUD), Basic UI

│   │   ├── Income.php3	API Development (Sanctum), Authentication

│   │   ├── Budget.php4	Dashboard widgets, Budget tracking

│   │   └── FamilyGroup.php5	Superset integration, Analytics APIs

│   ├── Notifications/6	Additional features (dark mode, notifications)

│   │   └── BudgetAlertNotification.php7	Testing, Polish, Documentation

│   ├── Observers/8	Deployment & Final Review

│   │   └── ExpenseObserver.php✅ Deliverables Checklist

│   ├── Providers/Working Laravel + Filament application

│   │   ├── AppServiceProvider.php      # Observer registration

│   │   └── Filament/Complete REST API with Sanctum auth

│   │       └── AdminPanelProvider.php  # Filament config

│   └── Services/All Filament resources (CRUD operations)

│       └── AnalyticsService.php

├── database/migrations/Dashboard with summary cards and widgets

├── resources/views/

│   ├── welcome-new.blade.phpBudget tracking with visual indicators

│   └── filament/

│       ├── pages/monthly-report.blade.phpSuperset dashboards connected to real data

│       └── widgets/superset-dashboard.blade.php

└── routes/API documentation (Postman/Swagger)

    ├── api.php    # API routes

    └── web.php    # Web routesResponsive design

```

Git repository with proper commits

---

Deployment ready

## 🔧 Key Implementation Details

🐛 Testing Strategy

### Observer Registration (AppServiceProvider)API Testing: Postman collection for all endpoints

```php

// app/Providers/AppServiceProvider.phpFilament Testing: Test CRUD operations in admin panel

public function boot(): void

{Budget Logic: Test monthly reset and tracking

    Expense::observe(ExpenseObserver::class);

}Multi-user: Test data isolation between users

```

Superset: Verify data pipelines and charts

### Filament Notifications (AdminPanelProvider)

```php🚨 Notes for Development

// app/Providers/Filament/AdminPanelProvider.phpCurrency Handling: Store all amounts in base currency (NPR), convert if needed

return $panel

    // ...Monthly Reset: Use Laravel Scheduler to reset budgets on 1st of each month

    ->databaseNotifications();  // Enables bell icon notifications

```Data Privacy: Ensure users only see their own data



### API Response Trait UsagePerformance: Index database fields for filtering (date, category_id, user_id)

```php

// In any API controllerError Handling: Consistent API error responses

use App\Http\Traits\ApiResponse;

📞 Support & Collaboration

class MyController extends ControllerUse GitHub Issues for bug tracking

{

    use ApiResponse;Create feature branches for new developments



    public function index()Weekly sync meetings to review progress

    {

        return $this->successResponse($data, 'Retrieved successfully');Document all API changes in /docs/api folder
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [...]);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }
        // ...
    }
}
```

---

## 🐛 Testing Strategy

### API Testing
- Use Postman collection (see `postman_collection.json`)
- Test all CRUD operations
- Test authentication flows
- Test family account management

### Filament Testing
- Test CRUD operations in admin panel
- Verify observer triggers on form submission
- Check notification bell displays alerts

### Budget Logic Testing
- Create expense to trigger 70% alert
- Verify email sent and database notification created
- Check Filament bell shows notification

---

## 📞 Support & Collaboration

- Use GitHub Issues for bug tracking
- Create feature branches for new developments
- Weekly sync meetings to review progress
- Document all API changes in this file
