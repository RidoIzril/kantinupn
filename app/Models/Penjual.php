<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjual extends Model
{
    protected $primaryKey = 'penjuals_id';

    protected $fillable = [
        'users_id','nama_lengkap','kontak','gender','status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->hasOne(Tenants::class);
    }
}