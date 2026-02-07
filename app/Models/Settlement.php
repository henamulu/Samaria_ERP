<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    protected $table = 'sam_cr_settlement';

    public $timestamps = false;
    
    protected $fillable = [
        's_no',
        'settlement_type',
        'p_id',
        'payment_no',
        'amount',
        'balance',
        'status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'remark',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con pago
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'p_id', 'id');
    }
}
