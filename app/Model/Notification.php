<?php
declare(strict_types=1);

namespace App\Model;

use PDO;

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
            // Example columns: id, user_id, title, body, created_at, read_at
            $stmt = $pdo->prepare("
                SELECT id, user_id, title, body, created_at, read_at
                FROM notifications
                WHERE user_id = :uid
                ORDER BY COALESCE(read_at, '9999-12-31') IS NULL DESC, created_at DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            // If table doesn't exist or other SQL issue, don't crash the page
            if (strpos($e->getMessage(), 'Base table or view not found') !== false) {
                error_log('NOTIFICATIONS_TABLE_MISSING in punchlist: ' . $e->getMessage());
                return [];
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
        $stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = :id AND user_id = :uid");
        return $stmt->execute([':id' => $id, ':uid' => $userId]);
    }

    /**
     * Create a notification (optional helper).
     */
    public static function create(int $userId, string $title, string $body): int
    {
        $pdo = DB::ops();
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, body, created_at)
            VALUES (:uid, :title, :body, NOW())
        ");
        $stmt->execute([':uid' => $userId, ':title' => $title, ':body' => $body]);
        return (int)$pdo->lastInsertId();
    }
}
