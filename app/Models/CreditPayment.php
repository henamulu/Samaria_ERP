<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditPayment extends Model
{
    protected $table = 'sam_ext_payment_c';

    public $timestamps = false;
    
    protected $fillable = [
        'p_no',
        'supplier_id',
        'amount',
        'balance',
        'status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'paid_date',
        'remark',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'registered_date' => 'date',
        'paid_date' => 'date',
    ];

    /**
     * RelaciÃ³n con proveedor
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
