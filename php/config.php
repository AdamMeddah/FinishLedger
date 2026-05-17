<?php

function app_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = [
        'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
        'db_name' => getenv('DB_NAME') ?: 'finishledger',
        'db_user' => getenv('DB_USER') ?: 'root',
        'db_pass' => getenv('DB_PASS') ?: '',
        'db_charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        'app_env' => getenv('APP_ENV') ?: 'development',
    ];

    $localConfigPath = __DIR__ . '/config.local.php';
    if (file_exists($localConfigPath)) {
        $localConfig = require $localConfigPath;
        if (is_array($localConfig)) {
            $config = array_merge($config, $localConfig);
        }
    }

    return $config;
}
