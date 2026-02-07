<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceive extends Model
{
    protected $table = 'sam_goods_receive';

    public $timestamps = false;
    
    protected $fillable = [
        'gr_no',
        'po_no',
        'supplier_id',
        'item',
        'item_id',
        'quantity',
        'received_qty',
        'unit',
        'unit_price',
        'total',
        'remaning',
        'status',
        'grv_date',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'inspection_date',
        'inspection_status',
        'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'remaning' => 'decimal:2',
        'grv_date' => 'date',
        'registered_date' => 'date',
        'inspection_date' => 'date',
    ];

    /**
     * RelaciÃ³n con orden de compra
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_no', 'po_no');
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
}
