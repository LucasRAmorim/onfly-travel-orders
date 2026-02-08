<?php

namespace Database\Seeders;

use App\Models\Airport;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TravelOrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();
        if ($users->isEmpty()) {
            return;
        }

        $airports = Airport::query()->get();
        $statuses = ['requested', 'approved', 'canceled'];

        for ($i = 0; $i < 15; $i++) {
            $user = $users->random();
            $departure = Carbon::now()->addDays(rand(1, 60));
            $return = (clone $departure)->addDays(rand(2, 7));

            $destination = 'Destino indefinido';
            if ($airports->isNotEmpty()) {
                $airport = $airports->random();
                $code = $airport->iata_code ?: $airport->icao_code;
                $base = $airport->city . ' - ' . $airport->name;
                $destination = $code ? ($base . ' (' . $code . ')') : $base;
            }

            TravelOrder::create([
                'user_id' => $user->id,
                'requester_name' => $user->name,
                'destination' => $destination,
                'departure_date' => $departure->toDateString(),
                'return_date' => $return->toDateString(),
                'status' => $statuses[array_rand($statuses)],
            ]);
        }
    }
}
