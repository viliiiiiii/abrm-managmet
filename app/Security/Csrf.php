<?php
declare(strict_types=1);

namespace App\Security;

final class Csrf
{
    private static string $tokenKey;

    public static function bootstrap(array $config): void
    {
        self::$tokenKey = $config['security']['csrf_name'];
        if (!isset($_SESSION[self::$tokenKey])) {
            $_SESSION[self::$tokenKey] = bin2hex(random_bytes(32));
        }
    }

    public static function token(): string
    {
        return $_SESSION[self::$tokenKey] ?? '';
    }

    public static function field(): string
    {
        return self::$tokenKey;
    }

    public static function verify(?string $token): bool
    {
        return hash_equals(self::token(), (string)$token);
    }
}
