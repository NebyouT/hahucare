<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use Illuminate\Support\Facades\File;

echo "<h1>Firebase Push Notification Test</h1>";

$projectId = Setting::where('name', 'firebase_project_id')->first();
echo "<p><strong>Project ID:</strong> " . ($projectId ? $projectId->val : "❌ NOT FOUND") . "</p>";

$files = File::glob(storage_path('app/data/*.json'));
echo "<p><strong>Service Account:</strong> " . (count($files) > 0 ? "✅ Found" : "❌ Not Found") . "</p>";

if (count($files) > 0) {
    echo "<p><strong>File:</strong> " . basename($files[0]) . "</p>";
}

echo "<h2>Status: " . ($projectId && count($files) > 0 ? "✅ READY" : "❌ INCOMPLETE") . "</h2>";