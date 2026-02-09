<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Atributos que devem ser ocultados na serialização.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Retorna os casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Indica se o usuario possui papel de admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Relacionamento com pedidos de viagem.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function travelOrders()
    {
        return $this->hasMany(\App\Models\TravelOrder::class);
    }

    /**
     * Localiza um usuario pelo email.
     *
     * @param  string  $email
     * @return self|null
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }

    /**
     * Retorna as ultimas notificacoes do usuario.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function latestNotifications(int $limit = 10): Collection
    {
        return $this->notifications()
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Retorna a quantidade de notificacoes nao lidas.
     *
     * @return int
     */
    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Marca uma notificacao como lida e retorna o modelo atualizado.
     *
     * @param  string  $notificationId
     * @return \Illuminate\Notifications\DatabaseNotification
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function markNotificationAsReadById(string $notificationId): DatabaseNotification
    {
        $notification = $this->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return $notification;
    }
}
