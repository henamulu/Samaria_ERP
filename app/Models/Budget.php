<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $table = 'sam_budget';

    public $timestamps = false;
    
    protected $fillable = [
        'b_no',
        'b_id',
        'project',
        'amount',
        'status',
        'registered_by',
        'registered_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con budget request
     */
    public function budgetRequest(): BelongsTo
    {
        return $this->belongsTo(BudgetRequest::class, 'b_id', 'id');
    }
}
