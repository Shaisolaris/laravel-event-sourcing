<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'uuid', 'customer_id', 'status', 'subtotal', 'tax', 'total', 'refunded_amount',
        'shipping_address', 'billing_address', 'coupon_code', 'tracking_number', 'carrier',
        'estimated_delivery', 'placed_at', 'confirmed_at', 'shipped_at', 'delivered_at',
        'cancelled_at', 'cancellation_reason',
    ];
    protected $casts = [
        'subtotal' => 'float', 'tax' => 'float', 'total' => 'float', 'refunded_amount' => 'float',
        'placed_at' => 'datetime', 'confirmed_at' => 'datetime', 'shipped_at' => 'datetime',
        'delivered_at' => 'datetime', 'cancelled_at' => 'datetime',
    ];
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function notes(): HasMany { return $this->hasMany(OrderNote::class); }
    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeByCustomer($query, string $customerId) { return $query->where('customer_id', $customerId); }
}
