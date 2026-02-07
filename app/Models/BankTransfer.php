<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransfer extends Model
{
    protected $table = 'sam_bank_transfer';

    public $timestamps = false;
    
    protected $fillable = [
        't_no',
        'bank_id',
        'from_bank',
        'to_bank',
        'i_amount',
        't_date',
        'description',
        'status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'remark',
    ];

    protected $casts = [
        'i_amount' => 'decimal:2',
        't_date' => 'date',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con banco
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }
}
