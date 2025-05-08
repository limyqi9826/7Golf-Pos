<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Items extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price',
        'stock',
        'barcode'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];
}
