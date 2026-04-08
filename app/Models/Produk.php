<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kategoris_id', 'tenants_id', 'nama', 'deskripsi', 'harga', 'stok', 'foto_produk'
    ];

    public function kategoris()
    {
        return $this->belongsTo(Kategori::class, 'kategoris_id', 'id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenants::class, 'tenants_id', 'id');
    }

    public function variants()
    {
        return $this->hasMany(Variant::class, 'produk_id', 'id');
    }
}