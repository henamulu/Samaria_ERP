<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetRequest extends Model
{
    protected $table = 'sam_budget_request';

    public $timestamps = false;
    
    protected $fillable = [
        'b_no',
        'used_for',
        'project',
        'purpose',
        'category',
        'status',
        'request_from',
        'registered_by',
        'registered_date',
        'amount',
        'balance',
        'b_doc',
        'b_doc_by',
        'b_doc_date',
        'item',
        'item_id',
        'unit_price',
        'quantity',
        'profit',
        'overhead',
        'unit',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'profit' => 'decimal:2',
        'overhead' => 'decimal:2',
        'registered_date' => 'date',
        'b_doc_date' => 'date',
    ];

    /**
     * RelaciÃ³n con item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    /**
     * RelaciÃ³n con budgets creados
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'b_id', 'id');
    }
}
