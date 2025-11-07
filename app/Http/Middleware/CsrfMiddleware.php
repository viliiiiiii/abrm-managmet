<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Security\Csrf;

final class CsrfMiddleware
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function handle(array $params, callable $next)
    {
        $token = $_POST[$this->config['security']['csrf_name']] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::verify($token)) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            exit;
        }
        return $next();
    }
}
