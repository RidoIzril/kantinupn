<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'customers_id','order_tanggal','order_type',
        'total_produk','total_harga','order_status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function details()
    {
        return $this->hasMany(DetailOrder::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function transaksi()
    {
        return $this->hasOne(Transaksi::class);
    }
}