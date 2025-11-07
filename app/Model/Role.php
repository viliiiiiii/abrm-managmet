<?php
declare(strict_types=1);

namespace App\Model;

final class Role
{
    public static function permissionsForUser(int $userId): array
    {
        $stmt = DB::core()->prepare('SELECT p.slug FROM permissions p JOIN role_permission rp ON rp.permission_id = p.id JOIN roles r ON r.id = rp.role_id JOIN user_role ur ON ur.role_id = r.id WHERE ur.user_id = :id');
        $stmt->execute(['id' => $userId]);
        return array_column($stmt->fetchAll(), 'slug');
    }
}
