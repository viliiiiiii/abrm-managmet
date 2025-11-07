<?php
declare(strict_types=1);

namespace App\Model;

use PDO;
use PDOException;
use RuntimeException;

final class DB
{
    /** @var array{core:array, punchlist:array} */
    private static array $config;
    private static ?PDO $core = null;
    private static ?PDO $ops  = null;

    /**
     * Pass the full app config array; we read ['databases'].
     */
    public static function config(array $config): void
    {
        if (!isset($config['databases']['core'], $config['databases']['punchlist'])) {
            throw new RuntimeException('DB config missing "core" or "punchlist" section.');
        }
        self::$config = $config['databases'];
    }

    /**
     * PDO for core_db (auth/roles/sessions/audit)
     */
    public static function core(): PDO
    {
        if (!self::$core instanceof PDO) {
            self::$core = self::connect(self::$config['core']);
        }
        return self::$core;
    }

    /**
     * PDO for punchlist (ops: tasks/inventory/notes/sectors/exports)
     */
    public static function ops(): PDO
    {
        if (!self::$ops instanceof PDO) {
            self::$ops = self::connect(self::$config['punchlist']);
        }
        return self::$ops;
    }

    /**
     * Internal connector with sane defaults (utf8mb4, real prepares, errors as exceptions).
     *
     * @param array{host:string,port:int,name:string,user:string,pass:string,charset:string} $config
     */
    private static function connect(array $config): PDO
    {
        $host    = $config['host']    ?? '127.0.0.1';
        $port    = (int)($config['port'] ?? 3306);
        $db      = $config['name']    ?? '';
        $user    = $config['user']    ?? '';
        $pass    = $config['pass']    ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // use real prepared statements
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            // Initialize proper collation
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci",
            // Optional: uncomment to cap connection wait time
            // PDO::ATTR_TIMEOUT            => 5,
            // Optional: enable if you really want persistent conns
            // PDO::ATTR_PERSISTENT         => true,
        ];

        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Log detailed cause for ops, but throw a safe message up the stack
            error_log("DB CONNECT ERROR to {$db}@{$host}:{$port} – " . $e->getMessage());
            throw new RuntimeException('Database connection failed. Check credentials/GRANTs and that the pdo_mysql extension is installed.');
        }
    }

    /**
     * Lightweight pings for /healthz or diagnostics.
     */
    public static function pingCore(): bool
    {
        try { self::core()->query('SELECT 1'); return true; } catch (\Throwable) { return false; }
    }

    public static function pingOps(): bool
    {
        try { self::ops()->query('SELECT 1'); return true; } catch (\Throwable) { return false; }
    }
}
