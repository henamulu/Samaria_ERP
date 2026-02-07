<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $table = 'sam_sales_order';

    public $timestamps = false;
    
    protected $fillable = [
        'so_no',
        'payment_type',
        'from_dep',
        'to_dep',
        'customer',
        'type',
        'item_id',
        'item',
        'unit',
        'quantity',
        'unit_price',
        'tax_p',
        'total',
        'remaning',
        'remark',
        'location',
        'e_date',
        'status',
        'registered_by',
        'registered_date',
        'p_id',
        'checked_by',
        'approved_by',
        'p_doc',
        'p_doc_by',
        'p_doc_date',
        'i_id',
        'pr_id',
        'pi_id',
        't_km',
        't_tax_p',
        'transport_unit',
        't_unit_price',
        'transport',
        'invoice_type',
        'agg_id',
        'previous_price',
        'd_status',
        'requested_price',
        'r_by',
        'r_approved_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'remaning' => 'decimal:2',
        't_km' => 'decimal:2',
        't_unit_price' => 'decimal:2',
        'previous_price' => 'decimal:2',
        'requested_price' => 'decimal:2',
        'e_date' => 'date',
        'registered_date' => 'date',
        'p_doc_date' => 'date',
    ];

    /**
     * RelaciÃ³n con cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer', 'id');
    }

    /**
     * RelaciÃ³n con entregas
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'siv_id', 'id');
    }

    /**
     * RelaciÃ³n con pago
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'p_id', 'id');
    }
}
