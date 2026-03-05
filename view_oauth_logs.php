<?php
/**
 * View OAuth Logs - Real-time log viewer
 * Run via SSH: php view_oauth_logs.php
 * This shows the exact flow when you try to login with Google
 */

echo "===========================================\n";
echo "   OAUTH LOGIN LOG VIEWER\n";
echo "===========================================\n\n";

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "❌ Log file not found: {$logFile}\n";
    echo "Creating log directory...\n";
    @mkdir(__DIR__ . '/storage/logs', 0755, true);
    @touch($logFile);
    echo "Log file created. Try logging in with Google now.\n";
    exit(1);
}

echo "Log file: {$logFile}\n";
echo "File size: " . number_format(filesize($logFile)) . " bytes\n\n";

// Read last 200 lines
$lines = file($logFile);
$totalLines = count($lines);
echo "Total log lines: {$totalLines}\n\n";

// Filter for Social Login related entries
$socialLoginEntries = [];
$currentEntry = [];
$inEntry = false;

foreach (array_slice($lines, -200) as $line) {
    // Check if this is a new log entry
    if (preg_match('/^\[\d{4}-\d{2}-\d{2}/', $line)) {
        // Save previous entry if it was social login related
        if ($inEntry && count($currentEntry) > 0) {
            $socialLoginEntries[] = implode('', $currentEntry);
        }
        
        // Start new entry
        $currentEntry = [$line];
        
        // Check if this entry is social login related
        $inEntry = (
            stripos($line, 'social') !== false ||
            stripos($line, 'google') !== false ||
            stripos($line, 'oauth') !== false ||
            stripos($line, 'socialite') !== false ||
            stripos($line, 'findOrCreateUser') !== false ||
            stripos($line, 'UserProvider') !== false
        );
    } else {
        // Continuation of current entry
        if ($inEntry) {
            $currentEntry[] = $line;
        }
    }
}

// Don't forget the last entry
if ($inEntry && count($currentEntry) > 0) {
    $socialLoginEntries[] = implode('', $currentEntry);
}

if (count($socialLoginEntries) > 0) {
    echo "Found " . count($socialLoginEntries) . " Social Login log entries\n";
    echo "===========================================\n\n";
    
    foreach ($socialLoginEntries as $i => $entry) {
        echo "Entry #" . ($i + 1) . ":\n";
        echo str_repeat('-', 80) . "\n";
        echo $entry;
        echo "\n";
    }
} else {
    echo "❌ No Social Login log entries found in last 200 lines\n\n";
    echo "This means either:\n";
    echo "1. You haven't tried to login with Google yet\n";
    echo "2. The logs are being written elsewhere\n";
    echo "3. The logging code hasn't been deployed yet\n\n";
}

echo "===========================================\n";
echo "   INSTRUCTIONS\n";
echo "===========================================\n";
echo "1. Deploy the updated code:\n";
echo "   cd ~/public_html\n";
echo "   git pull origin master\n\n";
echo "2. Clear Laravel cache:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";
echo "3. Try to login with Google:\n";
echo "   https://hahucare.com/login/google\n\n";
echo "4. Run this script again to see the logs:\n";
echo "   php view_oauth_logs.php\n\n";
echo "5. The logs will show EXACTLY where it fails\n";
echo "===========================================\n";
