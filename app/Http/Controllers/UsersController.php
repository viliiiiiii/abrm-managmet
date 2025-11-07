<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Permissions;
use App\Auth\Auth;
use App\Model\User;
use App\Model\Role;

final class UsersController extends Controller
{
    public function index(): string
    {
        Permissions::authorize('users.view');
        $stmt = \App\Model\DB::core()->query('SELECT u.id, u.name, u.email, r.name AS role_name FROM users u LEFT JOIN user_role ur ON ur.user_id = u.id LEFT JOIN roles r ON r.id = ur.role_id ORDER BY u.name');
        $users = $stmt->fetchAll();
        return $this->view('users/index', ['users' => $users]);
    }

    public function profile(): string
    {
        $user = Auth::user();
        return $this->view('profile', ['user' => $user]);
    }
}
