<?php
namespace App\Http\Controllers;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index(Request $request, $limit = 10, $filter = 'all')
    {
        $user   = Auth::user();
        $length = $user->unreadNotifications()->count(); // عدد الإشعارات غير المقروءة

        switch ($filter) {
            case 'read':
                $notifications = $user->readNotifications()->take($limit)->get();
                break;

            case 'unread':
                $notifications = $user->unreadNotifications()->take($limit)->get();
                break;

            default: // all
                $notifications = $user->notifications()->take($limit)->get();
                break;
        }

        return HelperFunc::sendResponse(200, 'done', [
            'notifications' => $notifications,
            'length'        => $length,
        ]);
    }

    public function markAllAsRead()
    {
        try {
            // تحديث جميع الإشعارات غير المقروءة دفعة واحدة باستخدام كويري مباشر
            Auth::user()->unreadNotifications()->update(['read_at' => now()]);

            return HelperFunc::sendResponse(200, 'All notifications marked as read', []);
        } catch (\Throwable $e) {
            \Log::error("Failed to mark all notifications as read: " . $e->getMessage());
            return HelperFunc::sendResponse(500, 'Failed to mark notifications as read', []);
        }
    }

    public function update($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if (! $notification) {
            return HelperFunc::sendResponse(404, 'Notification not found', []);
        }

        try {
            $notification->markAsRead();
            return HelperFunc::sendResponse(200, 'Notification marked as read', $notification);
        } catch (\Throwable $e) {
            \Log::error("Failed to mark notification as read: " . $e->getMessage());
            return HelperFunc::sendResponse(500, 'Failed to mark notification as read', []);
        }
    }

    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if (! $notification) {
            return HelperFunc::sendResponse(404, 'Notification not found', []);
        }

        try {
            $notification->delete();
            return HelperFunc::sendResponse(200, 'Notification deleted successfully', $notification);
        } catch (\Throwable $e) {
            \Log::error("Delete notification failed: " . $e->getMessage());
            return HelperFunc::sendResponse(500, 'Failed to delete notification', []);
        }
    }
}
