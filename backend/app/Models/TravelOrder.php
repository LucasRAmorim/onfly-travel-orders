<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requester_name',
        'destination',
        'departure_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
    ];

    /**
     * Relacionamento com o usuario solicitante.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Restringe pedidos ao usuario (exceto admin).
     *
     * @param  Builder  $query
     * @param  User  $user
     * @return Builder
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /**
     * Filtra pedidos por status.
     *
     * @param  Builder  $query
     * @param  string|null  $status
     * @return Builder
     */
    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Filtra pedidos por destino (parcial).
     *
     * @param  Builder  $query
     * @param  string|null  $destination
     * @return Builder
     */
    public function scopeFilterDestination(Builder $query, ?string $destination): Builder
    {
        if ($destination) {
            $query->where('destination', 'like', "%{$destination}%");
        }

        return $query;
    }

    /**
     * Filtra pedidos por intervalo de datas de viagem.
     *
     * @param  Builder  $query
     * @param  string|null  $travelFrom
     * @param  string|null  $travelTo
     * @return Builder
     */
    public function scopeFilterTravelDateRange(Builder $query, ?string $travelFrom, ?string $travelTo): Builder
    {
        if (! $travelFrom && ! $travelTo) {
            return $query;
        }

        $from = $travelFrom ?: '1900-01-01';
        $to = $travelTo ?: '2999-12-31';

        return $query->where(function ($q) use ($from, $to) {
            $q->whereBetween('departure_date', [$from, $to])
              ->orWhereBetween('return_date', [$from, $to])
              ->orWhere(function ($q2) use ($from, $to) {
                  $q2->where('departure_date', '<=', $from)
                     ->where('return_date', '>=', $to);
              });
        });
    }

    /**
     * Filtra pedidos por intervalo de criacao.
     *
     * @param  Builder  $query
     * @param  string|null  $createdFrom
     * @param  string|null  $createdTo
     * @return Builder
     */
    public function scopeFilterCreatedDateRange(Builder $query, ?string $createdFrom, ?string $createdTo): Builder
    {
        if ($createdFrom) {
            $query->whereDate('created_at', '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        return $query;
    }

    /**
     * Monta a query filtrada para o usuario.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $filters
     * @return Builder
     */
    public static function filteredQueryFor(User $user, array $filters): Builder
    {
        return static::query()
            ->forUser($user)
            ->filterStatus($filters['status'] ?? null)
            ->filterDestination($filters['destination'] ?? null)
            ->filterTravelDateRange($filters['travel_from'] ?? null, $filters['travel_to'] ?? null)
            ->filterCreatedDateRange($filters['created_from'] ?? null, $filters['created_to'] ?? null);
    }

    /**
     * Retorna pedidos paginados para o usuario.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function paginateFor(User $user, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return static::filteredQueryFor($user, $filters)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Retorna contagem de pedidos por status.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $filters
     * @return array<string, int>
     */
    public static function statusCountsFor(User $user, array $filters): array
    {
        return static::filteredQueryFor($user, $filters)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }

    /**
     * Cria um pedido de viagem para o usuario.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $data
     * @return self
     */
    public static function createForUser(User $user, array $data): self
    {
        return static::create([
            'user_id' => $user->id,
            'requester_name' => $data['requester_name'],
            'destination' => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'status' => 'requested',
        ]);
    }
}
