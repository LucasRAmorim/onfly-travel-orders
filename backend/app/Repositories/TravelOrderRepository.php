<?php

namespace App\Repositories;

use App\Actions\UpdateTravelOrderStatusAction;
use App\Models\TravelOrder;
use App\Models\User;
use App\Notifications\TravelOrderStatusChanged;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TravelOrderRepository
{
    public function __construct(private UpdateTravelOrderStatusAction $statusAction)
    {
    }

    /**
     * Lista pedidos paginados e contagem por status.
     *
     * @return array{paginator: LengthAwarePaginator, counts: array<string,int>}
     */
    public function listFor(User $user, array $filters, int $perPage = 10): array
    {
        $paginator = TravelOrder::paginateFor($user, $filters, $perPage);
        $counts = TravelOrder::statusCountsFor($user, $filters);

        return [
            'paginator' => $paginator,
            'counts' => $counts,
        ];
    }

    /**
     * Cria um pedido para o usuario.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $data
     * @return TravelOrder
     */
    public function createForUser(User $user, array $data): TravelOrder
    {
        return TravelOrder::createForUser($user, $data);
    }

    /**
     * Atualiza o status do pedido e dispara notificacao.
     *
     * @param  TravelOrder  $order
     * @param  string  $status
     * @return TravelOrder
     */
    public function updateStatus(TravelOrder $order, string $status): TravelOrder
    {
        $updated = $this->statusAction->execute($order, $status);
        $updated->user->notify(new TravelOrderStatusChanged($updated));

        return $updated;
    }
}
