<?php
declare(strict_types=1);

namespace App\Auth;

use App\Model\Role;

final class Permissions
{
    public static function check(string $permission): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        return in_array($permission, Role::permissionsForUser($user->id), true);
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
