<?php

namespace Tests\Feature;

use App\Models\TravelOrder;
use App\Models\User;
use App\Notifications\TravelOrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_status(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = TravelOrder::factory()->create(['user_id' => $user->id]);

        $this->patchJson("/api/travel-orders/{$order->id}/status", ['status' => 'approved'])
            ->assertUnauthorized();
    }

    public function test_admin_can_approve_and_notifies_user(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $order = TravelOrder::create([
            'user_id' => $user->id,
            'requester_name' => $user->name,
            'destination' => 'São Paulo',
            'departure_date' => '2026-03-01',
            'return_date' => '2026-03-05',
            'status' => 'requested',
        ]);

        $token = $admin->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/travel-orders/{$order->id}/status", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        Notification::assertSentTo($user, TravelOrderStatusChanged::class);
    }

    public function test_admin_cannot_update_status_with_invalid_value(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $order = TravelOrder::factory()->create(['user_id' => $user->id]);

        $token = $admin->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/travel-orders/{$order->id}/status", ['status' => 'requested'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_non_admin_cannot_update_status(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = TravelOrder::factory()->create(['user_id' => $user->id]);

        $token = $user->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/travel-orders/{$order->id}/status", ['status' => 'approved'])
            ->assertForbidden();
    }

    public function test_cannot_cancel_if_already_approved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $order = TravelOrder::create([
            'user_id' => $user->id,
            'requester_name' => $user->name,
            'destination' => 'São Paulo',
            'departure_date' => '2026-03-01',
            'return_date' => '2026-03-05',
            'status' => 'approved',
        ]);

        $token = $admin->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/travel-orders/{$order->id}/status", ['status' => 'canceled'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }
}
