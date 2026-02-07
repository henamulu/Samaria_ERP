<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransporterPayment extends Model
{
    protected $table = 'sam_transporter_payment';

    public $timestamps = false;
    
    protected $fillable = [
        't_id',
        'd_id',
        'ta_id',
        'transporter_name',
        'unit_price',
        'total',
        'status',
        'tp_no',
        'registered_by',
        'registered_date',
        'trans_lose',
        'approved_by',
        'from_date',
        'to_date',
        'owner',
        'u_price',
        'lose_repay',
        'trans_excess',
        'trans_t_lose',
        'trans_t_excess',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'trans_lose' => 'decimal:2',
        'u_price' => 'decimal:2',
        'trans_excess' => 'decimal:2',
        'trans_t_lose' => 'decimal:2',
        'trans_t_excess' => 'decimal:2',
        'registered_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    /**
     * RelaciÃ³n con transportista
     */
    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class, 't_id', 'id');
    }

    /**
     * RelaciÃ³n con entrega
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'd_id', 'id');
    }

    /**
     * RelaciÃ³n con acuerdo de transportista
     */
    public function transporterAgreement(): BelongsTo
    {
        return $this->belongsTo(TransporterAgreement::class, 'ta_id', 'id');
    }

    /**
     * RelaciÃ³n con pagos netos
     */
    public function paymentNet(): HasMany
    {
        return $this->hasMany(TransporterPaymentNet::class, 'tp_no', 'tp_no');
    }
}
