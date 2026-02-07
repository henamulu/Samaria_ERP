<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'sam_supplier';

    public $timestamps = false;
    
    protected $fillable = [
        'supplier_name',
        'supplier_tin',
        'supplier_category',
        'contact_person',
        'phone_number',
        'status',
        'address',
        'registered_by',
    ];

    /**
     * RelaciÃ³n con Ã³rdenes de compra
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier', 'id');
    }
}
