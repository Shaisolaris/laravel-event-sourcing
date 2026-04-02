<?php

declare(strict_types=1);

namespace App\Projectors;

use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderConfirmed;
use App\Events\Order\OrderShipped;
use App\Events\Order\OrderDelivered;
use App\Events\Order\OrderCancelled;
use App\Events\Order\OrderRefunded;
use App\Events\Order\OrderNoteAdded;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class OrderProjector extends Projector
{
    public function onOrderPlaced(OrderPlaced $event): void
    {
        $order = Order::create([
            'uuid' => $event->orderId,
            'customer_id' => $event->customerId,
            'status' => 'placed',
            'subtotal' => $event->subtotal,
            'tax' => $event->tax,
            'total' => $event->total,
            'shipping_address' => $event->shippingAddress,
            'billing_address' => $event->billingAddress,
            'coupon_code' => $event->couponCode,
            'placed_at' => now(),
        ]);

        foreach ($event->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }
    }

    public function onOrderConfirmed(OrderConfirmed $event): void
    {
        Order::where('uuid', $event->orderId)->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'estimated_delivery' => $event->estimatedDelivery,
        ]);
    }

    public function onOrderShipped(OrderShipped $event): void
    {
        Order::where('uuid', $event->orderId)->update([
            'status' => 'shipped',
            'tracking_number' => $event->trackingNumber,
            'carrier' => $event->carrier,
            'shipped_at' => $event->shippedAt,
        ]);
    }

    public function onOrderDelivered(OrderDelivered $event): void
    {
        Order::where('uuid', $event->orderId)->update([
            'status' => 'delivered',
            'delivered_at' => $event->deliveredAt,
        ]);
    }

    public function onOrderCancelled(OrderCancelled $event): void
    {
        Order::where('uuid', $event->orderId)->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $event->reason,
        ]);
    }

    public function onOrderRefunded(OrderRefunded $event): void
    {
        $order = Order::where('uuid', $event->orderId)->first();
        if ($order) {
            $order->increment('refunded_amount', $event->amount);
            if ($order->refunded_amount >= $order->total) {
                $order->update(['status' => 'refunded']);
            }
        }
    }

    public function onOrderNoteAdded(OrderNoteAdded $event): void
    {
        $order = Order::where('uuid', $event->orderId)->first();
        if ($order) {
            OrderNote::create([
                'order_id' => $order->id,
                'note' => $event->note,
                'author_id' => $event->authorId,
                'is_internal' => $event->isInternal,
            ]);
        }
    }
}
