<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::query()
            ->where('user_id', auth()->id())
            ->latest('created_at')
            ->paginate(30);

        return view('admin.notifications.index', [
            'adminNav' => 'notifications',
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->update(['read_at' => now()]);

        $url = $notification->data['url'] ?? route('admin.notifications.index');

        return redirect($url);
    }

    public function markAllRead(): RedirectResponse
    {
        Notification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les notifications ont été lues.');
    }
}
