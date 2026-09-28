<?php
declare(strict_types=1);

$defaults = [
    'database' => [
        'host' => getenv('SHM_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('SHM_DB_PORT') ?: '3306',
        'name' => getenv('SHM_DB_NAME') ?: 'smart_health_monitoring',
        'username' => getenv('SHM_DB_USER') ?: 'root',
        'password' => getenv('SHM_DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    'session_secure' => filter_var(getenv('SHM_SESSION_SECURE') ?: '0', FILTER_VALIDATE_BOOL),
];

$localFile = __DIR__ . '/local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (!is_array($local)) {
        throw new RuntimeException('The local configuration file must return an array.');
    }
    $defaults = array_replace_recursive($defaults, $local);
}

return $defaults;
