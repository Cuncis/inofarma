<?php

namespace App\Support\Presenters;

use Illuminate\Notifications\DatabaseNotification;

/**
 * The admin topbar's notification bell (Fase 8) — reads straight off each
 * `DatabaseNotification`'s `data` column, which every `Admin\*` notification
 * class already writes as `{title, body, link}` from its own `toDatabase()`.
 */
class AdminNotificationPresenter
{
    /**
     * @param  iterable<DatabaseNotification>  $notifications
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $notifications): array
    {
        return collect($notifications)->map(fn (DatabaseNotification $notification) => [
            'id' => $notification->id,
            'title' => $notification->data['title'] ?? 'Notifikasi',
            'body' => $notification->data['body'] ?? '',
            'link' => $notification->data['link'] ?? null,
            'read' => $notification->read_at !== null,
            'createdAt' => $notification->created_at?->diffForHumans(),
        ])->values()->all();
    }
}
