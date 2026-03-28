<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $primaryKey = 'carts_id';

    protected $fillable = ['customers_id'];

    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}