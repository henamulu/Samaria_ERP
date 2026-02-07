<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankBalance extends Model
{
    protected $table = 'sam_bank_balance';

    public $timestamps = false;

    protected $fillable = [
        'bank_id',
        'bank_name',
        'branch_name',
        'acc_no',
        'balance',
        'last_update',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }
}
