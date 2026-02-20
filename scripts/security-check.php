<?php

/**
 * Security Check Script untuk Laravel
 * Usage: php scripts/security-check.php
 */

echo "🔒 Laravel Security Check\n";
echo str_repeat("=", 50) . "\n\n";

$issues = [];
$warnings = [];
$passed = [];

// Helper function to read .env file
function getEnvValue($key, $default = null) {
    if (!file_exists('.env')) {
        return $default;
    }
    
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if ($name === $key) {
            return $value;
        }
    }
    
    return $default;
}

// Check APP_DEBUG
$debug = getEnvValue('APP_DEBUG', 'false');
if ($debug === 'true' || $debug === true) {
    $issues[] = "❌ APP_DEBUG is set to TRUE - This should be FALSE in production!";
} else {
    $passed[] = "✅ APP_DEBUG is set to FALSE";
}

// Check APP_ENV
$env = getEnvValue('APP_ENV', 'local');
if ($env === 'production') {
    $passed[] = "✅ APP_ENV is set to production";
} else {
    $warnings[] = "⚠️  APP_ENV is set to '{$env}' - Should be 'production' for production";
}

// Check APP_KEY
$key = getEnvValue('APP_KEY');
if (empty($key)) {
    $issues[] = "❌ APP_KEY is not set! Run: php artisan key:generate";
} else {
    $passed[] = "✅ APP_KEY is set";
}

// Check .env file
if (file_exists('.env')) {
    $passed[] = "✅ .env file exists";
    
    // Check if .env is in .gitignore
    $gitignore = file_get_contents('.gitignore');
    if (strpos($gitignore, '.env') !== false) {
        $passed[] = "✅ .env is in .gitignore";
    } else {
        $issues[] = "❌ .env is NOT in .gitignore - This is a security risk!";
    }
} else {
    $warnings[] = "⚠️  .env file not found";
}

// Check storage permissions
$storageWritable = is_writable('storage');
$cacheWritable = is_writable('bootstrap/cache');

if ($storageWritable) {
    $passed[] = "✅ storage/ directory is writable";
} else {
    $issues[] = "❌ storage/ directory is NOT writable";
}

if ($cacheWritable) {
    $passed[] = "✅ bootstrap/cache/ directory is writable";
} else {
    $issues[] = "❌ bootstrap/cache/ directory is NOT writable";
}

// Check HTTPS
$url = getEnvValue('APP_URL', '');
if (strpos($url, 'https://') === 0) {
    $passed[] = "✅ APP_URL uses HTTPS";
} else {
    $warnings[] = "⚠️  APP_URL does not use HTTPS - Recommended for production";
}

// Display results
echo "PASSED CHECKS:\n";
foreach ($passed as $check) {
    echo "  {$check}\n";
}

echo "\n";

if (!empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  {$warning}\n";
    }
    echo "\n";
}

if (!empty($issues)) {
    echo "CRITICAL ISSUES:\n";
    foreach ($issues as $issue) {
        echo "  {$issue}\n";
    }
    echo "\n";
    exit(1);
}

if (empty($issues) && empty($warnings)) {
    echo "🎉 All security checks passed!\n";
} else {
    echo "⚠️  Please review warnings and fix critical issues before deploying to production.\n";
}
