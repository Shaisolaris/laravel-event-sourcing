<?php

declare(strict_types=1);

namespace App\Events\Order;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderShipped extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $trackingNumber,
        public readonly string $carrier,
        public readonly string $shippedAt,
    ) {}
}
