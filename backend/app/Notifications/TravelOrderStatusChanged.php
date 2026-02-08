<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TravelOrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public TravelOrder $travelOrder
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'status' => $this->travelOrder->status,
            'destination' => $this->travelOrder->destination,
            'departure_date' => $this->travelOrder->departure_date?->toDateString(),
            'return_date' => $this->travelOrder->return_date?->toDateString(),
        ];
    }
}
