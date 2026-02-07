<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAgreement extends Model
{
    protected $table = 'sam_customer_agg';

    public $timestamps = false;
    
    protected $fillable = [
        'a_no',
        'customer',
        'item_id',
        'item',
        'unit',
        'unit_price',
        'tax_p',
        'quantity',
        'total',
        'status',
        'agg_status',
        'valid_from',
        'valid_to',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'remark',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_p' => 'decimal:2',
        'quantity' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer', 'id');
    }

    /**
     * RelaciÃ³n con item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
