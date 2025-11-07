<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Security\RateLimiter;

final class RateLimitMiddleware
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function handle(array $params, callable $next)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!RateLimiter::hit('ip:' . $ip)) {
            http_response_code(429);
            echo 'Too Many Requests';
            exit;
        }
        return $next();
    }
}
