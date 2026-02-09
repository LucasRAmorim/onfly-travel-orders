<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository
{
    /**
     * Lista notificacoes e contagem de nao lidas.
     *
     * @return array{notifications: Collection, unreadCount: int}
     */
    public function listFor(User $user, int $limit = 10, bool $onlyUnread = false): array
    {
        if ($user->isAdmin()) {
            return [
                'notifications' => new Collection(),
                'unreadCount' => 0,
            ];
        }

        $query = $onlyUnread ? $user->unreadNotifications() : $user->notifications();
        $query->latest();

        if ($limit > 0) {
            $query->take($limit);
        }

        return [
            'notifications' => $query->get(),
            'unreadCount' => $user->unreadNotificationsCount(),
        ];
    }

    /**
     * Marca uma notificacao como lida.
     *
     * @param  User  $user
     * @param  string  $notificationId
     * @return \Illuminate\Notifications\DatabaseNotification
     */
    public function markAsRead(User $user, string $notificationId): DatabaseNotification
    {
        return $user->markNotificationAsReadById($notificationId);
    }
}
