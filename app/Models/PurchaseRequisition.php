<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisition extends Model
{
    protected $table = 'sam_purchase_requisition';

    public $timestamps = false;
    
    protected $fillable = [
        'pr_no',
        'item_id',
        'item',
        'item_desc',
        'pr_quantity',
        'unit',
        'request_from',
        'used_for',
        'e_date',
        'status',
        'pr_status',
        'sr_id',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'remark',
    ];

    protected $casts = [
        'pr_quantity' => 'decimal:2',
        'e_date' => 'date',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    /**
     * RelaciÃ³n con store requisition
     */
    public function storeRequisition(): BelongsTo
    {
        return $this->belongsTo(StoreRequisition::class, 'sr_id', 'id');
    }
}
