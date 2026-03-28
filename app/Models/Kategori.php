<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $primaryKey = 'kategoris_id';

    protected $fillable = ['nama_kategori'];

    public function produks()
    {
        return $this->hasMany(Produk::class);
    }
}