<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNote extends Model
{
    protected $fillable = ['order_id', 'note', 'author_id', 'is_internal'];
    protected $casts = ['is_internal' => 'boolean'];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
