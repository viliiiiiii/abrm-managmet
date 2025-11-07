<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'ABRM-Managment',
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'frontend_urls' => array_filter(array_map('trim', explode(',', getenv('FRONTEND_URLS') ?: ''))),
        'env' => getenv('APP_ENV') ?: 'local',
        'debug' => (bool)(getenv('APP_DEBUG') ?: false),
        'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
    ],
    'security' => [
        'session_name' => getenv('SESSION_NAME') ?: 'abrm_session',
        'csrf_name' => '_token',
        'rate_limit' => [
            'window' => (int)(getenv('RATE_LIMIT_WINDOW') ?: 60),
            'max_attempts' => (int)(getenv('RATE_LIMIT_MAX') ?: 120),
        ],
        'csp' => [
            'default' => "default-src 'self'",
        ],
    ],
    'databases' => [
        'core' => [
            'host' => getenv('CORE_DB_HOST') ?: '127.0.0.1',
            'port' => (int)(getenv('CORE_DB_PORT') ?: 3306),
            'name' => getenv('CORE_DB_NAME') ?: 'core_db',
            'user' => getenv('CORE_DB_USER') ?: 'root',
            'pass' => getenv('CORE_DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ],
        'punchlist' => [
            'host' => getenv('PUNCH_DB_HOST') ?: '127.0.0.1',
            'port' => (int)(getenv('PUNCH_DB_PORT') ?: 3306),
            'name' => getenv('PUNCH_DB_NAME') ?: 'punchlist',
            'user' => getenv('PUNCH_DB_USER') ?: 'root',
            'pass' => getenv('PUNCH_DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ],
    ],
    'minio' => [
        'endpoint' => getenv('MINIO_ENDPOINT') ?: 'http://127.0.0.1:9000',
        'access_key' => getenv('MINIO_ACCESS_KEY') ?: 'minioadmin',
        'secret_key' => getenv('MINIO_SECRET_KEY') ?: 'minioadmin',
        'region' => getenv('MINIO_REGION') ?: 'us-east-1',
        'bucket_uploads' => getenv('MINIO_BUCKET_UPLOADS') ?: 'abrm-uploads',
        'bucket_exports' => getenv('MINIO_BUCKET_EXPORTS') ?: 'abrm-exports',
        'path_style' => filter_var(getenv('MINIO_PATH_STYLE') ?: 'true', FILTER_VALIDATE_BOOL),
        'use_https' => stripos((getenv('MINIO_ENDPOINT') ?: ''), 'https://') === 0,
    ],
    'push' => [
        'vapid_public' => getenv('PUSH_VAPID_PUBLIC') ?: null,
        'vapid_private' => getenv('PUSH_VAPID_PRIVATE') ?: null,
    ],
];
