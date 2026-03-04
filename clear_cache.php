<?php

/**
 * Clear Laravel Configuration Cache
 * 
 * Upload this to your production server and run via browser:
 * https://hahucare.com/clear_cache.php
 * 
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>";
echo "===========================================\n";
echo "   Clearing Laravel Cache\n";
echo "===========================================\n\n";

// Clear config cache
echo "🗑️  Clearing config cache...\n";
\Artisan::call('config:clear');
echo "   ✅ Config cache cleared\n";

// Clear application cache
echo "🗑️  Clearing application cache...\n";
\Artisan::call('cache:clear');
echo "   ✅ Application cache cleared\n";

// Clear route cache
echo "🗑️  Clearing route cache...\n";
\Artisan::call('route:clear');
echo "   ✅ Route cache cleared\n";

// Clear view cache
echo "🗑️  Clearing view cache...\n";
\Artisan::call('view:clear');
echo "   ✅ View cache cleared\n";

echo "\n===========================================\n";
echo "   ✅ ALL CACHES CLEARED!\n";
echo "===========================================\n\n";

echo "Now test Google login again!\n";
echo "DELETE THIS FILE: clear_cache.php\n";
echo "</pre>";
