<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $requestedItems = collect($data['items']);

            $menuItems = MenuItem::query()
                ->whereIn(
                    'id',
                    $requestedItems->pluck('menu_item_id')
                )
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;

            foreach ($requestedItems as $requested) {
                $menuItem = $menuItems->get(
                    $requested['menu_item_id']
                );

                if (!$menuItem || !$menuItem->is_available) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'One or more selected menu items are unavailable.',
                        ],
                    ]);
                }

                $total += (float) $menuItem->price
                    * $requested['quantity'];
            }

            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'status' => 'received',
                'total_amount' => number_format($total, 2, '.', ''),
            ]);

            foreach ($requestedItems as $requested) {
                $menuItem = $menuItems->get(
                    $requested['menu_item_id']
                );

                $order->items()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $requested['quantity'],
                    'unit_price' => $menuItem->price,
                ]);
            }

            return $order->load('items.menuItem');
        });
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $allowedTransitions = [
            'received' => ['preparing', 'cancelled'],
            'preparing' => ['out_for_delivery', 'cancelled'],
            'out_for_delivery' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!in_array(
            $status,
            $allowedTransitions[$order->status] ?? [],
            true
        )) {
            throw ValidationException::withMessages([
                'status' => ['This status transition is not allowed.'],
            ]);
        }

        $order->update(['status' => $status]);

        return $order->load('items.menuItem');
    }
}