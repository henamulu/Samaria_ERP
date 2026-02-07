<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponNumber extends Model
{
    protected $table = 'sam_cupon_no';

    public $timestamps = false;
    
    protected $fillable = [
        'cupon_no',
        'r_id',
        'registered_by',
        'registered_date',
        'order_type',
        'status',
        'supplier',
        'dispatcher',
        'pass_by',
        'h_no',
        'ref_no',
        'cupon_tag',
        'd_r',
        'd_r_date',
        'd_r_by',
        'filename',
    ];

    protected $casts = [
        'registered_date' => 'date',
        'd_r_date' => 'date',
    ];

    /**
     * RelaciÃ³n con cupÃ³n
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'r_id', 'id');
    }

    /**
     * RelaciÃ³n con proveedor
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier', 'id');
    }
}
