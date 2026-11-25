<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\ShippingService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerOrderCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_cancel_own_pending_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $shipping = ShippingService::create([
            'courier' => 'JNE',
            'service' => 'REG',
            'eta' => '2-4 hari',
            'cost' => 15000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'code' => 'ORD-TEST-001',
            'user_id' => $customer->id,
            'shipping_service_id' => $shipping->id,
            'shipping_courier' => 'JNE',
            'shipping_service' => 'REG',
            'shipping_eta' => '2-4 hari',
            'recipient_name' => 'Budi Customer',
            'phone' => '081234567890',
            'address' => 'Jl. Praktikum No. 10, Purwokerto',
            'subtotal' => 100000,
            'shipping_cost' => 15000,
            'grand_total' => 115000,
            'status' => 'pending',
            'ordered_at' => now(),
        ]);

        Sanctum::actingAs($customer);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_customer_cannot_cancel_confirmed_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $shipping = ShippingService::create([
            'courier' => 'JNE',
            'service' => 'REG',
            'eta' => '2-4 hari',
            'cost' => 15000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'code' => 'ORD-TEST-002',
            'user_id' => $customer->id,
            'shipping_service_id' => $shipping->id,
            'shipping_courier' => 'JNE',
            'shipping_service' => 'REG',
            'shipping_eta' => '2-4 hari',
            'recipient_name' => 'Budi Customer',
            'phone' => '081234567890',
            'address' => 'Jl. Praktikum No. 10, Purwokerto',
            'subtotal' => 100000,
            'shipping_cost' => 15000,
            'grand_total' => 115000,
            'status' => 'confirmed',
            'ordered_at' => now(),
        ]);

        Sanctum::actingAs($customer);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(409);
    }
}