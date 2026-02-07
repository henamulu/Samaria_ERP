<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collection extends Model
{
    protected $table = 'sam_collection';

    public $timestamps = false;

    protected $fillable = [
        'collection_no',
        'collection_date',
        'type',
        'customer_id',
        'customer_name',
        'source',
        'collection_type',
        'bank',
        'amount',
        'cheque_no',
        'cheque_date',
        'reference_no',
        'deposit_date',
        'description',
        'status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collection_date' => 'date',
        'cheque_date' => 'date',
        'deposit_date' => 'date',
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
