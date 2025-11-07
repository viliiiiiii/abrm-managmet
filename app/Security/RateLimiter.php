<?php
declare(strict_types=1);

namespace App\Security;

use RuntimeException;

final class RateLimiter
{
    private static array $config;
    private static bool $apcuAvailable;

    public static function bootstrap(array $config): void
    {
        self::$config = $config['security']['rate_limit'];
        self::$apcuAvailable = function_exists('apcu_fetch') && ini_get('apc.enabled');
    }

    public static function hit(string $key): bool
    {
        $window = self::$config['window'];
        $max = self::$config['max_attempts'];
        $now = time();

        if (self::$apcuAvailable) {
            $bucket = sprintf('rate:%s', $key);
            $entry = apcu_fetch($bucket);
            if (!$entry) {
                apcu_store($bucket, ['count' => 1, 'expires' => $now + $window], $window);
                return true;
            }
            if ($entry['expires'] < $now) {
                apcu_store($bucket, ['count' => 1, 'expires' => $now + $window], $window);
                return true;
            }
            if ($entry['count'] >= $max) {
                return false;
            }
            $entry['count']++;
            apcu_store($bucket, $entry, $window);
            return true;
        }

        if (!isset($_SESSION['rate'])) {
            $_SESSION['rate'] = [];
        }
        $bucket = &$_SESSION['rate'][$key];
        if (!isset($bucket) || $bucket['expires'] < $now) {
            $bucket = ['count' => 1, 'expires' => $now + $window];
            return true;
        }
        if ($bucket['count'] >= $max) {
            return false;
        }
        $bucket['count']++;
        return true;
    }
}
