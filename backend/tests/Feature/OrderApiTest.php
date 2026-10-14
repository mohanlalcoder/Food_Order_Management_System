<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function menuItem(array $attributes = []): MenuItem
    {
        return MenuItem::create(array_merge([
            'name' => 'Test Pizza',
            'description' => 'A delicious test pizza',
            'price' => 200.00,
            'image_url' => null,
            'is_available' => true,
        ], $attributes));
    }

    private function validOrderPayload(MenuItem $item): array
    {
        return [
            'customer_name' => 'Mohan',
            'address' => '123 Siva Street, Chennai',
            'phone' => '+919876543210',
            'items' => [
                [
                    'menu_item_id' => $item->id,
                    'quantity' => 2,
                ],
            ],
        ];
    }

    public function test_menu_endpoint_returns_available_items(): void
    {
        $this->menuItem();

        $this->menuItem([
            'name' => 'Unavailable Pizza',
            'is_available' => false,
        ]);

        $response = $this->getJson('/api/menu');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Test Pizza');
    }

    public function test_customer_can_place_an_order(): void
    {
        $item = $this->menuItem();

        $response = $this->postJson(
            '/api/orders',
            $this->validOrderPayload($item)
        );

        $response->assertCreated()
            ->assertJsonPath('data.status', 'received')
            ->assertJsonPath('data.total_amount', '400.00');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Mohan',
            'total_amount' => '400.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'menu_item_id' => $item->id,
            'quantity' => 2,
            'unit_price' => '200.00',
        ]);
    }

    public function test_customer_cannot_submit_an_empty_order(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Mohan',
            'address' => '123 Example Street, Chennai',
            'phone' => '9876543210',
            'items' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('items');
    }

    public function test_server_does_not_trust_client_prices(): void
    {
        $item = $this->menuItem(['price' => 200.00]);

        $payload = $this->validOrderPayload($item);
        $payload['total_amount'] = 1;
        $payload['items'][0]['price'] = 0.01;

        $response = $this->postJson('/api/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.total_amount', '400.00');
    }

    public function test_unavailable_items_cannot_be_ordered(): void
    {
        $item = $this->menuItem(['is_available' => false]);

        $response = $this->postJson(
            '/api/orders',
            $this->validOrderPayload($item)
        );

        $response->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_can_be_retrieved(): void
    {
        $item = $this->menuItem();

        $created = $this->postJson(
            '/api/orders',
            $this->validOrderPayload($item)
        )->assertCreated();

        $id = $created->json('data.id');

        $this->getJson("/api/orders/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id);
    }

    public function test_order_status_can_be_updated(): void
    {
        $item = $this->menuItem();

        $created = $this->postJson(
            '/api/orders',
            $this->validOrderPayload($item)
        )->assertCreated();

        $id = $created->json('data.id');

        $this->patchJson("/api/orders/{$id}/status", [
            'status' => 'preparing',
        ])->assertOk()
          ->assertJsonPath('data.status', 'preparing');
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $item = $this->menuItem();

        $created = $this->postJson(
            '/api/orders',
            $this->validOrderPayload($item)
        )->assertCreated();

        $id = $created->json('data.id');

        $this->patchJson("/api/orders/{$id}/status", [
            'status' => 'delivered',
        ])->assertUnprocessable();
    }

    public function test_order_can_be_deleted(): void
    {
        $item = $this->menuItem();

        $created = $this->postJson(
            '/api/orders',
            $this->validOrderPayload($item)
        )->assertCreated();

        $id = $created->json('data.id');

        $this->deleteJson("/api/orders/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('orders', ['id' => $id]);
    }
}