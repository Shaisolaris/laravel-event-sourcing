<?php

declare(strict_types=1);

namespace App\Projectors;

use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderCancelled;
use App\Events\Order\OrderDelivered;
use App\Events\Order\OrderRefunded;
use App\Models\DailyOrderStats;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class OrderAnalyticsProjector extends Projector
{
    public function onOrderPlaced(OrderPlaced $event): void
    {
        $stats = DailyOrderStats::firstOrCreate(
            ['date' => now()->toDateString()],
            ['orders_placed' => 0, 'orders_cancelled' => 0, 'orders_delivered' => 0, 'revenue' => 0, 'refunds' => 0],
        );

        $stats->increment('orders_placed');
        $stats->increment('revenue', (int) round($event->total * 100));
    }

    public function onOrderCancelled(OrderCancelled $event): void
    {
        DailyOrderStats::firstOrCreate(
            ['date' => now()->toDateString()],
            ['orders_placed' => 0, 'orders_cancelled' => 0, 'orders_delivered' => 0, 'revenue' => 0, 'refunds' => 0],
        )->increment('orders_cancelled');
    }

    public function onOrderDelivered(OrderDelivered $event): void
    {
        DailyOrderStats::firstOrCreate(
            ['date' => now()->toDateString()],
            ['orders_placed' => 0, 'orders_cancelled' => 0, 'orders_delivered' => 0, 'revenue' => 0, 'refunds' => 0],
        )->increment('orders_delivered');
    }

    public function onOrderRefunded(OrderRefunded $event): void
    {
        DailyOrderStats::firstOrCreate(
            ['date' => now()->toDateString()],
            ['orders_placed' => 0, 'orders_cancelled' => 0, 'orders_delivered' => 0, 'revenue' => 0, 'refunds' => 0],
        )->increment('refunds', (int) round($event->amount * 100));
    }
}
