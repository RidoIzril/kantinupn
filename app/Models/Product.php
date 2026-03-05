<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'penjual_id',
        'product_code',
        'product_name',
        'category_id',
        'product_price',
        'product_stock',
        'product_image',
        'product_description',
    ];

    /**
     * RELASI: Product -> Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    /**
     * RELASI: Product -> Penjual
     */
    public function penjual()
    {
        return $this->belongsTo(Penjual::class, 'penjual_id', 'penjual_id');
    }
}
