<?php
declare(strict_types=1);

/**
 * ----------------------------------------------------------------
 * Minimal dotenv loader (no Composer): loads .env into getenv/$_ENV
 * ----------------------------------------------------------------
 */
(function () {
    $envFile = __DIR__ . '/.env';
    if (!is_file($envFile) || !is_readable($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $raw) {
        $line = trim($raw);
        if ($line === '' || $line[0] === '#') continue;

        // Support "export KEY=VALUE"
        if (str_starts_with($line, 'export ')) {
            $line = substr($line, 7);
        }

        // Parse KEY=VALUE (VALUE may be quoted)
        if (!preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/i', $line, $m)) {
            continue;
        }
        $key = $m[1];
        $val = $m[2];

        // Strip surrounding quotes if present
        if ((strlen($val) >= 2) && (
                ($val[0] === '"' && $val[strlen($val) - 1] === '"') ||
                ($val[0] === "'" && $val[strlen($val) - 1] === "'")
            )) {
            $val = substr($val, 1, -1);
        }

        // Basic unescape for common sequences
        $val = str_replace(["\\n", "\\r", "\\t"], ["\n", "\r", "\t"], $val);

        // Do not overwrite values already present in the process env
        if (getenv($key) === false) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
})();

/**
 * --------------------------------------------------------
 * Optional: toggle PHP error reporting based on APP_DEBUG
 * --------------------------------------------------------
 * (Safe to do here since .env has just been loaded.)
 */
$debug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
}

/**
 * ----------------------------------------
 * Simple PSR-4 autoloader for the App\ ns
 * ----------------------------------------
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

/**
 * ---------------------------
 * Include bundled single-libs
 * ---------------------------
 */
$qrLib = __DIR__ . '/lib/phpqrcode.php';
if (is_file($qrLib)) {
    require_once $qrLib;
}
// If you bundled a PDF lib, include it here too (optional):
// $fpdf = __DIR__ . '/lib/fpdf/fpdf.php';
// if (is_file($fpdf)) require_once $fpdf;

/**
 * -------------------------------------------------------------------------
 * (Optional) Define legacy constants from the new array config for fallback
 * -------------------------------------------------------------------------
 * Some older code may still reference CORE_DSN/APPS_DSN/etc. If so, define
 * them from config.php once here. Safe to include config since it returns
 * an array (idempotent). If you don’t need this, you can remove this block.
 */
$defineLegacy = true; // set to false if you don’t want constants defined
if ($defineLegacy) {
    $cfgPath = __DIR__ . '/config.php';
    if (is_file($cfgPath) && is_readable($cfgPath)) {
        $cfg = require $cfgPath;
        if (is_array($cfg) && isset($cfg['databases'])) {
            // core_db
            if (!defined('CORE_DSN')) {
                $c = $cfg['databases']['core'] ?? null;
                if (is_array($c)) {
                    $dsn = sprintf(
                        'mysql:host=%s;dbname=%s;charset=%s',
                        $c['host'] ?? '127.0.0.1',
                        $c['name'] ?? 'core_db',
                        $c['charset'] ?? 'utf8mb4'
                    );
                    define('CORE_DSN', $dsn);
                    define('CORE_DB_USER', $c['user'] ?? 'root');
                    define('CORE_DB_PASS', $c['pass'] ?? '');
                }
            }
            // punchlist / ops
            if (!defined('APPS_DSN')) {
                $p = $cfg['databases']['punchlist'] ?? null;
                if (is_array($p)) {
                    $dsn = sprintf(
                        'mysql:host=%s;dbname=%s;charset=%s',
                        $p['host'] ?? '127.0.0.1',
                        $p['name'] ?? 'punchlist',
                        $p['charset'] ?? 'utf8mb4'
                    );
                    define('APPS_DSN', $dsn);
                    define('APPS_DB_USER', $p['user'] ?? 'root');
                    define('APPS_DB_PASS', $p['pass'] ?? '');
                }
            }
        }
    }
}

/**
 * -----------------------------------------------------------
 * Sensible defaults for encoding (helps with mb_* and output)
 * -----------------------------------------------------------
 */
if (function_exists('mb_internal_encoding')) {
    @mb_internal_encoding('UTF-8');
}
ini_set('default_charset', 'UTF-8');
