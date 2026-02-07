<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transporter extends Model
{
    protected $table = 'sam_transporter';

    public $timestamps = false;
    
    protected $fillable = [
        'transporter_type',
        'company_name',
        'firstname',
        'lastname',
        'tin_no',
        'withholding',
        'withhold',
        'phone_no',
        'email',
        'contact_person',
        'office_location',
        'status',
        'registered_by',
    ];

    /**
     * RelaciÃ³n con entregas
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 't_id', 'id');
    }

    /**
     * RelaciÃ³n con pagos de transportista
     */
    public function transporterPayments(): HasMany
    {
        return $this->hasMany(TransporterPayment::class, 't_id', 'id');
    }
}
