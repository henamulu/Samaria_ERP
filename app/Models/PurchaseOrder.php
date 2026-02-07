<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'sam_purchase_order';

    public $timestamps = false;
    
    protected $fillable = [
        'po_no',
        'po_type',
        'supplier',
        'item',
        'unit',
        'quantity',
        'unit_price',
        'total',
        'remark',
        'status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
    ];
}
