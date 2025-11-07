<?php
declare(strict_types=1);

namespace App\Model;

final class Notification
{
    public static function unreadCount(int $userId): int
    {
        $stmt = DB::core()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :id AND read_at IS NULL');
        $stmt->execute(['id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function recent(int $userId, int $limit = 10): array
    {
        $stmt = DB::core()->prepare('SELECT * FROM notifications WHERE user_id = :id ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
