<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsurancePolicy extends Model
{
    protected $table = 'sam_insurance_policy';

    public $timestamps = false;
    
    protected $fillable = [
        'i_no',
        'insurance_type',
        'p_type',
        'insurance_company',
        'insured',
        'c_no',
        'p_no',
        'issued_date',
        'period',
        'fund_tariff',
        'premium_tariff',
        'notification',
        'status',
        'registered_by',
        'registered_date',
        'previous',
        'registered_time',
    ];

    protected $casts = [
        'fund_tariff' => 'decimal:2',
        'premium_tariff' => 'decimal:2',
        'issued_date' => 'date',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con compaÃ±Ã­a de seguros
     */
    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Insurance::class, 'insurance_company', 'insurance_name');
    }
}
