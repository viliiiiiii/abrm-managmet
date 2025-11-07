<?php
declare(strict_types=1);

namespace App\Auth;

use App\Model\Role;

final class Permissions
{
    private const PERMISSION_ALIASES = [
        'tasks.view' => 'view',
        'tasks.export' => 'download',
        'exports.tasks' => 'download',
        'notes.view' => 'view',
        'photos.upload' => 'edit',
        'inventory.view' => 'inventory_manage',
        'inventory.export' => 'download',
        'notifications.view' => 'notifications_admin',
        'users.view' => 'manage_users',
    ];

    public static function check(string $permission): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        $normalized = self::PERMISSION_ALIASES[$permission] ?? $permission;
        return in_array($normalized, Role::permissionsForUser($user->id), true);
    }

    public static function authorize(string $permission): void
    {
        if (!self::check($permission)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }
}
