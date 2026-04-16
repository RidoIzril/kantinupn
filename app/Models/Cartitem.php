<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'carts_id','produks_id','variants_id',
        'jumlah','harga_per_item','subtotal',
        'catatan_menu','nomor_meja'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'carts_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produks_id');
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'variants_id');
    }
}