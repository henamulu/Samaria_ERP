<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $table = 'sam_payment';

    public $timestamps = false;

    protected $fillable = [
        'bank_id',
        'p_no',
        'p_type',
        'po_no',
        'balance_type',
        'payment_status',
        'pdc_date',
        'pdc_update_date',
        'pdc_update_by',
        'account_no',
        'net_amount',
        'bank',
        'branch',
        'payment_date',
        'payment_ref_no',
        'payment_type',
        'cheque_no',
        'payment_description',
        'internal_payment_request',
        'pay_to',
        'payment_handover',
        'registered_by',
        'checked_by',
        'approved_by',
        'date_registered',
        'status',
        'item_desc',
        'advance_balance',
        'supplier_id',
        'settlement_id',
        'advance_status',
        'le_id',
        'outstand',
        'outstand_date',
        'withhold',
        'cr_status',
        'recon',
        'not_paid',
        'refund_by',
        'refund_status',
        'rejection_status',
        'rejection_reason',
    ];

    protected $casts = [
        'net_amount' => 'decimal:2',
        'advance_balance' => 'decimal:4',
        'not_paid' => 'decimal:2',
        'payment_date' => 'date',
        'pdc_date' => 'date',
        'pdc_update_date' => 'date',
        'date_registered' => 'date',
        'outstand_date' => 'date',
    ];

    /**
     * Relación con proveedor
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    /**
     * Relación con entregas
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'p_id', 'id');
    }

    /**
     * Relación con órdenes de venta
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'p_id', 'id');
    }
}
