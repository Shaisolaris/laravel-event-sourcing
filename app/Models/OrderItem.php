<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'name', 'quantity', 'unit_price', 'total_price'];
    protected $casts = ['unit_price' => 'float', 'total_price' => 'float'];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
