<?php

namespace Tests\Feature;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelOrderFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('api')->plainTextToken;

        $payload = [
            'requester_name' => $user->name,
            'destination' => 'Sao Paulo',
            'departure_date' => now()->addDays(3)->toDateString(),
            'return_date' => now()->addDays(6)->toDateString(),
        ];

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/travel-orders', $payload)
            ->assertCreated()
            ->assertJsonPath('data.destination', 'Sao Paulo');
    }

    public function test_admin_can_list_all_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        TravelOrder::factory()->count(2)->create(['user_id' => $admin->id]);
        TravelOrder::factory()->count(3)->create(['user_id' => $user->id]);

        $token = $admin->createToken('api')->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/travel-orders')
            ->assertOk()
            ->json('data.data');

        $this->assertCount(5, $res);
    }

    public function test_can_filter_by_status_and_destination(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status' => 'requested',
            'destination' => 'Rio de Janeiro',
        ]);

        TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'destination' => 'Sao Paulo',
        ]);

        $token = $user->createToken('api')->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/travel-orders?status=approved&destination=Sao')
            ->assertOk()
            ->json('data.data');

        $this->assertCount(1, $res);
        $this->assertSame('approved', $res[0]['status']);
    }

    public function test_can_filter_by_travel_date_range(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        TravelOrder::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2026-03-01',
            'return_date' => '2026-03-05',
        ]);

        TravelOrder::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2026-04-01',
            'return_date' => '2026-04-05',
        ]);

        $token = $user->createToken('api')->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/travel-orders?travel_from=2026-03-01&travel_to=2026-03-31')
            ->assertOk()
            ->json('data.data');

        $this->assertCount(1, $res);
        $this->assertSame('2026-03-01', substr($res[0]['departure_date'], 0, 10));
    }
}
