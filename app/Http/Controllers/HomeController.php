<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Auth;
use App\Model\Inventory;
use App\Model\Task;
use App\Model\Notification;

final class HomeController extends Controller
{
    public function index(): string
    {
        $user = Auth::user();
        $tasksByStatus = Task::countsByStatus();
        $lowStock = Inventory::lowStock();
        $notifications = $user ? Notification::recent($user->id) : [];

        return $this->view('dashboard', [
            'user' => $user,
            'tasksByStatus' => $tasksByStatus,
            'lowStock' => $lowStock,
            'notifications' => $notifications,
        ]);
    }
}
