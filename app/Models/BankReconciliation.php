<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliation extends Model
{
    protected $table = 'sam_bank_reconcilation';

    public $timestamps = false;
    
    protected $fillable = [
        'br_no',
        'bank_id',
        'bank_name',
        'month',
        'year',
        'balance',
        'beginning_balance',
        'diff',
        'status',
        'payment_type',
        'amount',
        'od_balance',
        'registered_by',
        'date_registered',
        'checked_by',
        'approved_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'beginning_balance' => 'decimal:2',
        'diff' => 'decimal:2',
        'amount' => 'decimal:2',
        'od_balance' => 'decimal:2',
        'date_registered' => 'date',
    ];

    /**
     * RelaciÃ³n con banco
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }
}
