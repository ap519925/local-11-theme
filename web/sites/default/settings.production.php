<?php
/**
 * @file
 * Production settings for WHM/cPanel server.
 *
 * This file is included by settings.php when the .env file
 * contains production database credentials.
 */

// Load environment variables from .env if available
$env_file = dirname(DRUPAL_ROOT) . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#'))
            continue;
        if (strpos($line, '=') === false)
            continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Database configuration
$databases['default']['default'] = [
    'database' => getenv('DB_NAME') ?: 'drupal',
    'username' => getenv('DB_USER') ?: 'drupal',
    'password' => getenv('DB_PASS') ?: '',
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'driver' => 'mysql',
    'prefix' => '',
    'collation' => 'utf8mb4_general_ci',
];

// Hash salt
if (getenv('DRUPAL_HASH_SALT')) {
    $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT');
}

// Trusted host patterns - UPDATE with your actual domain
$settings['trusted_host_patterns'] = [
    '^ibew\.yourdomain\.com$',
    '^www\.ibew\.yourdomain\.com$',
    '^localhost$',
];

// Config sync directory
$settings['config_sync_directory'] = '../config/sync';

// Performance settings for production
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
$config['system.logging']['error_level'] = 'hide';

// File system
$settings['file_private_path'] = '../private';
