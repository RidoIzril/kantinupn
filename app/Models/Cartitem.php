<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $primaryKey = 'cartitems_id';

    protected $fillable = [
        'carts_id','produks_id','variants_id',
        'jumlah','harga_per_item','subtotal'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}