<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Auth;
use App\Auth\Permissions;
use App\Model\Notification;
use App\Util\Response;

final class NotificationsController extends Controller
{
    public function unread(): void
    {
        Permissions::authorize('notifications.view');
        $user = Auth::user();
        if (!$user) {
            Response::problem('Unauthorized', 'Login required', 401);
            return;
        }
        Response::json([
            'data' => Notification::recent($user->id),
            'meta' => ['unread' => Notification::unreadCount($user->id)],
        ]);
    }
}
