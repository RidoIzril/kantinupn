<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class Category extends Authenticatable
{
    use Notifiable;
    protected $table = 'categories'; // Nama tabel di database
    protected $primaryKey = 'category_id'; // Primary Key
    protected $fillable = [
        'category_name',
        'category_code',
    ];
}
