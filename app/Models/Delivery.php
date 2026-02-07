<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $table = 'sam_delivery';

    public $timestamps = false;
    
    protected $fillable = [
        'd_no',
        'source',
        'used_for',
        'project',
        'item',
        'unit',
        'quantity',
        'unit_price',
        'total',
        'remark',
        'type_of_asset',
        'issue_date',
        'voucher_type',
        'driver_name',
        'truck_plate_no',
        'delivered_to',
        'status',
        'registered_by',
        'registered_date',
        'checked_by',
        'approved_by',
        'siv_id',
        'git_id',
        't_id',
        'p_id',
        't_qty',
        'tp_id',
        'delivery_no',
        'pr_id',
        'supplier_qty',
        'daily_cob',
        'daily_cob_approve',
        'ex_id',
        'category',
        'old_date',
        'd_name_val',
        'accepted_by',
        'signed_by_customer',
        'previous_qty',
        'confirm_signed',
        'confirm_by',
        'confirm_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        't_qty' => 'decimal:2',
        'supplier_qty' => 'decimal:2',
        'previous_qty' => 'decimal:4',
        'issue_date' => 'date',
        'registered_date' => 'date',
        'confirm_date' => 'date',
    ];

    /**
     * RelaciÃ³n con cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'project', 'id');
    }

    /**
     * RelaciÃ³n con transportista
     */
    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class, 't_id', 'id');
    }

    /**
     * RelaciÃ³n con pago
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'p_id', 'id');
    }

    /**
     * RelaciÃ³n con pago de transportista
     */
    public function transporterPayment(): BelongsTo
    {
        return $this->belongsTo(TransporterPayment::class, 'tp_id', 'id');
    }
}
