<?php
declare(strict_types=1);

namespace App;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\RateLimitMiddleware;
use App\Util\Response;

class Router
{
    private array $routes = [];
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function add(string $method, string $pattern, callable $handler, array $middleware = []): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            if (strcasecmp($method, $route['method']) === 0 && preg_match($this->patternToRegex($route['pattern']), $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $middlewareStack = $this->resolveMiddleware($route['middleware']);
                $handler = $route['handler'];
                $response = $this->executeMiddleware($middlewareStack, $handler, $params);
                if (is_array($response) || is_object($response)) {
                    Response::json($response);
                } elseif (is_string($response)) {
                    echo $response;
                }
                return;
            }
        }

        http_response_code(404);
        echo 'Not Found';
    }

    private function patternToRegex(string $pattern): string
    {
        $escaped = preg_quote($pattern, '#');
        $regex = preg_replace('#\\\{([a-zA-Z_][a-zA-Z0-9_-]*)\\\}#', '(?P<$1>[^/]+)', $escaped);
        return '#^' . $regex . '$#';
    }

    private function resolveMiddleware(array $middleware): array
    {
        $resolved = [];
        foreach ($middleware as $item) {
            switch ($item) {
                case 'auth':
                    $resolved[] = new AuthMiddleware();
                    break;
                case 'csrf':
                    $resolved[] = new CsrfMiddleware($this->config);
                    break;
                case 'rate':
                    $resolved[] = new RateLimitMiddleware($this->config);
                    break;
                default:
                    if ($item instanceof \Closure) {
                        $resolved[] = $item;
                    }
                    break;
            }
        }
        return $resolved;
    }

    private function executeMiddleware(array $middleware, callable $handler, array $params)
    {
        $next = function () use (&$middleware, $handler, $params, &$next) {
            if ($mw = array_shift($middleware)) {
                return $mw->handle($params, $next);
            }
            return call_user_func_array($handler, $params);
        };

        return $next();
    }
}
