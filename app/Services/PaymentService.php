<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\TransporterPayment;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Crear solicitud de pago
     */
    public function createPaymentRequest(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = Payment::create([
                ...$data,
                'status' => 'Pending',
                'payment_status' => 'Pending',
                'date_registered' => now(),
            ]);
            
            Log::info('Payment request created', [
                'payment_id' => $payment->id,
                'p_no' => $payment->p_no,
                'net_amount' => $payment->net_amount,
            ]);
            
            return $payment;
        });
    }

    /**
     * Aprobar pago
     */
    public function approvePayment(int $paymentId, string $approvedBy): Payment
    {
        return DB::transaction(function () use ($paymentId, $approvedBy) {
            $payment = Payment::findOrFail($paymentId);
            
            $payment->approved_by = $approvedBy;
            $payment->status = 'Approved';
            $payment->payment_status = 'Approved';
            $payment->save();
            
            Log::info('Payment approved', [
                'payment_id' => $payment->id,
                'approved_by' => $approvedBy,
            ]);
            
            return $payment;
        });
    }

    /**
     * Procesar pago (Settled)
     */
    public function settlePayment(int $paymentId, array $data): Payment
    {
        return DB::transaction(function () use ($paymentId, $data) {
            $payment = Payment::findOrFail($paymentId);
            
            $payment->payment_date = $data['payment_date'] ?? now();
            $payment->payment_ref_no = $data['payment_ref_no'] ?? null;
            $payment->cheque_no = $data['cheque_no'] ?? null;
            $payment->status = 'Settled';
            $payment->payment_status = 'Settled';
            $payment->save();
            
            Log::info('Payment settled', [
                'payment_id' => $payment->id,
                'payment_ref_no' => $payment->payment_ref_no,
            ]);
            
            return $payment;
        });
    }

    /**
     * Calcular pagos pendientes a transportistas
     */
    public function getUnpaidTransporterPayments(array $filters = [])
    {
        $query = Delivery::where('status', 'Done')
            ->where('t_id', '!=', 'none')
            ->where('truck_plate_no', '!=', '')
            ->with(['transporter', 'customer']);
        
        if (isset($filters['transporter_id']) && $filters['transporter_id'] !== 'all') {
            $query->whereHas('transporter', function ($q) use ($filters) {
                $q->where('id', $filters['transporter_id']);
            });
        }
        
        if (isset($filters['date_from'])) {
            $query->where('issue_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('issue_date', '<=', $filters['date_to']);
        }
        
        return $query->get()
            ->groupBy(function ($delivery) {
                // Agrupar por propietario de camión (necesita tabla sam_transporter_agg)
                return $delivery->truck_plate_no;
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'owner' => $first->truck_plate_no,
                    'transporter' => $first->transporter,
                    'total_delivered' => $group->sum('total'),
                    'total_paid' => $group->whereNotNull('tp_id')->sum('total'),
                    'remaining' => $group->whereNull('tp_id')->sum('total'),
                    'deliveries' => $group->count(),
                ];
            })
            ->values();
    }

    /**
     * Crear pago a transportista
     */
    public function createTransporterPayment(array $data): TransporterPayment
    {
        return DB::transaction(function () use ($data) {
            $payment = TransporterPayment::create([
                ...$data,
                'status' => 'Pending',
            ]);
            
            // Actualizar entregas relacionadas
            if (isset($data['delivery_ids'])) {
                Delivery::whereIn('id', $data['delivery_ids'])
                    ->update(['tp_id' => $payment->id]);
            }
            
            Log::info('Transporter payment created', [
                'payment_id' => $payment->id,
                'transporter_id' => $payment->t_id ?? null,
            ]);
            
            return $payment;
        });
    }
}
