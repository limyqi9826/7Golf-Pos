<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'customer_id',
        'transaction_code',
        'hole_number',
        'buggy_number',
        'players',
        'subtotal',
        'tax',
        'total',
        'payment_method',
        'special_request'
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'split_payment' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
