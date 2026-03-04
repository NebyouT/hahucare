<?php
/**
 * Trace Menu Execution - Find where menu building stops
 * Run via SSH: php trace_menu_execution.php
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== TRACE MENU EXECUTION ===\n\n";

// Login as lab_technician
$labUser = User::role('lab_technician')->first();
if (!$labUser) {
    echo "ERROR: No lab_technician user found!\n";
    exit(1);
}

\Auth::login($labUser);
echo "Logged in as: {$labUser->email}\n";
echo "hasRole('lab_technician'): " . (auth()->user()->hasRole('lab_technician') ? 'YES' : 'NO') . "\n\n";

// Temporarily modify GenerateMenus.php to add debug logging
$menuFile = __DIR__ . '/app/Http/Middleware/GenerateMenus.php';
$originalContent = file_get_contents($menuFile);

// Insert debug statements at key points
$modifiedContent = $originalContent;

// Add debug at line 874 (before lab_technician block)
$marker1 = "if (auth()->user()->hasRole(['lab_technician'])) {";
$replacement1 = "error_log('DEBUG: Reached line 874 - lab_technician check'); if (auth()->user()->hasRole(['lab_technician'])) { error_log('DEBUG: Inside lab_technician block - will add menu items');";

$modifiedContent = str_replace($marker1, $replacement1, $modifiedContent);

// Add debug at line 877 (first menu item)
$marker2 = "\$this->staticMenu(\$menu, ['title' => 'Laboratory', 'order' => 0]);";
$replacement2 = "error_log('DEBUG: Adding Laboratory static menu'); \$this->staticMenu(\$menu, ['title' => 'Laboratory', 'order' => 0]); error_log('DEBUG: Laboratory static menu added');";

$modifiedContent = str_replace($marker2, $replacement2, $modifiedContent, $count);
if ($count > 0) {
    echo "Added debug logging to GenerateMenus.php\n";
    file_put_contents($menuFile, $modifiedContent);
} else {
    echo "WARNING: Could not add debug logging\n";
}

// Clear OPcache for the modified file
if (function_exists('opcache_invalidate')) {
    opcache_invalidate($menuFile, true);
}

// Now build the menu
echo "\nBuilding menu...\n";
try {
    $menuGen = new \App\Http\Middleware\GenerateMenus();
    $menu = $menuGen->handle();
    $items = $menu->all();
    echo "Menu built successfully. Total items: " . count($items) . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION during menu build: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

// Restore original file
file_put_contents($menuFile, $originalContent);
if (function_exists('opcache_invalidate')) {
    opcache_invalidate($menuFile, true);
}
echo "\nRestored original GenerateMenus.php\n";

// Check error log
echo "\n=== CHECKING ERROR LOG ===\n";
$errorLog = ini_get('error_log');
if (!$errorLog || $errorLog === 'syslog') {
    // Try common locations
    $possibleLogs = [
        '/var/log/php_errors.log',
        '/var/log/php-fpm/error.log',
        storage_path('logs/laravel.log'),
        __DIR__ . '/storage/logs/laravel.log',
        '/home/hahucaxq/logs/error_log',
        '/home/hahucaxq/public_html/error_log',
    ];
    
    foreach ($possibleLogs as $log) {
        if (file_exists($log)) {
            $errorLog = $log;
            break;
        }
    }
}

echo "Error log location: " . ($errorLog ?: 'NOT FOUND') . "\n";

if ($errorLog && file_exists($errorLog)) {
    echo "\nLast 20 lines of error log:\n";
    $lines = file($errorLog);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        if (stripos($line, 'DEBUG:') !== false || stripos($line, 'lab_technician') !== false) {
            echo "  " . trim($line) . "\n";
        }
    }
}

// Also check Laravel log
$laravelLog = storage_path('logs/laravel.log');
if (file_exists($laravelLog)) {
    echo "\n=== LARAVEL LOG (last 30 lines with 'menu' or 'lab') ===\n";
    $lines = file($laravelLog);
    $relevant = [];
    foreach (array_slice($lines, -200) as $line) {
        if (stripos($line, 'menu') !== false || stripos($line, 'lab') !== false || stripos($line, 'DEBUG') !== false) {
            $relevant[] = trim($line);
        }
    }
    foreach (array_slice($relevant, -30) as $line) {
        echo "  " . substr($line, 0, 200) . "\n";
    }
}

echo "\n=== DIRECT CODE INSPECTION ===\n";
echo "Let's check what's between line 76 (lab_technician dashboard) and line 874 (lab menu items)\n\n";

$lines = explode("\n", $originalContent);

// Find line 76-86 (lab_technician dashboard block)
echo "Lines 76-86 (lab_technician dashboard block):\n";
for ($i = 75; $i <= 85; $i++) {
    echo "  " . ($i+1) . ": " . trim($lines[$i]) . "\n";
}

echo "\nLines 88-100 (NOT lab_technician block):\n";
for ($i = 87; $i <= 99; $i++) {
    echo "  " . ($i+1) . ": " . trim($lines[$i]) . "\n";
}

echo "\nLines 872-880 (lab_technician menu block start):\n";
for ($i = 871; $i <= 879; $i++) {
    echo "  " . ($i+1) . ": " . trim($lines[$i]) . "\n";
}

// Check if there's a closing brace for the Menu::make callback
echo "\n=== CHECKING Menu::make CLOSURE ===\n";
$menuMakeLine = 0;
foreach ($lines as $num => $line) {
    if (strpos($line, 'return \Menu::make') !== false) {
        $menuMakeLine = $num + 1;
        echo "Menu::make starts at line {$menuMakeLine}\n";
        break;
    }
}

// Find where it closes
$depth = 0;
$inClosure = false;
for ($i = $menuMakeLine - 1; $i < count($lines); $i++) {
    $line = $lines[$i];
    if (strpos($line, 'function ($menu)') !== false) {
        $inClosure = true;
        $depth = 0;
    }
    if ($inClosure) {
        $depth += substr_count($line, '{') - substr_count($line, '}');
        if ($depth < 0) {
            echo "Menu::make closure CLOSES at line " . ($i + 1) . "\n";
            echo "  Line content: " . trim($line) . "\n";
            
            // Check if line 874 is before or after this
            if ($i + 1 < 874) {
                echo "\n*** PROBLEM FOUND! ***\n";
                echo "The Menu::make closure closes at line " . ($i + 1) . "\n";
                echo "But the lab_technician menu block is at line 874\n";
                echo "This means the lab menu items are OUTSIDE the Menu::make callback!\n";
                echo "They're never added to the menu object!\n";
            }
            break;
        }
    }
}

echo "\n=== DONE ===\n";
