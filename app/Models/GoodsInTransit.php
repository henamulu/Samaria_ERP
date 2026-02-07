<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsInTransit extends Model
{
    protected $table = 'sam_git';

    public $timestamps = false;
    
    protected $fillable = [
        'git_no',
        'so_id',
        'supplier_id',
        'item',
        'item_id',
        'quantity',
        'unit',
        'unit_price',
        'tax_p',
        'total',
        'status',
        'git_date',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'delivered_date',
        'hold_reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_p' => 'decimal:2',
        'total' => 'decimal:2',
        'git_date' => 'date',
        'registered_date' => 'date',
        'delivered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con orden de venta
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'so_id', 'id');
    }

    /**
     * RelaciÃ³n con proveedor
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    /**
     * RelaciÃ³n con item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    /**
     * RelaciÃ³n con entregas
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'git_id', 'id');
    }
}
