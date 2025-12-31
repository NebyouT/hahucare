<?php

// ================= SECURITY =================
$secret = 'DEPLOY_SECRET_123';

if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    exit('Forbidden');
}

// ================= DEPLOY =================
$cmd = "
cd /home/hahucaxq/public_html &&
git pull origin main 2>&1
";

$output = shell_exec($cmd);

// ================= LOG =================
file_put_contents(
    '/home/hahucaxq/public_html/deploy.log',
    date('Y-m-d H:i:s') . PHP_EOL . $output . PHP_EOL,
    FILE_APPEND
);

echo 'Deployment successful';
