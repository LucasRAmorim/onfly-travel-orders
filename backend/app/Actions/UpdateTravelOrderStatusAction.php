<?php

namespace App\Actions;

use App\Models\TravelOrder;
use Illuminate\Validation\ValidationException;

class UpdateTravelOrderStatusAction
{
    public function execute(TravelOrder $order, string $newStatus): TravelOrder
    {
        if ($newStatus === 'canceled' && $order->status === 'approved') {
            throw ValidationException::withMessages([
                'status' => ['Não é possível cancelar um pedido já aprovado.'],
            ]);
        }

        $order->status = $newStatus;
        $order->save();

        return $order;
    }
}
