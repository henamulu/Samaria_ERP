<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'sam_customer';

    public $timestamps = false;
    
    protected $fillable = [
        'customer_type',
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
        'approved_by',
    ];

    protected $casts = [
        // withholding is stored as 'Yes' / 'No' string in the database â€” do not cast to boolean
    ];

    /**
     * RelaciÃ³n con entregas
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'project', 'id');
    }

    /**
     * RelaciÃ³n con Ã³rdenes de venta
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'customer', 'id');
    }
}
