<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';

    protected $fillable = [
        'produks_id','nama_variant','harga_variant', 'status_variant'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}