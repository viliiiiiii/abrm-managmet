<?php
declare(strict_types=1);

namespace App;

use App\Security\Headers;
use App\Security\Csrf;
use App\Auth\Auth;
use App\Security\RateLimiter;
use App\Model\DB;

require_once __DIR__ . '/../vendor_stub.php';

class Bootstrap
{
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config.php';
        date_default_timezone_set($this->config['app']['timezone']);
        $this->startSession();
        DB::config($this->config);
        Headers::apply($this->config);
        Csrf::bootstrap($this->config);
        RateLimiter::bootstrap($this->config);
        Auth::bootstrap($this->config);
    }
private function startSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    // Detect HTTPS: direct or via proxy
    $httpsServer = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $httpsProxy  = !empty($this->config['security']['trust_proxy'])
        && isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
        && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';

    $isHttps = $httpsServer || $httpsProxy;

    // Allow hard override from config
    if (array_key_exists('force_https', $this->config['security'])) {
        $isHttps = (bool)$this->config['security']['force_https'];
    }

    session_name($this->config['security']['session_name'] ?? 'abrm_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => $this->config['security']['cookie_domain'] ?? '',
        'secure'   => $isHttps,  // ← key change
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}



    public function config(): array
    {
        return $this->config;
    }
}
