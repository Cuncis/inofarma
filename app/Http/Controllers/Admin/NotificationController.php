<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The admin topbar bell's own two actions (Fase 8) — read individually when
 * a notification is opened, or cleared all at once from "Bersihkan".
 */
class NotificationController extends Controller
{
    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->where('id', $notification)->first()?->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
