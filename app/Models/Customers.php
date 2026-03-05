<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Customers extends Authenticatable
{
use HasApiTokens;    
use Notifiable;
    protected $table = 'customers'; // Nama tabel di database
    protected $primaryKey = 'customer_id'; // Primary Key
    protected $fillable = [
        'customer_username',
        'customer_password',
        'customer_fullname',
        'customer_email',
        'customer_dob',
        'customer_gender',
        'customer_faculty',
        'customer_status',
        'customer_contact',
    ];
    public function getAuthPassword()
    {
        return $this->customer_password;
    }
    
    public function carts()
    {
        return $this->hasMany(Cart::class, 'customer_id');
    }
}
