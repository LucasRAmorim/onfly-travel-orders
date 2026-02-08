<?php

namespace Tests\Feature;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelOrderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_orders(): void
    {
        $this->getJson('/api/travel-orders')
            ->assertUnauthorized();
    }

    public function test_guest_cannot_create_order(): void
    {
        $this->postJson('/api/travel-orders', [])
            ->assertUnauthorized();
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $userA = User::factory()->create(['role' => 'user']);
        $userB = User::factory()->create(['role' => 'user']);

        $order = TravelOrder::factory()->create(['user_id' => $userB->id]);

        $token = $userA->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/travel-orders/{$order->id}")
            ->assertForbidden();
    }

    public function test_user_list_only_own_orders(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);

        TravelOrder::factory()->count(2)->create(['user_id' => $user->id]);
        TravelOrder::factory()->count(3)->create(['user_id' => $other->id]);

        $token = $user->createToken('api')->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/travel-orders')
            ->assertOk()
            ->json('data.data');

        $this->assertCount(2, $res);
    }
}
