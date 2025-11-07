<?php
declare(strict_types=1);

namespace App\Model;

use PDO;
use PDOException;

final class Role
{
    /**
     * Return granted permission keys for the given user.
     * Looks up role_id from users.role_id if available; otherwise tries user↔role pivots.
     * Reads from core_db.
     */
    public static function permissionsForUser(int $userId): array
    {
        $pdo = DB::core();

        $grants = [];
        $roleId = self::detectUserRoleId($pdo, $userId);

        if ($roleId !== null) {
            foreach (self::fetchRolePermissions($pdo, $roleId) as $row) {
                if (!isset($row['permission_key'])) {
                    continue;
                }

                $key = (string)$row['permission_key'];
                if ((int)$row['granted'] !== 0) {
                    $grants[$key] = true;
                }
            }
        }

        foreach (self::fetchUserOverrides($pdo, $userId) as $row) {
            if (!isset($row['permission_key'])) {
                continue;
            }

            $key = (string)$row['permission_key'];
            if ((int)$row['granted'] !== 0) {
                $grants[$key] = true;
            } else {
                unset($grants[$key]);
            }
        }

        ksort($grants);

        return array_keys($grants);
    }

    public static function metadataForId(int $roleId): ?array
    {
        if ($roleId <= 0) {
            return null;
        }

        $pdo = DB::core();

        try {
            $stmt = $pdo->prepare('SELECT id, key_slug, label FROM roles WHERE id = :rid LIMIT 1');
            $stmt->bindValue(':rid', $roleId, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        } catch (PDOException $e) {
            error_log('RBAC: failed to load role metadata – ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Try to get role_id for a user:
     *   a) users.role_id
     *   b) user_role(user_id, role_id) pivot
     *   c) role_user(user_id, role_id) pivot
     */
    private static function detectUserRoleId(PDO $pdo, int $userId): ?int
    {
        $lookups = [
            ['sql' => 'SELECT role_id FROM users WHERE id = :uid LIMIT 1'],
            ['sql' => 'SELECT role_id FROM user_role WHERE user_id = :uid LIMIT 1'],
            ['sql' => 'SELECT role_id FROM role_user WHERE user_id = :uid LIMIT 1'],
        ];

        foreach ($lookups as $lookup) {
            try {
                $stmt = $pdo->prepare($lookup['sql']);
                $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
                $stmt->execute();

                $rid = $stmt->fetchColumn();
                if ($rid !== false && $rid !== null) {
                    return (int)$rid;
                }
            } catch (PDOException $e) {
                error_log('RBAC: role lookup failed – ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Load permissions granted to a role. Failures are logged and treated as empty.
     *
     * @return array<int, array{permission_key:string, granted:mixed}>
     */
    private static function fetchRolePermissions(PDO $pdo, int $roleId): array
    {
        try {
            $stmt = $pdo->prepare('SELECT permission_key, COALESCE(granted, 1) AS granted FROM role_permissions WHERE role_id = :rid');
            $stmt->bindValue(':rid', $roleId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('RBAC: failed to read role permissions – ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Load per-user overrides (grants and revokes).
     *
     * @return array<int, array{permission_key:string, granted:mixed}>
     */
    private static function fetchUserOverrides(PDO $pdo, int $userId): array
    {
        try {
            $stmt = $pdo->prepare('SELECT permission_key, COALESCE(granted, 1) AS granted FROM user_permissions WHERE user_id = :uid');
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('RBAC: failed to read user overrides – ' . $e->getMessage());
            return [];
        }
    }
}
