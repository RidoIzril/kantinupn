<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;
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
        return $this->hasMany(Variant::class, 'produks_id');
    }

    
}