# Quick Reference: Adding a New Module

## 🚀 Quick Start Commands

```bash
# 1. Create the module
php artisan module:make Laboratory

# 2. Enable the module
php artisan module:enable Laboratory

# 3. Create models
php artisan module:make-model LabTest Laboratory
php artisan module:make-model LabResult Laboratory

# 4. Create migrations
php artisan module:make-migration create_lab_tests_table Laboratory
php artisan module:make-migration create_lab_results_table Laboratory

# 5. Create controllers
php artisan module:make-controller Backend/LabTestController Laboratory
php artisan module:make-controller Backend/LabResultController Laboratory

# 6. Create requests (validation)
php artisan module:make-request LabTestRequest Laboratory

# 7. Run migrations
php artisan module:migrate Laboratory

# 8. Create seeders (optional)
php artisan module:make-seed LabTestSeeder Laboratory
php artisan module:seed Laboratory
```

---

## 📁 Module Structure Checklist

```
Modules/Laboratory/
├── ✅ Config/config.php
├── ✅ Http/
│   ├── Controllers/Backend/
│   ├── Middleware/
│   └── Requests/
├── ✅ Models/
├── ✅ Providers/
│   ├── LaboratoryServiceProvider.php
│   └── RouteServiceProvider.php
├── ✅ Resources/views/
├── ✅ Routes/
│   ├── web.php
│   └── api.php
├── ✅ database/
│   ├── migrations/
│   └── seeders/
├── ✅ composer.json
└── ✅ module.json
```

---

## 📝 Essential Files to Edit

### 1. `module.json`
```json
{
    "name": "Laboratory",
    "alias": "laboratory",
    "providers": [
        "Modules\\Laboratory\\Providers\\LaboratoryServiceProvider"
    ]
}
```

### 2. `modules_statuses.json` (root)
```json
{
    "Laboratory": true
}
```

### 3. Service Provider Pattern
```php
protected string $moduleName = 'Laboratory';
protected string $moduleNameLower = 'laboratory';

public function boot(): void
{
    $this->registerTranslations();
    $this->registerConfig();
    $this->registerViews();
    $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
}
```

### 4. Routes Pattern
```php
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {
    Route::group(['prefix' => 'lab-tests', 'as' => 'lab-tests.'], function () {
        Route::get('index_data', [LabTestController::class, 'index_data'])->name('index_data');
    });
    Route::resource('lab-tests', LabTestController::class);
});
```

### 5. Controller Pattern
```php
namespace Modules\Laboratory\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabTest;

class LabTestController extends Controller
{
    public function index() { }
    public function index_data(Request $request) { } // For DataTables
    public function create() { }
    public function store(Request $request) { }
    public function edit($id) { }
    public function update(Request $request, $id) { }
    public function destroy($id) { }
}
```

### 6. Model Pattern
```php
namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTest extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'code', 'price'];
    protected $casts = ['is_active' => 'boolean'];
    
    public function category() {
        return $this->belongsTo(LabTestCategory::class);
    }
}
```

---

## 🔗 Common Integration Patterns

### Link to Appointments
```php
public function appointment()
{
    return $this->belongsTo(\Modules\Appointment\Models\Appointment::class);
}
```

### Link to Patients/Customers
```php
public function patient()
{
    return $this->belongsTo(\Modules\Customer\Models\Customer::class, 'patient_id');
}
```

### Link to Users (Doctors, Technicians)
```php
public function doctor()
{
    return $this->belongsTo(\App\Models\User::class, 'doctor_id');
}
```

---

## 🎯 Common Middleware Used

- `auth` - User must be authenticated
- `auth_check` - Additional authentication checks
- `pharmaStatus` - Module-specific status check (create similar for Laboratory)

---

## 📊 Database Migration Patterns

```php
Schema::create('lab_tests', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->string('name');
    $table->text('description')->nullable();
    $table->bigInteger('category_id')->nullable();
    $table->decimal('price', 10, 2);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

---

## 🛠️ Useful Helper Functions

```php
// Get module path
module_path('Laboratory', 'database/migrations')

// Get module config
config('laboratory.setting_name')

// Load module views
view('laboratory::lab-tests.index')

// Module routes
route('backend.lab-tests.index')
```

---

## ✅ Post-Creation Checklist

- [ ] Module created and enabled
- [ ] Migrations run successfully
- [ ] Models created with relationships
- [ ] Controllers implement CRUD operations
- [ ] Routes defined (web and/or API)
- [ ] Views created (if needed)
- [ ] Permissions added to roles
- [ ] Module integrated with existing modules
- [ ] Seeders run (if applicable)
- [ ] Tests written (optional but recommended)

---

## 🔍 Debugging Commands

```bash
# List all modules and their status
php artisan module:list

# Check if module is enabled
php artisan module:list | grep Laboratory

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate autoload files
composer dump-autoload
```

---

## 📚 File Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Module | PascalCase | `Laboratory` |
| Model | PascalCase Singular | `LabTest` |
| Controller | PascalCase + Controller | `LabTestController` |
| Migration | snake_case | `create_lab_tests_table` |
| Table | snake_case plural | `lab_tests` |
| Route | kebab-case | `lab-tests` |
| View | kebab-case | `lab-tests/index.blade.php` |

---

## 🎨 View Blade Syntax

```blade
{{-- Extend layout --}}
@extends('backend.layouts.app')

{{-- Section title --}}
@section('title', 'Lab Tests')

{{-- Content section --}}
@section('content')
    <div class="container">
        <!-- Your content -->
    </div>
@endsection

{{-- Push scripts --}}
@push('scripts')
<script>
    // Your JavaScript
</script>
@endpush
```

---

## 🔐 Permission Patterns

```php
// Common permissions for a module
'view_lab_tests'
'create_lab_tests'
'edit_lab_tests'
'delete_lab_tests'
'export_lab_tests'
'approve_lab_results'
```

---

## 📦 Common Dependencies

Already included in `composer.json`:
- `nwidart/laravel-modules` - Module management
- `yajra/laravel-datatables-oracle` - DataTables
- `maatwebsite/excel` - Excel export
- `spatie/laravel-permission` - Roles & permissions
- `spatie/laravel-medialibrary` - File uploads

---

## 🚨 Common Pitfalls to Avoid

1. ❌ Forgetting to enable the module in `modules_statuses.json`
2. ❌ Not running `composer dump-autoload` after creating new classes
3. ❌ Incorrect namespace in service provider
4. ❌ Missing middleware on routes
5. ❌ Not clearing cache after config changes
6. ❌ Hardcoding module names instead of using variables
7. ❌ Not using soft deletes for important data
8. ❌ Forgetting to add relationships in models

---

## 💡 Pro Tips

1. ✅ Always use resource controllers for standard CRUD
2. ✅ Create separate `index_data` methods for DataTables
3. ✅ Use Form Requests for complex validation
4. ✅ Implement soft deletes for audit trails
5. ✅ Add indexes to frequently queried columns
6. ✅ Use eager loading to prevent N+1 queries
7. ✅ Create seeders for test data
8. ✅ Follow existing module patterns for consistency
