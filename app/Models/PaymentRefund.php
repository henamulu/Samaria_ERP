<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRefund extends Model
{
    protected $table = 'sam_payment_refund';

    public $timestamps = false;
    
    protected $fillable = [
        'pr_no',
        'p_id',
        'payment_no',
        'amount',
        'reason',
        'status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'refund_date',
        'remark',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'registered_date' => 'date',
        'refund_date' => 'date',
    ];

    /**
     * RelaciÃ³n con pago
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'p_id', 'id');
    }
}
