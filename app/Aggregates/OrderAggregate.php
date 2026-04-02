<?php

declare(strict_types=1);

namespace App\Aggregates;

use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderConfirmed;
use App\Events\Order\OrderShipped;
use App\Events\Order\OrderDelivered;
use App\Events\Order\OrderCancelled;
use App\Events\Order\OrderRefunded;
use App\Events\Order\OrderNoteAdded;
use App\Exceptions\InvalidOrderStateException;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class OrderAggregate extends AggregateRoot
{
    private string $status = 'draft';
    private float $total = 0;
    private float $refundedAmount = 0;
    private array $items = [];
    private string $customerId = '';

    // ─── Commands ───────────────────────────────────────

    public function placeOrder(
        string $customerId,
        array $items,
        float $subtotal,
        float $tax,
        float $total,
        string $shippingAddress,
        string $billingAddress,
        ?string $couponCode = null,
    ): self {
        if ($this->status !== 'draft') {
            throw new InvalidOrderStateException('Order has already been placed');
        }

        if (empty($items)) {
            throw new \InvalidArgumentException('Order must have at least one item');
        }

        if ($total <= 0) {
            throw new \InvalidArgumentException('Order total must be positive');
        }

        $this->recordThat(new OrderPlaced(
            orderId: $this->uuid(),
            customerId: $customerId,
            items: $items,
            subtotal: $subtotal,
            tax: $tax,
            total: $total,
            shippingAddress: $shippingAddress,
            billingAddress: $billingAddress,
            couponCode: $couponCode,
        ));

        return $this;
    }

    public function confirm(string $confirmedBy, ?string $estimatedDelivery = null): self
    {
        $this->guardStatus(['placed'], 'confirm');

        $this->recordThat(new OrderConfirmed(
            orderId: $this->uuid(),
            confirmedBy: $confirmedBy,
            estimatedDelivery: $estimatedDelivery,
        ));

        return $this;
    }

    public function ship(string $trackingNumber, string $carrier): self
    {
        $this->guardStatus(['confirmed'], 'ship');

        $this->recordThat(new OrderShipped(
            orderId: $this->uuid(),
            trackingNumber: $trackingNumber,
            carrier: $carrier,
            shippedAt: now()->toIso8601String(),
        ));

        return $this;
    }

    public function deliver(?string $signedBy = null): self
    {
        $this->guardStatus(['shipped'], 'deliver');

        $this->recordThat(new OrderDelivered(
            orderId: $this->uuid(),
            deliveredAt: now()->toIso8601String(),
            signedBy: $signedBy,
        ));

        return $this;
    }

    public function cancel(string $reason, string $cancelledBy, bool $refund = false): self
    {
        $this->guardStatus(['placed', 'confirmed'], 'cancel');

        $this->recordThat(new OrderCancelled(
            orderId: $this->uuid(),
            reason: $reason,
            cancelledBy: $cancelledBy,
            refundIssued: $refund,
        ));

        return $this;
    }

    public function refund(float $amount, string $reason, string $refundId): self
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Refund amount must be positive');
        }

        if ($this->refundedAmount + $amount > $this->total) {
            throw new \InvalidArgumentException('Refund amount exceeds order total');
        }

        $this->recordThat(new OrderRefunded(
            orderId: $this->uuid(),
            amount: $amount,
            reason: $reason,
            refundId: $refundId,
        ));

        return $this;
    }

    public function addNote(string $note, string $authorId, bool $isInternal = true): self
    {
        $this->recordThat(new OrderNoteAdded(
            orderId: $this->uuid(),
            note: $note,
            authorId: $authorId,
            isInternal: $isInternal,
        ));

        return $this;
    }

    // ─── Event Appliers ─────────────────────────────────

    protected function applyOrderPlaced(OrderPlaced $event): void
    {
        $this->status = 'placed';
        $this->total = $event->total;
        $this->items = $event->items;
        $this->customerId = $event->customerId;
    }

    protected function applyOrderConfirmed(OrderConfirmed $event): void
    {
        $this->status = 'confirmed';
    }

    protected function applyOrderShipped(OrderShipped $event): void
    {
        $this->status = 'shipped';
    }

    protected function applyOrderDelivered(OrderDelivered $event): void
    {
        $this->status = 'delivered';
    }

    protected function applyOrderCancelled(OrderCancelled $event): void
    {
        $this->status = 'cancelled';
    }

    protected function applyOrderRefunded(OrderRefunded $event): void
    {
        $this->refundedAmount += $event->amount;
    }

    // ─── Guards ─────────────────────────────────────────

    private function guardStatus(array $allowedStatuses, string $action): void
    {
        if (!in_array($this->status, $allowedStatuses)) {
            throw new InvalidOrderStateException(
                "Cannot {$action} order in '{$this->status}' status. Allowed: " . implode(', ', $allowedStatuses)
            );
        }
    }
}
