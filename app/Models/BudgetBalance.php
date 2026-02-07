<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetBalance extends Model
{
    protected $table = 'sam_budget_balance';

    public $timestamps = false;
    
    protected $fillable = [
        'project',
        'balance',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];
}
