<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'produks_id','nama_variant','harga_variant'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}