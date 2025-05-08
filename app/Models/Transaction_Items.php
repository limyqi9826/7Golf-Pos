<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction_Items extends Model
{
    protected $table = 'transaction_items';

    protected $fillable = [
        'transaction_id',
        'item_id',
        'quantity',
        'unit_price',
        'total_price'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transactions::class, 'transaction_id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
