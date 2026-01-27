# HahuCare Module Architecture Guide

## Overview

This is a **Laravel 11** healthcare management application using the **nwidart/laravel-modules** package for modular architecture. The application is organized into independent, reusable modules that handle specific business domains.

---

## 📁 Current Module Structure

The application currently has **38 modules** located in the `Modules/` directory:

### Core Healthcare Modules
- **Appointment** - Patient appointment scheduling and management
- **Clinic** - Clinic/hospital management
- **Pharma** - Pharmacy and medicine management
- **Bed** - Hospital bed management
- **Vital** - Patient vital signs tracking

### Business Modules
- **Product** - Product catalog management
- **Service** - Service offerings
- **Booking** - Booking system
- **QuickBooking** - Quick booking functionality

### Support Modules
- **Customer** - Customer/patient management
- **Commission** - Commission calculations
- **Earning** - Earnings tracking
- **Wallet** - Digital wallet functionality
- **Tax** - Tax management
- **Currency** - Multi-currency support

### Content & Frontend
- **Frontend** - Public-facing website
- **FrontendSetting** - Frontend configuration
- **Blog** - Blog/news management
- **Page** - Static pages
- **Slider** - Image sliders
- **FAQ** - Frequently asked questions

### System Modules
- **Constant** - System constants
- **CustomField** - Custom field definitions
- **CustomForm** - Custom form builder
- **Language** - Multi-language support
- **Location** - Location management
- **World** - Countries/regions data
- **NotificationTemplate** - Notification templates
- **Plugins** - Plugin system

### Other Modules
- **Logistic** - Logistics management
- **MultiVendor** - Multi-vendor support
- **Promotion** - Promotional campaigns
- **RequestService** - Service requests
- **Subscriptions** - Subscription management
- **Tag** - Tagging system
- **Tip** - Tipping functionality
- **Installer** - Application installer

---

## 🏗️ Module Anatomy

Each module follows a consistent structure. Let's examine the **Pharma** module as a reference:

```
Modules/Pharma/
├── Config/
│   └── config.php                    # Module-specific configuration
├── Console/                          # Artisan commands
├── Http/
│   ├── Controllers/
│   │   └── Backend/                  # Admin controllers
│   ├── Middleware/                   # Module-specific middleware
│   └── Requests/                     # Form request validation
├── Models/                           # Eloquent models
│   ├── Medicine.php
│   ├── MedicineCategory.php
│   ├── Supplier.php
│   └── ...
├── Providers/
│   ├── PharmaServiceProvider.php     # Main service provider
│   └── RouteServiceProvider.php      # Route registration
├── Resources/
│   ├── assets/                       # CSS, JS, images
│   ├── lang/                         # Translations
│   └── views/                        # Blade templates
├── Routes/
│   ├── web.php                       # Web routes
│   └── api.php                       # API routes
├── Tests/                            # Unit and feature tests
├── Traits/                           # Reusable traits
├── Transformers/                     # API resource transformers
├── database/
│   ├── migrations/                   # Database migrations
│   ├── seeders/                      # Database seeders
│   └── factories/                    # Model factories
├── composer.json                     # Module dependencies
├── module.json                       # Module metadata
├── package.json                      # NPM dependencies
└── vite.config.js / webpack.mix.js   # Asset compilation
```

---

## 🔧 How Modules Work

### 1. Module Registration

Modules are registered through the **module.json** file:

```json
{
    "name": "Pharma",
    "alias": "pharma",
    "description": "",
    "keywords": [],
    "priority": 0,
    "providers": [
        "Modules\\Pharma\\Providers\\PharmaServiceProvider"
    ],
    "files": []
}
```

### 2. Module Activation

Module status is tracked in `modules_statuses.json` at the project root:

```json
{
    "Pharma": true,
    "Clinic": true,
    "Laboratory": true  // Will be added when we create it
}
```

- `true` = Module is **enabled** and loaded
- `false` = Module is **disabled** and not loaded

### 3. Service Provider

The **Service Provider** is the heart of the module. It registers:
- Routes (via RouteServiceProvider)
- Views
- Translations
- Migrations
- Configuration
- Commands

**Example from PharmaServiceProvider.php:**

```php
<?php

namespace Modules\Pharma\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PharmaServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Pharma';
    protected string $moduleNameLower = 'pharma';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), 'Pharma');
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower.'.php')
        ], 'config');
        
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), 
            $this->moduleNameLower
        );
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.config('modules.paths.generator.component-class.path'));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }
        return $paths;
    }
}
```

### 4. Routes

Routes are defined in `Routes/web.php` and `Routes/api.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Pharma\Http\Controllers\Backend\MedicineController;

// Protected routes with authentication
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check', 'pharmaStatus']], function () {
    
    Route::group(['prefix' => 'medicine', 'as' => 'medicine.'], function () {
        Route::get('index_data', [MedicineController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [MedicineController::class, 'bulk_action'])->name('bulk_action');
        Route::get('export', [MedicineController::class, 'export'])->name('export');
    });
    
    Route::resource('medicine', MedicineController::class);
});
```

### 5. Database Migrations

Migrations follow Laravel naming conventions:

```
2025_05_14_102608_create_medicines_table.php
2025_05_14_102759_create_medicine_categories_table.php
2025_05_15_063210_create_medicine_forms_table.php
```

**Example Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('dosage')->nullable();
            $table->bigInteger('category_id')->nullable();
            $table->bigInteger('form_id')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->text('note')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->string('purchase_price')->nullable();
            $table->string('selling_price')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
```

### 6. Models

Models are placed in the `Models/` directory:

```php
<?php

namespace Modules\Pharma\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dosage',
        'category_id',
        'form_id',
        'expiry_date',
        'supplier_id',
        'purchase_price',
        'selling_price',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
```

### 7. Controllers

Controllers handle business logic:

```php
<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Pharma\Models\Medicine;

class MedicineController extends Controller
{
    public function index()
    {
        return view('pharma::medicine.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string',
            'category_id' => 'nullable|exists:medicine_categories,id',
        ]);

        $medicine = Medicine::create($validated);

        return redirect()->route('backend.medicine.index')
            ->with('success', 'Medicine created successfully');
    }
}
```

---

## 🧪 Creating a Laboratory Module

Now let's create a **Laboratory** module step by step!

### Step 1: Generate the Module

Use the artisan command to scaffold the module:

```bash
php artisan module:make Laboratory
```

This creates the basic structure in `Modules/Laboratory/`.

### Step 2: Update Module Configuration

**File: `Modules/Laboratory/module.json`**

```json
{
    "name": "Laboratory",
    "alias": "laboratory",
    "description": "Laboratory test management module",
    "keywords": ["laboratory", "tests", "diagnostics"],
    "priority": 0,
    "providers": [
        "Modules\\Laboratory\\Providers\\LaboratoryServiceProvider"
    ],
    "files": []
}
```

### Step 3: Enable the Module

Add to `modules_statuses.json`:

```json
{
    "Laboratory": true
}
```

Or use the command:

```bash
php artisan module:enable Laboratory
```

### Step 4: Create Database Migrations

Generate migrations for laboratory-related tables:

```bash
php artisan module:make-migration create_lab_tests_table Laboratory
php artisan module:make-migration create_lab_test_categories_table Laboratory
php artisan module:make-migration create_lab_results_table Laboratory
php artisan module:make-migration create_lab_equipment_table Laboratory
```

**Example: `database/migrations/xxxx_create_lab_tests_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_code')->unique();
            $table->string('test_name');
            $table->text('description')->nullable();
            $table->bigInteger('category_id')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->nullable(); // Time to complete test
            $table->text('preparation_instructions')->nullable();
            $table->text('normal_range')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
```

**Example: `database/migrations/xxxx_create_lab_results_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->string('result_code')->unique();
            $table->bigInteger('patient_id');
            $table->bigInteger('doctor_id')->nullable();
            $table->bigInteger('lab_test_id');
            $table->bigInteger('appointment_id')->nullable();
            $table->dateTime('test_date');
            $table->dateTime('result_date')->nullable();
            $table->text('result_value')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->bigInteger('technician_id')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('sample_id')->nullable();
            $table->text('attachments')->nullable(); // JSON field for file paths
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
```

### Step 5: Create Models

Generate models:

```bash
php artisan module:make-model LabTest Laboratory
php artisan module:make-model LabTestCategory Laboratory
php artisan module:make-model LabResult Laboratory
php artisan module:make-model LabEquipment Laboratory
```

**File: `Models/LabTest.php`**

```php
<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'test_code',
        'test_name',
        'description',
        'category_id',
        'price',
        'duration_minutes',
        'preparation_instructions',
        'normal_range',
        'unit_of_measurement',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(LabTestCategory::class, 'category_id');
    }

    public function results()
    {
        return $this->hasMany(LabResult::class);
    }
}
```

**File: `Models/LabResult.php`**

```php
<?php

namespace Modules\Laboratory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class LabResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'result_code',
        'patient_id',
        'doctor_id',
        'lab_test_id',
        'appointment_id',
        'test_date',
        'result_date',
        'result_value',
        'remarks',
        'status',
        'technician_id',
        'sample_type',
        'sample_id',
        'attachments',
    ];

    protected $casts = [
        'test_date' => 'datetime',
        'result_date' => 'datetime',
        'attachments' => 'array',
    ];

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
```

### Step 6: Create Controllers

Generate controllers:

```bash
php artisan module:make-controller Backend/LabTestController Laboratory
php artisan module:make-controller Backend/LabResultController Laboratory
php artisan module:make-controller Backend/LabCategoryController Laboratory
```

**File: `Http/Controllers/Backend/LabTestController.php`**

```php
<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabTest;
use Modules\Laboratory\Models\LabTestCategory;

class LabTestController extends Controller
{
    public function index()
    {
        return view('laboratory::lab-tests.index');
    }

    public function index_data(Request $request)
    {
        $query = LabTest::with('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('test_name', 'like', "%{$search}%")
                  ->orWhere('test_code', 'like', "%{$search}%");
            });
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                return view('laboratory::lab-tests.action', compact('row'));
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $categories = LabTestCategory::where('is_active', true)->get();
        return view('laboratory::lab-tests.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'test_code' => 'required|string|unique:lab_tests,test_code',
            'test_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:lab_test_categories,id',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'preparation_instructions' => 'nullable|string',
            'normal_range' => 'nullable|string',
            'unit_of_measurement' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        LabTest::create($validated);

        return redirect()->route('backend.lab-tests.index')
            ->with('success', 'Lab test created successfully');
    }

    public function edit($id)
    {
        $labTest = LabTest::findOrFail($id);
        $categories = LabTestCategory::where('is_active', true)->get();
        return view('laboratory::lab-tests.edit', compact('labTest', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $labTest = LabTest::findOrFail($id);

        $validated = $request->validate([
            'test_code' => 'required|string|unique:lab_tests,test_code,' . $id,
            'test_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:lab_test_categories,id',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'preparation_instructions' => 'nullable|string',
            'normal_range' => 'nullable|string',
            'unit_of_measurement' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $labTest->update($validated);

        return redirect()->route('backend.lab-tests.index')
            ->with('success', 'Lab test updated successfully');
    }

    public function destroy($id)
    {
        $labTest = LabTest::findOrFail($id);
        $labTest->delete();

        return response()->json(['message' => 'Lab test deleted successfully']);
    }
}
```

### Step 7: Define Routes

**File: `Routes/web.php`**

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Laboratory\Http\Controllers\Backend\LabTestController;
use Modules\Laboratory\Http\Controllers\Backend\LabResultController;
use Modules\Laboratory\Http\Controllers\Backend\LabCategoryController;

// Backend routes with authentication
Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {
    
    // Lab Tests
    Route::group(['prefix' => 'lab-tests', 'as' => 'lab-tests.'], function () {
        Route::get('index_data', [LabTestController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [LabTestController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [LabTestController::class, 'update_status'])->name('update_status');
        Route::get('export', [LabTestController::class, 'export'])->name('export');
    });
    Route::resource('lab-tests', LabTestController::class);

    // Lab Results
    Route::group(['prefix' => 'lab-results', 'as' => 'lab-results.'], function () {
        Route::get('index_data', [LabResultController::class, 'index_data'])->name('index_data');
        Route::post('update-status', [LabResultController::class, 'updateStatus'])->name('update_status');
        Route::get('print/{id}', [LabResultController::class, 'print'])->name('print');
        Route::get('download/{id}', [LabResultController::class, 'download'])->name('download');
        Route::post('upload-attachment/{id}', [LabResultController::class, 'uploadAttachment'])->name('upload_attachment');
    });
    Route::resource('lab-results', LabResultController::class);

    // Lab Categories
    Route::group(['prefix' => 'lab-categories', 'as' => 'lab-categories.'], function () {
        Route::get('index_data', [LabCategoryController::class, 'index_data'])->name('index_data');
        Route::post('bulk-action', [LabCategoryController::class, 'bulk_action'])->name('bulk_action');
    });
    Route::resource('lab-categories', LabCategoryController::class);
});
```

### Step 8: Create Views

Create blade templates in `Resources/views/`:

**File: `Resources/views/lab-tests/index.blade.php`**

```blade
@extends('backend.layouts.app')

@section('title', 'Lab Tests')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Lab Tests</h4>
                    <a href="{{ route('backend.lab-tests.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Test
                    </a>
                </div>
                <div class="card-body">
                    <table id="lab-tests-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Test Code</th>
                                <th>Test Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#lab-tests-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("backend.lab-tests.index_data") }}',
        columns: [
            { data: 'test_code', name: 'test_code' },
            { data: 'test_name', name: 'test_name' },
            { data: 'category.name', name: 'category.name' },
            { data: 'price', name: 'price' },
            { data: 'duration_minutes', name: 'duration_minutes' },
            { data: 'is_active', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush
```

### Step 9: Run Migrations

```bash
php artisan module:migrate Laboratory
```

Or migrate all modules:

```bash
php artisan migrate
```

### Step 10: Create Seeders (Optional)

```bash
php artisan module:make-seed LabTestCategorySeeder Laboratory
php artisan module:make-seed LabTestSeeder Laboratory
```

**File: `database/seeders/LabTestCategorySeeder.php`**

```php
<?php

namespace Modules\Laboratory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Laboratory\Models\LabTestCategory;

class LabTestCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Hematology', 'description' => 'Blood tests', 'is_active' => true],
            ['name' => 'Biochemistry', 'description' => 'Chemical analysis', 'is_active' => true],
            ['name' => 'Microbiology', 'description' => 'Bacterial and viral tests', 'is_active' => true],
            ['name' => 'Immunology', 'description' => 'Immune system tests', 'is_active' => true],
            ['name' => 'Pathology', 'description' => 'Tissue analysis', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            LabTestCategory::create($category);
        }
    }
}
```

Run seeders:

```bash
php artisan module:seed Laboratory
```

---

## 🔗 Module Integration Points

### 1. **Integration with Appointment Module**

Lab tests can be ordered during appointments:

```php
// In LabResult model
public function appointment()
{
    return $this->belongsTo(\Modules\Appointment\Models\Appointment::class);
}
```

### 2. **Integration with Patient/Customer Module**

Link lab results to patients:

```php
// In LabResult model
public function patient()
{
    return $this->belongsTo(\Modules\Customer\Models\Customer::class, 'patient_id');
}
```

### 3. **Integration with Billing**

Create billing records for lab tests:

```php
// When creating a lab result
$billing = new \Modules\Billing\Models\BillingRecord([
    'patient_id' => $labResult->patient_id,
    'service_type' => 'laboratory',
    'service_id' => $labResult->id,
    'amount' => $labResult->labTest->price,
]);
$billing->save();
```

### 4. **Permissions and Roles**

Add permissions for the Laboratory module:

```php
// In a migration or seeder
$permissions = [
    'view_lab_tests',
    'create_lab_tests',
    'edit_lab_tests',
    'delete_lab_tests',
    'view_lab_results',
    'create_lab_results',
    'edit_lab_results',
    'approve_lab_results',
];

foreach ($permissions as $permission) {
    Permission::create(['name' => $permission]);
}
```

---

## 📋 Useful Artisan Commands

```bash
# Create a new module
php artisan module:make ModuleName

# List all modules
php artisan module:list

# Enable a module
php artisan module:enable ModuleName

# Disable a module
php artisan module:disable ModuleName

# Create a controller
php artisan module:make-controller ControllerName ModuleName

# Create a model
php artisan module:make-model ModelName ModuleName

# Create a migration
php artisan module:make-migration migration_name ModuleName

# Create a seeder
php artisan module:make-seed SeederName ModuleName

# Create a request
php artisan module:make-request RequestName ModuleName

# Run migrations for a specific module
php artisan module:migrate ModuleName

# Run seeders for a specific module
php artisan module:seed ModuleName

# Rollback migrations for a module
php artisan module:migrate-rollback ModuleName

# Publish module assets
php artisan module:publish ModuleName
```

---

## 🎯 Best Practices

### 1. **Naming Conventions**
- Module names: PascalCase (e.g., `Laboratory`, `Pharma`)
- Routes: kebab-case (e.g., `lab-tests`, `lab-results`)
- Database tables: snake_case (e.g., `lab_tests`, `lab_results`)
- Models: PascalCase singular (e.g., `LabTest`, `LabResult`)

### 2. **Module Independence**
- Modules should be as independent as possible
- Use interfaces for cross-module communication
- Avoid tight coupling between modules

### 3. **Database Design**
- Use foreign keys for relationships
- Add indexes for frequently queried columns
- Use soft deletes for important data
- Include `created_at` and `updated_at` timestamps

### 4. **Code Organization**
- Keep controllers thin, move business logic to services
- Use Form Requests for validation
- Use Resource/Transformer classes for API responses
- Create reusable traits for common functionality

### 5. **Security**
- Always use middleware for authentication
- Implement role-based access control
- Validate all user inputs
- Sanitize data before displaying

### 6. **Testing**
- Write unit tests for models
- Write feature tests for controllers
- Test API endpoints
- Test database migrations

---

## 🚀 Next Steps for Laboratory Module

1. **Add API endpoints** for mobile/external integrations
2. **Create reports** for lab test analytics
3. **Implement notifications** for test results
4. **Add file upload** for test result attachments
5. **Create dashboard widgets** for lab statistics
6. **Integrate with payment gateway** for online payments
7. **Add barcode/QR code** generation for samples
8. **Implement email/SMS** notifications for patients
9. **Create printable** lab result templates
10. **Add audit logging** for compliance

---

## 📚 Additional Resources

- **Laravel Modules Documentation**: https://nwidart.com/laravel-modules/
- **Laravel Documentation**: https://laravel.com/docs/11.x
- **Module Configuration**: `config/modules.php`
- **Module Status**: `modules_statuses.json`

---

## 🎓 Summary

This healthcare application uses a **modular architecture** where each business domain is encapsulated in its own module. To add a new module like **Laboratory**:

1. ✅ Generate the module structure using artisan commands
2. ✅ Create database migrations for your tables
3. ✅ Define Eloquent models with relationships
4. ✅ Create controllers for business logic
5. ✅ Define routes (web and API)
6. ✅ Create views using Blade templates
7. ✅ Run migrations and seeders
8. ✅ Enable the module in `modules_statuses.json`
9. ✅ Integrate with existing modules (Appointment, Patient, Billing)
10. ✅ Add permissions and roles for access control

The modular approach makes the codebase **maintainable**, **scalable**, and **testable**!
