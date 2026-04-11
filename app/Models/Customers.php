<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    protected $table = 'customers';

    protected $fillable = [
    'users_id',
    'nama_lengkap',
    'email',
    'tanggal_lahir',
    'jenis_kelamin',
    'fakultas',
    'status',
    'kontak',
];
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}