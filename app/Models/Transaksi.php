<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksis';
    protected $primaryKey = 'id';

    protected $fillable = [
        'orders_id','metode_pembayaran','status_pembayaran',
        'jumlah_bayar','waktu_bayar','reference_payment', 'qris_url'
    ];

    public function order()
    {
        // FK di transaksi = orders_id, PK di orders = order_id
        return $this->belongsTo(Order::class, 'orders_id', 'id');
    }
}