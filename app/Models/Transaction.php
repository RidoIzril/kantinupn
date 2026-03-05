<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';
    public $timestamps = true;

    protected $fillable = [
        'custom_code_transaction',
        'order_id',
        'payment_id',
        'transaction_date',
        'status',
        'delivery_address',
        'delivery_status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
