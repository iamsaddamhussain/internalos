<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the authenticated user.
     */
    public function index(Request $request): Response
    {
        $notifications = Notification::with(['automation', 'record', 'workspace'])
            ->forUser(auth()->id())
            ->when($request->workspace_id, function ($query) use ($request) {
                $query->forWorkspace($request->workspace_id);
            })
            ->when($request->unread_only, function ($query) {
                $query->unread();
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => Notification::forUser(auth()->id())->unread()->count(),
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request)
    {
        $count = Notification::forUser(auth()->id())
            ->when($request->workspace_id, function ($query) use ($request) {
                $query->forWorkspace($request->workspace_id);
            })
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        $this->authorize('update', $notification);

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        Notification::forUser(auth()->id())
            ->when($request->workspace_id, function ($query) use ($request) {
                $query->forWorkspace($request->workspace_id);
            })
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }
}
