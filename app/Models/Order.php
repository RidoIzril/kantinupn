<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'customers_id', 'order_tanggal', 'order_type', 'nomor_meja',
        'total_produk', 'total_harga', 'order_status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customers_id');
    }

    public function details()
    {
        // FOREIGN KEY BENAR: orders_id
        return $this->hasMany(DetailOrder::class, 'orders_id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'orders_id');
    }

    public function transaksi() 
    { 
        return $this->hasOne(\App\Models\Transaksi::class, 'orders_id'); 
    }
}