<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Auth\Auth;

final class AuthMiddleware
{
    public function handle(array $params, callable $next)
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        return $next();
    }
}
