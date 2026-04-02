<?php

declare(strict_types=1);

namespace App\Events\Order;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderConfirmed extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $confirmedBy,
        public readonly ?string $estimatedDelivery = null,
    ) {}
}
