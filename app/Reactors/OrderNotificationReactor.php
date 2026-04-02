<?php

declare(strict_types=1);

namespace App\Reactors;

use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderShipped;
use App\Events\Order\OrderDelivered;
use App\Events\Order\OrderCancelled;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class OrderNotificationReactor extends Reactor
{
    public function onOrderPlaced(OrderPlaced $event): void
    {
        Log::info("[Notification] Order confirmation email queued", [
            'order_id' => $event->orderId,
            'customer_id' => $event->customerId,
            'total' => $event->total,
        ]);
        // In production: dispatch(new SendOrderConfirmationEmail($event));
    }

    public function onOrderShipped(OrderShipped $event): void
    {
        Log::info("[Notification] Shipping notification queued", [
            'order_id' => $event->orderId,
            'tracking' => $event->trackingNumber,
            'carrier' => $event->carrier,
        ]);
        // In production: dispatch(new SendShippingNotification($event));
    }

    public function onOrderDelivered(OrderDelivered $event): void
    {
        Log::info("[Notification] Delivery confirmation + review request queued", [
            'order_id' => $event->orderId,
        ]);
        // In production: dispatch(new SendDeliveryConfirmation($event));
    }

    public function onOrderCancelled(OrderCancelled $event): void
    {
        Log::info("[Notification] Cancellation confirmation queued", [
            'order_id' => $event->orderId,
            'reason' => $event->reason,
        ]);
        // In production: dispatch(new SendCancellationEmail($event));
    }
}
