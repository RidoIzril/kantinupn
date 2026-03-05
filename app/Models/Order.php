<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    public $timestamps = true;

    protected $fillable = [
        'customer_id',
        'order_date',
        'total_amount',
        'total_price',
        'status',
    ];

    public function customers()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'order_id');
    }
}
