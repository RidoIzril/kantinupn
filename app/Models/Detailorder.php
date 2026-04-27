<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailOrder extends Model
{
    protected $table = 'detailorders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'orders_id', 'produks_id', 'jumlah', 'total_harga', 'variants_id', 'catatan_menu', 
    ];

    public function order()
    {
        
        return $this->belongsTo(Order::class, 'orders_id');
    }

    public function produk() {

        return $this->belongsTo(Produk::class, 'produks_id')->withTrashed();
    }

    public function variant() {

        return $this->belongsTo(Variant::class, 'variants_id')->withTrashed();
    }
}