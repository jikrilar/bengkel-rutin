<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.notifications.index', [
            'notifications' => $request->user()
                ->notifications()
                ->latest()
                ->paginate(15),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $this->notificationFor($request, $notification)->markAsRead();

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $record = $this->notificationFor($request, $notification);
        $record->markAsRead();
        $target = $record->data['target_url'] ?? null;

        if (is_string($target) && str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return redirect($target);
        }

        return redirect()->route('notifications.index');
    }

    private function notificationFor(Request $request, string $id): DatabaseNotification
    {
        return $request->user()->notifications()->whereKey($id)->firstOrFail();
    }
}
