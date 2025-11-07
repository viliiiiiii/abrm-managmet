<?php
declare(strict_types=1);

namespace App\Model;

final class Audit
{
    public static function log(int $userId, string $entity, string $action, array $meta = []): void
    {
        $stmt = DB::core()->prepare('INSERT INTO audit_logs (user_id, entity, action, ip_address, user_agent, meta, created_at) VALUES (:user_id, :entity, :action, :ip, :ua, :meta, NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'entity' => $entity,
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'meta' => json_encode($meta, JSON_THROW_ON_ERROR),
        ]);
    }
}
