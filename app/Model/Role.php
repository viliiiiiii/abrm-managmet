<?php
declare(strict_types=1);

namespace App\Model;

use PDO;

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

        // 1) Resolve user's role_id
        $roleId = self::detectUserRoleId($pdo, $userId);
        if ($roleId === null) {
            return [];
        }

        // 2) Ensure role_permissions exists
        $grants = [];

        if ($roleId !== null && self::tableExists($pdo, 'role_permissions')) {
            $sql = "
                SELECT permission_key, COALESCE(granted, 1) AS granted
                FROM role_permissions
                WHERE role_id = :rid
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':rid', $roleId, PDO::PARAM_INT);
            $stmt->execute();

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (!isset($row['permission_key'])) {
                    continue;
                }
                $key = (string)$row['permission_key'];
                if ((int)$row['granted'] !== 0) {
                    $grants[$key] = true;
                }
            }
        } elseif ($roleId !== null) {
            error_log('RBAC: core_db.role_permissions missing.');
        }

        // 4) Apply user-specific overrides if available
        if (self::tableExists($pdo, 'user_permissions')) {
            $sql = "
                SELECT permission_key, COALESCE(granted, 1) AS granted
                FROM user_permissions
                WHERE user_id = :uid
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
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
        }

        ksort($grants);

        return array_keys($grants);
    }

    /**
     * Try to get role_id for a user:
     *   a) users.role_id
     *   b) user_role(user_id, role_id) pivot
     *   c) role_user(user_id, role_id) pivot
     */
    private static function detectUserRoleId(PDO $pdo, int $userId): ?int
    {
        // a) users.role_id
        if (self::tableExists($pdo, 'users') && self::columnExists($pdo, 'users', 'role_id')) {
            $stmt = $pdo->prepare('SELECT role_id FROM users WHERE id = :uid LIMIT 1');
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $rid = $stmt->fetchColumn();
            if ($rid !== false && $rid !== null) {
                return (int)$rid;
            }
        }

        // b) user_role pivot
        if (self::tableExists($pdo, 'user_role')) {
            $stmt = $pdo->prepare('SELECT role_id FROM user_role WHERE user_id = :uid LIMIT 1');
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $rid = $stmt->fetchColumn();
            if ($rid !== false && $rid !== null) {
                return (int)$rid;
            }
        }

        // c) role_user pivot
        if (self::tableExists($pdo, 'role_user')) {
            $stmt = $pdo->prepare('SELECT role_id FROM role_user WHERE user_id = :uid LIMIT 1');
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $rid = $stmt->fetchColumn();
            if ($rid !== false && $rid !== null) {
                return (int)$rid;
            }
        }

        return null;
    }

    /**
     * Does a table exist in the current database? (uses INFORMATION_SCHEMA)
     */
    private static function tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("
            SELECT 1
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t
            LIMIT 1
        ");
        $stmt->execute([':t' => $table]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Does a column exist for a given table? (uses INFORMATION_SCHEMA)
     */
    private static function columnExists(PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("
            SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c
            LIMIT 1
        ");
        $stmt->execute([':t' => $table, ':c' => $column]);
        return (bool)$stmt->fetchColumn();
    }
}
