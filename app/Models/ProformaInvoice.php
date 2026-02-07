<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProformaInvoice extends Model
{
    protected $table = 'sam_proforma';

    public $timestamps = false;
    
    protected $fillable = [
        'pi_no',
        'customer',
        'item_id',
        'item',
        'quantity',
        'unit',
        'unit_price',
        'tax_p',
        'total',
        'validity',
        'd_time',
        'term_of_payment',
        'payment_percent',
        'p_date',
        'status',
        'so_status',
        'transport',
        't_unit_price',
        'transport_unit',
        't_tax_p',
        't_km',
        'location',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_p' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_percent' => 'decimal:2',
        't_unit_price' => 'decimal:2',
        't_tax_p' => 'decimal:2',
        't_km' => 'decimal:2',
        'p_date' => 'date',
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
