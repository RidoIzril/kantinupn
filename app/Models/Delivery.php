<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $primaryKey = 'deliveries_id';

    protected $fillable = [
        'orders_id','alamat','catatan','status_delivery'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}