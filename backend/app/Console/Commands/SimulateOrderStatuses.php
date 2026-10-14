<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class SimulateOrderStatuses extends Command
{
    protected $signature = 'orders:simulate-statuses';

    protected $description = 'Advance active orders for demo purposes';

    public function handle(): int
    {
        $transitions = [
            'received' => 'preparing',
            'preparing' => 'out_for_delivery',
            'out_for_delivery' => 'delivered',
        ];

        Order::query()
            ->whereIn('status', array_keys($transitions))
            ->where('created_at', '<=', now()->subMinute())
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($transitions) {
                foreach ($orders as $order) {
                    $nextStatus = $transitions[$order->status];

                    $order->update(['status' => $nextStatus]);

                    $this->info(
                        "Order #{$order->id}: {$nextStatus}"
                    );
                }
            });

        return self::SUCCESS;
    }
}