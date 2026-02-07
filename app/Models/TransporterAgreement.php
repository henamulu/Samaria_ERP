<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransporterAgreement extends Model
{
    protected $table = 'sam_transporter_agg';

    public $timestamps = false;
    
    protected $fillable = [
        'a_no',
        't_id',
        'item_id',
        'item',
        'unit_price',
        'tax_p',
        'size',
        'plate_no',
        'site',
        'owner',
        'supplier',
        'status',
        'agg_status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'valid_from',
        'valid_to',
        'remark',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_p' => 'decimal:2',
        'registered_date' => 'date',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    /**
     * RelaciÃ³n con transportista
     */
    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class, 't_id', 'id');
    }

    /**
     * RelaciÃ³n con cliente (site)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'site', 'id');
    }

    /**
     * RelaciÃ³n con proveedor
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier', 'id');
    }

    /**
     * RelaciÃ³n con item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    /**
     * RelaciÃ³n con pagos de transportista
     */
    public function transporterPayments(): HasMany
    {
        return $this->hasMany(TransporterPayment::class, 'ta_id', 'id');
    }
}
