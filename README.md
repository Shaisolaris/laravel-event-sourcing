# laravel-event-sourcing

## Quick Start

```bash
git clone https://github.com/Shaisolaris/laravel-event-sourcing.git
cd laravel-event-sourcing
cp .env.example .env
composer install --no-interaction
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
# Open http://localhost:8000
# Login: admin@demo.com / demo123
```


![CI](https://github.com/Shaisolaris/laravel-event-sourcing/actions/workflows/ci.yml/badge.svg)

Laravel 11 event-sourced API implementing CQRS with Spatie Event Sourcing for an e-commerce order lifecycle. Features aggregate roots with state machine guards, event-driven projections for read models, reactive side effects (notifications, inventory), daily analytics projections, and a full event replay/audit endpoint.

## Stack

- **Framework:** Laravel 11, PHP 8.2+
- **Event Sourcing:** Spatie Laravel Event Sourcing v7
- **Auth:** Laravel Sanctum
- **Pattern:** CQRS (Command Query Responsibility Segregation)

## Event Sourcing Architecture

```
Command (API Request)
    ↓
OrderAggregate (validates + records event)
    ↓
Event Store (stored_events table)
    ↓ (async)
┌───────────────────────────┐
│  OrderProjector           │ → orders table (read model)
│  OrderAnalyticsProjector  │ → daily_order_stats table
│  OrderNotificationReactor │ → emails, push notifications
│  InventoryReactor         │ → stock reservation/release
└───────────────────────────┘
```

## Domain Events

| Event | Trigger | Data |
|---|---|---|
| `OrderPlaced` | New order | Customer, items, totals, addresses |
| `OrderConfirmed` | Staff confirms | Confirmed by, estimated delivery |
| `OrderShipped` | Shipped | Tracking number, carrier |
| `OrderDelivered` | Delivered | Delivery timestamp, signed by |
| `OrderCancelled` | Cancellation | Reason, cancelled by, refund flag |
| `OrderRefunded` | Refund issued | Amount, reason, refund ID |
| `OrderNoteAdded` | Note added | Note text, author, internal flag |
| `StockReserved` | Order placed | Product, order, quantity |
| `StockReleased` | Order cancelled | Product, order, quantity, reason |

## Order State Machine

```
draft → placed → confirmed → shipped → delivered
                    ↓           
                cancelled (from placed or confirmed)
                    
Any state → refunded (partial or full)
```

The `OrderAggregate` enforces valid transitions. Attempting an invalid transition throws `InvalidOrderStateException`.

## API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/orders` | List orders (filter by status, customer) |
| POST | `/api/orders` | Place new order (via aggregate) |
| GET | `/api/orders/{uuid}` | Order detail with items + notes |
| GET | `/api/orders/{uuid}/events` | Full event history for audit |
| POST | `/api/orders/{uuid}/confirm` | Confirm order |
| POST | `/api/orders/{uuid}/ship` | Mark as shipped |
| POST | `/api/orders/{uuid}/deliver` | Mark as delivered |
| POST | `/api/orders/{uuid}/cancel` | Cancel order |
| POST | `/api/orders/{uuid}/refund` | Issue refund |
| POST | `/api/orders/{uuid}/notes` | Add note |
| GET | `/api/orders/analytics` | 30-day daily stats |

## File Structure

```
app/
├── Aggregates/
│   └── OrderAggregate.php          # State machine, event recording, guards
├── Events/
│   ├── Order/
│   │   ├── OrderPlaced.php         # Items, totals, addresses, coupon
│   │   ├── OrderConfirmed.php      # Confirmed by, estimated delivery
│   │   ├── OrderShipped.php        # Tracking, carrier, timestamp
│   │   ├── OrderDelivered.php      # Delivery time, signature
│   │   ├── OrderCancelled.php      # Reason, refund flag
│   │   ├── OrderRefunded.php       # Amount, reason, refund ID
│   │   └── OrderNoteAdded.php      # Note, author, internal flag
│   └── Inventory/
│       ├── StockReserved.php       # Product, order, quantity
│       └── StockReleased.php       # Product, order, quantity, reason
├── Projectors/
│   ├── OrderProjector.php          # Builds orders read model from events
│   └── OrderAnalyticsProjector.php # Aggregates daily stats
├── Reactors/
│   ├── OrderNotificationReactor.php  # Queues emails on lifecycle events
│   └── InventoryReactor.php          # Reserves/releases stock
├── Models/
│   ├── Order.php                   # Read model with scopes
│   ├── OrderItem.php
│   ├── OrderNote.php
│   └── DailyOrderStats.php        # Analytics projection
├── Http/Controllers/Api/
│   └── OrderController.php         # Full CRUD + lifecycle + events endpoint
└── Exceptions/
    └── InvalidOrderStateException.php
```

## Key Design Decisions

**Aggregate root as single source of truth.** All mutations go through `OrderAggregate`. The aggregate validates business rules, records events, and the projectors build read models. The read models (Order table) are derived state that can be rebuilt from events at any time.

**State machine in the aggregate, not the database.** Order status transitions are enforced by `guardStatus()` in the aggregate. The database status column is a projection convenience, not the authority. This means you can replay events and the state will always be consistent.

**Separate projectors for different read concerns.** `OrderProjector` builds the transactional read model (order details). `OrderAnalyticsProjector` builds aggregated stats. They run independently — you can rebuild one without affecting the other.

**Reactors for side effects.** Notifications and inventory operations happen in reactors, not projectors. Reactors are for "fire and forget" side effects that don't build read models. If a reactor fails, the event is still stored and the projections are still correct.

**Event endpoint for audit trail.** `GET /api/orders/{uuid}/events` returns the complete event history for an order. This provides a full audit trail without any additional logging infrastructure — the event store IS the audit log.

**Refund as separate event, not status.** Refunds are tracked as individual events with amounts, allowing partial refunds. The total refunded amount is accumulated. When refunded amount equals the order total, the status changes to "refunded".

## Setup

```bash
git clone https://github.com/Shaisolaris/laravel-event-sourcing.git
cd laravel-event-sourcing
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## License

MIT
