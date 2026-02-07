<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransporterPaymentNet extends Model
{
    protected $table = 'sam_transporter_payment_net';

    public $timestamps = false;
    
    protected $fillable = [
        'tp_no',
        'net_amount',
        'remaning',
        'paid_amount',
        'paid_date',
    ];

    protected $casts = [
        'net_amount' => 'decimal:2',
        'remaning' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    /**
     * RelaciÃ³n con pago de transportista
     */
    public function transporterPayment(): BelongsTo
    {
        return $this->belongsTo(TransporterPayment::class, 'tp_no', 'tp_no');
    }
}
