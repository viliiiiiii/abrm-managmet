<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Auth;
use App\Security\Csrf;
use App\Security\RateLimiter;
use App\Util\Validator;

final class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('login');
    }

    public function login(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!RateLimiter::hit('login:' . $ip)) {
            http_response_code(429);
            echo 'Too many attempts';
            return;
        }

        $data = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'totp' => $_POST['totp'] ?? null,
        ];
        $errors = Validator::require($data, ['email' => 'required|string', 'password' => 'required|string']);
        if ($errors) {
            $_SESSION['errors'] = $errors;
            header('Location: /login');
            return;
        }

        if (!Auth::attempt($data['email'], $data['password'], $data['totp'])) {
            $_SESSION['errors'] = ['auth' => ['Invalid credentials or 2FA code']];
            header('Location: /login');
            return;
        }

        header('Location: /');
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
    }
}
