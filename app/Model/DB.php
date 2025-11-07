<?php
declare(strict_types=1);

namespace App\Model;

use PDO;
use PDOException;

final class DB
{
    private static array $config;
    private static ?PDO $core = null;
    private static ?PDO $ops = null;

    public static function config(array $config): void
    {
        self::$config = $config['databases'];
    }

    public static function core(): PDO
    {
        if (!self::$core) {
            self::$core = self::connect(self::$config['core']);
        }
        return self::$core;
    }

    public static function ops(): PDO
    {
        if (!self::$ops) {
            self::$ops = self::connect(self::$config['punchlist']);
        }
        return self::$ops;
    }

    private static function connect(array $config): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $config['host'], $config['port'], $config['name'], $config['charset']);
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }
}
