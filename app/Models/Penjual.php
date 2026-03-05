<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Penjual extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;
    protected $table = 'penjuals';
    protected $primaryKey = 'penjual_id';

    // WAJIB untuk custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'penjual_username',
        'penjual_notenant',
        'penjual_tenantname',
        'penjual_password',
        'penjual_fullname',
        'penjual_nohp',
        'foto_tenant',
        'penjual_gender',
        'penjual_status',
    ];

    protected $hidden = [
        'penjual_password',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTH CONFIGURATION
    |--------------------------------------------------------------------------
    */

    // Laravel akan pakai ini sebagai identifier (bukan id default)
    // Custom password field
    public function getAuthPassword()
    {
        return $this->penjual_password;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */
    public function products()
    {
        return $this->hasMany(Product::class, 'penjual_id', 'penjual_id');
    }
}