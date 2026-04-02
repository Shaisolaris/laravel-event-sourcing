<?php

declare(strict_types=1);

namespace App\Events\Order;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderRefunded extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly float $amount,
        public readonly string $reason,
        public readonly string $refundId,
    ) {}
}
