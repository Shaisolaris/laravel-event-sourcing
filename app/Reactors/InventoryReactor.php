<?php

declare(strict_types=1);

namespace App\Reactors;

use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderCancelled;
use App\Events\Inventory\StockReserved;
use App\Events\Inventory\StockReleased;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class InventoryReactor extends Reactor
{
    public function onOrderPlaced(OrderPlaced $event): void
    {
        foreach ($event->items as $item) {
            event(new StockReserved(
                productId: $item['product_id'],
                orderId: $event->orderId,
                quantity: $item['quantity'],
            ));

            Log::info("[Inventory] Stock reserved", [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'order_id' => $event->orderId,
            ]);
        }
    }

    public function onOrderCancelled(OrderCancelled $event): void
    {
        Log::info("[Inventory] Releasing stock for cancelled order", [
            'order_id' => $event->orderId,
        ]);
        // In production: look up order items and release each
    }
}
