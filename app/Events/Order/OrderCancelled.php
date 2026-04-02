<?php

declare(strict_types=1);

namespace App\Events\Order;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderCancelled extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $reason,
        public readonly string $cancelledBy,
        public readonly bool $refundIssued = false,
    ) {}
}
