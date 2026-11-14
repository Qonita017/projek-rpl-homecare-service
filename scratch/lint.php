<?php
// Simple CLI runner to check syntax of all PHP files in the project
function checkDir($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            checkDir($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $cmd = '"C:\\xampp\\php\\php.exe" -l "' . $path . '"';
            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);
            if ($returnVar !== 0) {
                echo "FAIL: $path\n";
                echo implode("\n", $output) . "\n";
            } else {
                echo "OK: $path\n";
            }
        }
    }
}

$rootDir = dirname(__DIR__);
echo "=== LINTING PHP FILES ===\n";
checkDir($rootDir . '/app');
checkDir($rootDir . '/routes');
checkDir($rootDir . '/public');
echo "=== COMPLETED ===\n";
