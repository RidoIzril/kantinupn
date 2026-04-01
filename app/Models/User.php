<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ⬇️ WAJIB ADA
use App\Models\Customers;
use App\Models\Penjual;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // RELASI
    public function customers()
    {
        return $this->hasOne(Customers::class);
    }

    public function penjual()
    {
        return $this->hasOne(Penjual::class, 'users_id', 'id');
    }
}