<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailOrder extends Model
{
    protected $primaryKey = 'detailorders_id';

    protected $fillable = [
        'orders_id','produks_id','jumlah','total_harga'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}