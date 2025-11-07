<?php
declare(strict_types=1);

namespace App\Auth;

use App\Model\User;
use App\Security\Csrf;

final class Auth
{
    private static array $config;

    public static function bootstrap(array $config): void
    {
        self::$config = $config;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user || !$user->verifyPassword($password)) {
            return false;
        }

        $_SESSION['user_id'] = $user->id;
        $_SESSION['last_seen'] = time();
        Csrf::bootstrap(self::$config);
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function user(): ?User
    {
        $id = $_SESSION['user_id'] ?? null;
        return $id ? User::find((int)$id) : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }
}
