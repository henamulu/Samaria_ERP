<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreRequisition extends Model
{
    protected $table = 'sam_store_requisition';

    public $timestamps = false;
    
    protected $fillable = [
        'sr_no',
        'item_id',
        'item',
        'sr_quantity',
        'unit',
        'priority',
        'e_date',
        'urgency_reason',
        'status',
        'pr_status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'remark',
    ];

    protected $casts = [
        'sr_quantity' => 'decimal:2',
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
}
