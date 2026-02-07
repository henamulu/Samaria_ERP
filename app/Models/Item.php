<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $table = 'sam_item';

    public $timestamps = false;
    
    protected $fillable = [
        'item',
        'item_name',
        'category',
        'unit',
        'item_type',
        'status',
        'description',
        'registered_by',
        'registered_date',
    ];

    protected $casts = [
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con entregas
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'item', 'item');
    }

    /**
     * RelaciÃ³n con Ã³rdenes de venta
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'item_id', 'id');
    }

    /**
     * RelaciÃ³n con Ã³rdenes de compra
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'item', 'item');
    }
}
