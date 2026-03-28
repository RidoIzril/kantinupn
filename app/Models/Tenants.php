<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenants extends Model
{
    protected $primaryKey = 'tenants_id';

    protected $fillable = [
        'penjuals_id','tenant_name','no_tenant','foto_tenant'
    ];

    public function penjual()
    {
        return $this->belongsTo(Penjual::class);
    }

    public function produks()
    {
        return $this->hasMany(Produk::class);
    }
}