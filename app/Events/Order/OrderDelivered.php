<?php

declare(strict_types=1);

namespace App\Events\Order;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderDelivered extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $deliveredAt,
        public readonly ?string $signedBy = null,
    ) {}
}
