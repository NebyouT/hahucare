# HahuLabs Project Analysis

## Project Overview

**HahuLabs** (also known as **KiviLabs Admin Panel**) is a comprehensive **Laravel-based modular CMS** designed for managing laboratory services, appointments, vendors, and subscriptions. It's built on the `nasirkhan/laravel-starter` framework and uses a modular architecture for extensibility.

---

## Technology Stack

### Backend
- **Framework**: Laravel 11.30 (PHP 8.2+)
- **Architecture**: Modular structure using `nwidart/laravel-modules`
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Authentication**: Laravel Sanctum for API authentication
- **Authorization**: Spatie Laravel Permission for role-based access control

### Frontend
- **JavaScript Framework**: Vue.js 3.2.45
- **UI Framework**: Bootstrap 5.3.3
- **Build Tool**: Laravel Mix 6.0.49
- **State Management**: Pinia 2.0.35
- **Routing**: Vue Router 4.1.6
- **Rich Text Editor**: TinyMCE 7.2, Quill
- **Data Tables**: DataTables.net with Bootstrap 5
- **Charts**: ApexCharts 4.5.0, Chart.js 4.4.8

### Key Dependencies
- **Payment Gateways**: Stripe, Razorpay
- **Notifications**: Firebase (Kreait), Twilio SDK
- **File Management**: Spatie Media Library
- **Export/Import**: Maatwebsite Excel, League CSV
- **PDF Generation**: Barryvdh Laravel DomPDF
- **Social Login**: Laravel Socialite (Facebook, GitHub, Google)
- **Backup**: Spatie Laravel Backup
- **Activity Logging**: Spatie Laravel Activity Log
- **Webhooks**: Spatie Webhook Client/Server

---

## Project Structure

```
hahulabs/
├── app/                    # Core application code
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Models/            # Eloquent models
│   ├── Providers/         # Service providers
│   ├── Services/          # Business logic services
│   ├── Trait/             # Reusable traits
│   ├── helpers.php        # Global helper functions
│   └── ...
├── Modules/               # Modular features (30+ modules)
│   ├── Appointment/       # Appointment management
│   ├── Lab/               # Laboratory services
│   ├── Vendor/            # Vendor management
│   ├── Subscriptions/     # Subscription plans
│   ├── User/              # User management
│   ├── Setting/           # Application settings
│   ├── Wallet/            # Wallet/payment system
│   ├── Prescription/      # Prescription handling
│   └── ...
├── resources/             # Views, assets, language files
├── routes/                # Route definitions
│   ├── web.php           # Web routes
│   ├── api.php           # API routes
│   └── auth.php          # Authentication routes
├── database/              # Migrations, seeders, factories
├── public/                # Public assets
├── config/                # Configuration files
└── storage/               # File storage, logs, cache
```

---

## Core Modules

The application uses **30+ active modules** (as defined in `modules_statuses.json`):

### Business Logic Modules
1. **Appointment** - Manage lab test appointments
2. **Lab** - Laboratory test catalog and management
3. **Vendor** - Vendor registration, management, and payments
4. **Subscriptions** - Subscription plan management
5. **Prescription** - Prescription upload and management
6. **Collector** - Sample collector management
7. **Wallet** - Digital wallet for payments
8. **Payout** - Vendor payout processing
9. **Bank** - Bank account management

### Supporting Modules
10. **User** - User management and authentication
11. **Category** - Category management
12. **Coupon** - Discount coupon system
13. **Review** - Rating and review system
14. **Helpdesk** - Customer support ticketing
15. **FAQ** - Frequently asked questions
16. **Banner** - Banner/slider management
17. **Page** - CMS page management
18. **Blog** - Blog/news management

### System Modules
19. **Setting** - Application configuration
20. **NotificationTemplate** - Notification templates
21. **Currency** - Multi-currency support
22. **Tax** - Tax calculation
23. **World** - Countries, states, cities data
24. **Constant** - System constants
25. **Document** - Document management
26. **Report** - Reporting and analytics
27. **Earning** - Revenue tracking
28. **Installer** - Application installer
29. **PackageManagement** - Package/bundle management
30. **CatlogManagement** - Catalog management

---

## How It Works

### 1. **Modular Architecture**

Each module is self-contained with its own:
- **Controllers** (HTTP request handling)
- **Models** (Database entities)
- **Views** (UI templates)
- **Routes** (URL endpoints)
- **Migrations** (Database schema)
- **Resources** (API transformers)

Example module structure (`Modules/Lab/`):
```
Lab/
├── Http/Controllers/      # Lab-specific controllers
├── Models/                # Lab models (Test, TestCategory, etc.)
├── Resources/             # Vue.js components
├── database/              # Migrations and seeders
├── routes/                # Module routes
└── module.json            # Module metadata
```

### 2. **Authentication & Authorization**

- **Multi-role system**: Admin, Vendor, Collector, User
- **Permission-based access control** using Spatie Permission
- **Social login** support (Google, Facebook, GitHub)
- **API authentication** via Laravel Sanctum tokens
- **Activity logging** for audit trails

### 3. **Vendor Management Flow**

1. **Vendor Registration** - Vendors register through a public form
2. **Subscription Selection** - Choose a subscription plan
3. **Payment Processing** - Stripe/Razorpay integration
4. **Approval Workflow** - Admin approves vendor accounts
5. **Lab Management** - Vendors manage their lab tests
6. **Commission System** - Automatic commission calculation
7. **Payout Processing** - Vendor earnings and withdrawals

### 4. **Appointment Workflow**

1. **User browses labs** and available tests
2. **Selects tests** and adds to cart
3. **Books appointment** with preferred time/location
4. **Payment processing** (wallet, card, or cash on collection)
5. **Collector assignment** for sample collection
6. **Prescription upload** (if required)
7. **Sample collection** and lab processing
8. **Results delivery** via app/email

### 5. **Notification System**

- **Multi-channel notifications**: Email, SMS (Twilio), Push (Firebase)
- **Template-based** notifications for consistency
- **Real-time notifications** using Laravel's notification system
- **Webhook support** for external integrations

### 6. **Payment & Wallet System**

- **Multiple payment gateways**: Stripe, Razorpay
- **Digital wallet** for users and vendors
- **Commission tracking** on transactions
- **Payout management** for vendors
- **Transaction history** and reporting

### 7. **Data Management**

- **Import/Export** functionality using Maatwebsite Excel
- **Backup system** using Spatie Backup
- **Media management** with Spatie Media Library
- **Activity logging** for all critical operations
- **Multi-language support** with Vue i18n

### 8. **Frontend Architecture**

- **Vue.js 3** with Composition API
- **Component-based** UI development
- **Pinia** for state management
- **Vue Router** for SPA navigation
- **Bootstrap 5** for responsive design
- **DataTables** for data grids
- **ApexCharts/Chart.js** for analytics visualization

---

## Key Features

### Admin Panel
✅ Dashboard with analytics and charts  
✅ User, vendor, and collector management  
✅ Lab test catalog management  
✅ Appointment scheduling and tracking  
✅ Commission and payout management  
✅ Coupon and discount management  
✅ Multi-currency and tax configuration  
✅ Notification template management  
✅ Activity logs and reporting  
✅ System settings and customization  

### Vendor Features
✅ Vendor registration and onboarding  
✅ Subscription plan selection  
✅ Lab test management  
✅ Appointment notifications  
✅ Earnings and payout tracking  
✅ Commission reports  
✅ Profile and bank account management  

### User Features
✅ Browse labs and tests  
✅ Book appointments  
✅ Upload prescriptions  
✅ Digital wallet  
✅ Order history  
✅ Reviews and ratings  
✅ Multi-language support  

---

## Configuration

### Environment Setup
The `.env.example` file shows key configurations:

- **App**: Name (KiviLab), environment, debug mode
- **Database**: SQLite default, MySQL/PostgreSQL support
- **Storage**: Local/S3 support
- **Mail**: SMTP configuration
- **Payment**: Stripe, Razorpay credentials
- **Social Login**: Facebook, GitHub, Google OAuth
- **Firebase**: Push notification configuration
- **Twilio**: SMS notification support

### Module Management
Modules can be enabled/disabled via `modules_statuses.json` without code changes.

---

## Helper Functions

The `app/helpers.php` file provides 70+ global helper functions:

- **`setting($key, $default)`** - Get/set application settings
- **`formatCurrency()`** - Format currency with locale support
- **`sendNotification($data)`** - Send multi-channel notifications
- **`storeMediaFile()`** - Handle file uploads
- **`getSingleMedia()`** - Retrieve media files
- **`formatDate()`** - Format dates with timezone
- **`language_direction()`** - RTL/LTR support
- **`getCustomizationSetting()`** - Get UI customization settings
- And many more...

---

## Development Workflow

### Setup Commands
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Link storage
php artisan storage:link

# Build assets
npm run dev

# Start development server
php artisan serve
```

### Build Commands
```bash
# Development build
npm run dev

# Watch for changes
npm run watch

# Production build
npm run prod

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## Security Features

- **CSRF Protection** on all forms
- **SQL Injection Prevention** via Eloquent ORM
- **XSS Protection** with HTML Purifier (Mews Purifier)
- **Role-based Access Control** (RBAC)
- **Password Confirmation** for sensitive operations
- **Activity Logging** for audit trails
- **Secure File Uploads** with validation
- **API Rate Limiting** via Sanctum

---

## API Architecture

The application provides RESTful APIs for:
- User authentication and registration
- Lab test browsing
- Appointment booking
- Prescription upload
- Wallet transactions
- Notifications
- User profile management

API routes are defined in `routes/api.php` with Sanctum authentication.

---

## Deployment Considerations

### Requirements
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL/PostgreSQL (production)
- Web server (Apache/Nginx)
- SSL certificate (for production)

### Production Setup
1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure production database
3. Set up proper file permissions
4. Configure queue workers for background jobs
5. Set up scheduled tasks (cron jobs)
6. Configure backup storage (S3 recommended)
7. Set up monitoring and logging

### Docker Support
The project includes `docker-compose.yml` for containerized deployment.

---

## Summary

**HahuLabs** is a sophisticated, production-ready laboratory management system with:

- **Modular architecture** for easy customization
- **Multi-role support** (Admin, Vendor, Collector, User)
- **Complete appointment workflow** from booking to results
- **Payment integration** with wallet system
- **Vendor marketplace** with commission tracking
- **Multi-channel notifications** (Email, SMS, Push)
- **Modern tech stack** (Laravel 11 + Vue 3)
- **Extensive features** for lab service management

The codebase is well-structured, follows Laravel best practices, and is designed for scalability and maintainability.
