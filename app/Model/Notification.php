<?php
declare(strict_types=1);

namespace App\Model;

use PDO;

use function json_encode;

final class Notification
{
    /**
     * Fetch recent notifications for a user from punchlist DB.
     * Adjust table/columns as per your schema.
     */
    public static function recent(int $userId, int $limit = 20): array
    {
        $pdo = DB::ops(); // <-- use punchlist, not core

        try {
            $stmt = $pdo->prepare("
                SELECT id, type, title, body, url, is_read, created_at, read_at
                FROM notifications
                WHERE user_id = :uid
                ORDER BY created_at DESC, id DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$row) {
                $row['id'] = isset($row['id']) ? (int)$row['id'] : null;
                $row['is_read'] = !empty($row['is_read']);
            }
            unset($row);
            return $rows;
        } catch (\PDOException $e) {
            // If table doesn't exist or other SQL issue, don't crash the page
            if (strpos($e->getMessage(), 'Base table or view not found') !== false) {
                error_log('NOTIFICATIONS_TABLE_MISSING in punchlist: ' . $e->getMessage());
                return [];
            }
            throw $e;
        }
    }

    public static function unreadCount(int $userId): int
    {
        $pdo = DB::ops();
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0');
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Base table or view not found') !== false) {
                error_log('NOTIFICATIONS_TABLE_MISSING in punchlist: ' . $e->getMessage());
                return 0;
            }
            throw $e;
        }
    }

    /**
     * Mark a notification as read (optional helper).
     */
    public static function markRead(int $id, int $userId): bool
    {
        $pdo = DB::ops();
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':id' => $id, ':uid' => $userId]);
    }

    /**
     * Create a notification (optional helper).
     */
    public static function create(
        int $userId,
        string $type,
        string $title,
        ?string $body = null,
        array $options = []
    ): int
    {
        $pdo = DB::ops();
        $stmt = $pdo->prepare("
            INSERT INTO notifications (
                user_id,
                actor_user_id,
                type,
                entity_type,
                entity_id,
                title,
                body,
                data,
                url,
                is_read,
                created_at
            ) VALUES (
                :uid,
                :actor_user_id,
                :type,
                :entity_type,
                :entity_id,
                :title,
                :body,
                :data,
                :url,
                :is_read,
                NOW()
            )
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':actor_user_id' => $options['actor_user_id'] ?? null,
            ':type' => $type,
            ':entity_type' => $options['entity_type'] ?? null,
            ':entity_id' => $options['entity_id'] ?? null,
            ':title' => $title,
            ':body' => $body,
            ':data' => isset($options['data']) ? json_encode($options['data'], \JSON_THROW_ON_ERROR) : null,
            ':url' => $options['url'] ?? null,
            ':is_read' => !empty($options['is_read']) ? 1 : 0,
        ]);
        return (int)$pdo->lastInsertId();
    }
}
