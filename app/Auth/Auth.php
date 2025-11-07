<?php
declare(strict_types=1);

namespace App\Auth;

use App\Model\DB;
use App\Model\User;
use App\Security\Csrf;

final class Auth
{
    /** @var array full app config */
    private static array $config;

    public static function bootstrap(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Attempt to authenticate a user using email/password.
     * - Reads from core_db.users (email, pass_hash)
     * - Verifies with password_verify
     * - Rehashes to Argon2id if the stored hash needs upgrade
     * - On success: regenerates session, stores user_id, seeds CSRF
     */
    public static function attempt(string $email, string $password): bool
    {
        $email = trim($email);

        // 1) Lookup user in core_db
        $pdo = DB::core();
        $stmt = $pdo->prepare('SELECT id, email, pass_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row || !is_array($row)) {
            return false; // user not found
        }

        $hash = (string)($row['pass_hash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return false; // invalid credentials
        }

        // 2) Optional: upgrade hash if needed (e.g., stronger cost/algorithm)
        if (password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 1 << 17,
            'time_cost'   => 4,
            'threads'     => 2,
        ])) {
            $new = password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 1 << 17,
                'time_cost'   => 4,
                'threads'     => 2,
            ]);
            $upd = $pdo->prepare('UPDATE users SET pass_hash = ? WHERE id = ?');
            $upd->execute([$new, (int)$row['id']]);
        }

        // 3) Success: rotate session & seed CSRF
        session_regenerate_id(true);
        $_SESSION['user_id']  = (int)$row['id'];
        $_SESSION['last_seen'] = time();

        // Ensure CSRF token exists after login
        Csrf::bootstrap(self::$config);

        return true;
    }

    /**
     * Destroy the current session and cookie.
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            // Expire the cookie
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool)$params['secure'],
                (bool)$params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Return the current authenticated user model or null.
     */
    public static function user(): ?User
    {
        $id = $_SESSION['user_id'] ?? null;
        return $id ? User::find((int)$id) : null;
    }

    /**
     * Whether a user is authenticated.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }
}
