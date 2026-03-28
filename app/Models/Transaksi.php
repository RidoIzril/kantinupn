<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $primaryKey = 'transaksis_id';

    protected $fillable = [
        'orders_id','metode_pembayaran','status_pembayaran',
        'jumlah_bayar','waktu_bayar','reference_payment'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}