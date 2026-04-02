<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyOrderStats extends Model
{
    protected $fillable = ['date', 'orders_placed', 'orders_cancelled', 'orders_delivered', 'revenue', 'refunds'];
    protected $casts = ['date' => 'date'];
    public $timestamps = false;
}
