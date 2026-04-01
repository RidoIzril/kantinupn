<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'email',
        'kontak',
        'tanggal_lahir',
        'jenis_kelamin',
        'fakultas',
        'status',
    ];
}