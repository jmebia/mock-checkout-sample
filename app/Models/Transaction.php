<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // If your primary key is the default 'id', no need to change $primaryKey

    protected $fillable = [
        'transaction_id',
        'email',
        'amount',
        'status',
    ];

    // Cast 'amount' to decimal/float automatically
    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
