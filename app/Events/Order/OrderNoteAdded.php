<?php

declare(strict_types=1);

namespace App\Events\Order;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderNoteAdded extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $note,
        public readonly string $authorId,
        public readonly bool $isInternal = true,
    ) {}
}
