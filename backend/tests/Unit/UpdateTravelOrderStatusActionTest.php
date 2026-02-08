<?php

namespace Tests\Unit;

use App\Actions\UpdateTravelOrderStatusAction;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateTravelOrderStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_cancel_approved_order(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $action = new UpdateTravelOrderStatusAction();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Não é possível cancelar um pedido já aprovado.');

        $action->execute($order, 'canceled');
    }

    public function test_can_update_status(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status' => 'requested',
        ]);

        $action = new UpdateTravelOrderStatusAction();
        $updated = $action->execute($order, 'approved');

        $this->assertSame('approved', $updated->status);
    }
}
