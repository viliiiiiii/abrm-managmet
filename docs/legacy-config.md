# Legacy Configuration Mapping

The legacy `config.php` exposes constants that legacy scripts expect. To maintain backward compatibility inside the Laravel codebase, replicate the relevant keys in `config/legacy.php` and expose them through a helper class.

## Example mapping

```php
<?php

return [
    'paths' => [
        'uploads' => env('LEGACY_UPLOAD_PATH', storage_path('app/legacy/uploads')),
    ],
    'database' => [
        'core' => [
            'name' => env('DB_CORE_DATABASE'),
            'user' => env('DB_CORE_USERNAME'),
        ],
        'punchlist' => [
            'name' => env('DB_PUNCHLIST_DATABASE'),
            'user' => env('DB_PUNCHLIST_USERNAME'),
        ],
    ],
];
```

Expose values via `app/Support/LegacyConfig.php`:

```php
<?php

namespace App\Support;

class LegacyConfig
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return config("legacy.{$key}", $default);
    }
}
```

Ensure existing scripts can include `bootstrap/legacy.php` that instantiates the Laravel application (via `app()->instance()`) before referencing these constants.
