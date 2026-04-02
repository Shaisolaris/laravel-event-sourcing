<?php

declare(strict_types=1);

namespace App\Events\Order;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderPlaced extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerId,
        public readonly array $items,
        public readonly float $subtotal,
        public readonly float $tax,
        public readonly float $total,
        public readonly string $shippingAddress,
        public readonly string $billingAddress,
        public readonly ?string $couponCode = null,
    ) {}
}
