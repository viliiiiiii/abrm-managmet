<?php
declare(strict_types=1);

namespace App\Security;

final class Headers
{
    public static function apply(array $config): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
        header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        $csp = $config['security']['csp']['default'] ?? "default-src 'self'";
        header('Content-Security-Policy: ' . $csp);
    }
}
