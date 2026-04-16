<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenants extends Model
{
    protected $table = 'tenants';
    protected $primaryKey = 'id';

    protected $fillable = [
        'penjuals_id', 'tenant_name', 'no_tenant', 'foto_tenant','desk_tenant','kantin'
    ];

    public function penjual()
    {
        return $this->belongsTo(Penjual::class, 'penjuals_id', 'id');
    }

    public function produks()
    {
        return $this->hasMany(Produk::class, 'tenants_id', 'id');
    }
}