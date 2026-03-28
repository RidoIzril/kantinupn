<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $primaryKey = 'produk_id';

    protected $fillable = [
        'kategoris_id','tenants_id','nama','deskripsi','harga','stok','foto_produk'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenants::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }
}