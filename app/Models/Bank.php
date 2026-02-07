<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $table = 'sam_bank';

    public $timestamps = false;
    
    protected $fillable = [
        'bank_name',
        'branch_name',
        'bank_ac_no',
        'contact_person',
        'beginning_balance',
        'beginning_left',
        'have_termloan',
        'tl_granted_date',
        'tl_maturity_date',
        'tl_amount',
        'tl_interest_rate',
        'tl_period',
        'tl_relief_period',
        'tl_relief_grant_date',
        'tl_relief_maturity_date',
        'have_overdraft',
        'od_amount',
        'od_interest_rate',
        'od_start_date',
        'od_balance',
        'block_amount',
        'bank_status',
        'tl_paid',
        'proforma_priority',
        'registered_date',
        'registered_by',
        'recon_start',
        'year_ending',
    ];

    protected $casts = [
        'beginning_balance' => 'decimal:2',
        'beginning_left' => 'decimal:2',
        'tl_amount' => 'decimal:2',
        'tl_interest_rate' => 'decimal:2',
        'od_amount' => 'decimal:2',
        'od_interest_rate' => 'decimal:2',
        'od_balance' => 'decimal:2',
        'block_amount' => 'decimal:2',
        'tl_paid' => 'decimal:2',
        'tl_granted_date' => 'date',
        'tl_maturity_date' => 'date',
        'tl_relief_grant_date' => 'date',
        'tl_relief_maturity_date' => 'date',
        'od_start_date' => 'date',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con transferencias bancarias
     */
    public function bankTransfers(): HasMany
    {
        return $this->hasMany(BankTransfer::class, 'bank_id', 'id');
    }

    /**
     * RelaciÃ³n con reconciliaciones bancarias
     */
    public function bankReconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class, 'bank_id', 'id');
    }
}
