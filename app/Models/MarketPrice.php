<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPrice extends Model
{
    protected $table = 'sam_market_price';

    public $timestamps = false;
    
    protected $fillable = [
        'item_id',
        'item',
        'unit',
        'unit_price',
        'tax_p',
        'status',
        'registered_by',
        'registered_date',
        'registered_time',
        'price_type',
        'agg_no',
        'customer',
        'transport',
        'transport_unit',
        't_unit_price',
        't_tax_p',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_p' => 'decimal:2',
        't_unit_price' => 'decimal:2',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    /**
     * RelaciÃ³n con cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer', 'id');
    }
}
