<?php
declare(strict_types=1);

namespace App\Security;

final class Passwords
{
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public static function strongEnough(string $password): bool
    {
        $length = strlen($password);
        $classes = 0;
        $classes += preg_match('/[a-z]/', $password) ? 1 : 0;
        $classes += preg_match('/[A-Z]/', $password) ? 1 : 0;
        $classes += preg_match('/[0-9]/', $password) ? 1 : 0;
        $classes += preg_match('/[^a-zA-Z0-9]/', $password) ? 1 : 0;
        return $length >= 12 && $classes >= 3;
    }
}
