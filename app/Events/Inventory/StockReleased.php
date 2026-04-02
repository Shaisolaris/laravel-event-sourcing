<?php

declare(strict_types=1);

namespace App\Events\Inventory;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockReleased extends ShouldBeStored
{
    public function __construct(
        public readonly string $productId,
        public readonly string $orderId,
        public readonly int $quantity,
        public readonly string $reason,
    ) {}
}
