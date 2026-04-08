<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjual extends Model
{
    protected $table = 'penjuals';
    protected $primaryKey = 'id';

    protected $fillable = [
        'users_id', 'nama_lengkap', 'kontak', 'gender', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    // singular: tenant
    public function tenant()
    {
        return $this->hasOne(Tenants::class, 'penjuals_id', 'id');
    }
}