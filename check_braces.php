<?php
/**
 * Check brace structure in GenerateMenus.php
 */

$file = __DIR__ . '/app/Http/Middleware/GenerateMenus.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

echo "=== CHECKING BRACE STRUCTURE ===\n\n";

// Find the "Only show these menus if NOT lab_technician" block
$startLine = 0;
$depth = 0;
$inBlock = false;

foreach ($lines as $num => $line) {
    $lineNum = $num + 1;
    
    // Track when we enter the NOT lab_technician block
    if (strpos($line, '// Only show these menus if NOT lab_technician') !== false) {
        echo "Line {$lineNum}: Found start comment\n";
        $startLine = $lineNum;
        $inBlock = true;
        $depth = 0;
    }
    
    if ($inBlock) {
        // Count braces
        $openBraces = substr_count($line, '{');
        $closeBraces = substr_count($line, '}');
        $depth += $openBraces - $closeBraces;
        
        if ($openBraces > 0 || $closeBraces > 0) {
            echo "Line {$lineNum}: depth={$depth} (open={$openBraces}, close={$closeBraces}) | " . trim(substr($line, 0, 80)) . "\n";
        }
        
        // Check if we've closed the block
        if ($depth === 0 && $lineNum > $startLine + 2 && $closeBraces > 0) {
            echo "\n*** Block CLOSES at line {$lineNum} ***\n\n";
            $inBlock = false;
        }
    }
    
    // Also mark the lab_technician block
    if (strpos($line, "if (auth()->user()->hasRole(['lab_technician']))") !== false) {
        echo "\n>>> Line {$lineNum}: LAB_TECHNICIAN BLOCK STARTS <<<\n";
    }
}

if ($inBlock) {
    echo "\n*** WARNING: Block never closed! Still open at end of file ***\n";
}

echo "\n=== CHECKING SPECIFIC LINES ===\n";
echo "Line 89: " . trim($lines[88]) . "\n";
echo "Line 874: " . trim($lines[873]) . "\n";
echo "Line 921: " . trim($lines[920]) . "\n";
