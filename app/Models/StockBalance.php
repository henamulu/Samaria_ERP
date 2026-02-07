<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    protected $table = 'sam_stock_balance';

    public $timestamps = false;
    
    protected $fillable = [
        'item_id',
        'item',
        'unit',
        'incoming',
        'outgoing',
        'balance',
        'registered_by',
        'registered_date',
        'remark',
    ];

    protected $casts = [
        'incoming' => 'decimal:2',
        'outgoing' => 'decimal:2',
        'balance' => 'decimal:2',
        'registered_date' => 'date',
    ];
}
