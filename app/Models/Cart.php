<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $primaryKey = 'cart_id';
    protected $fillable = [
        'customer_id', 'product_id', 'quantity', 'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function customers()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
}
