<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marker extends Model
{
    // Nama tabel jika tidak sesuai konvensi Laravel
    protected $table = 'markers';

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'name', 
        'description', 
        'latitude', 
        'longitude'
    ];

    // Casting tipe data
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float'
    ];
}
