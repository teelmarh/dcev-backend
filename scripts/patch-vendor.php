<?php

/**
 * Vendor patch script — runs via Composer post-autoload-dump.
 *
 * Adds missing type casts to ichtrojan/laravel-otp's Otp model so that
 * SQL Server's string-typed integer columns don't cause Carbon TypeError.
 */

$file = __DIR__ . '/../vendor/ichtrojan/laravel-otp/src/Models/Otp.php';

if (!file_exists($file)) {
    echo "  [patch-vendor] ichtrojan/laravel-otp not found, skipping.\n";
    exit(0);
}

$content = file_get_contents($file);

if (strpos($content, 'protected $casts') !== false) {
    echo "  [patch-vendor] OTP model already patched, skipping.\n";
    exit(0);
}

$patch = <<<'PHP'
    protected $casts = [
        'validity' => 'integer',
        'valid'    => 'boolean',
    ];

    PHP;

$content = str_replace('    protected $fillable', $patch . '    protected $fillable', $content);

file_put_contents($file, $content);

echo "  [patch-vendor] Patched OTP model with integer/boolean casts.\n";
