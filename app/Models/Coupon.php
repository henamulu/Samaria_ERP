<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $table = 'sam_cupon';

    public $timestamps = false;
    
    protected $fillable = [
        'r_no',
        'supplier',
        'po_no',
        'purchase_type',
        'supplier_name',
        'registered_by',
        'registered_date',
        'ref_no',
        'status',
        'cupon_tag',
    ];

    protected $casts = [
        'registered_date' => 'date',
    ];

    /**
     * RelaciÃ³n con proveedor
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier', 'id');
    }

    /**
     * RelaciÃ³n con orden de compra
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_no', 'po_no');
    }

    /**
     * RelaciÃ³n con nÃºmeros de cupÃ³n
     */
    public function couponNumbers(): HasMany
    {
        return $this->hasMany(CouponNumber::class, 'r_id', 'id');
    }
}
