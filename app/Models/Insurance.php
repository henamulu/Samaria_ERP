<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insurance extends Model
{
    protected $table = 'sam_insurance';

    public $timestamps = false;
    
    protected $fillable = [
        'insurance_name',
        'branch',
        'contact_person',
        'phone_number',
        'insurance_status',
    ];

    /**
     * RelaciÃ³n con pÃ³lizas
     */
    public function policies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class, 'insurance_company', 'insurance_name');
    }
}
