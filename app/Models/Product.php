<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Model Product -> tabel products (otomatis)

    protected $fillable = [
        'name', 'sku', 'category', 'description',
        'price', 'stock', 'status'
    ];

    // update created_at dan updated_at secara otomatis
    public $timestamps = true;
}