<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardPayment extends Model
{
    protected $fillable = ['transaction_id', 'card_last_four', 'card_network'];

    public function transaction()
    {
        return $this->belongsTo(Transactions::class);
    }
}
