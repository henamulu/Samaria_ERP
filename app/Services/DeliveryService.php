<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Customer;
use App\Models\Transporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    /**
     * Crear una nueva entrega
     */
    public function createDelivery(array $data): Delivery
    {
        return DB::transaction(function () use ($data) {
            $delivery = Delivery::create($data);
            
            Log::info('Delivery created', [
                'delivery_id' => $delivery->id,
                'customer_id' => $delivery->project,
                'registered_by' => $data['registered_by'] ?? null,
            ]);
            
            return $delivery;
        });
    }

    /**
     * Actualizar estado de entrega
     */
    public function updateDeliveryStatus(int $deliveryId, string $status, ?string $approvedBy = null): Delivery
    {
        return DB::transaction(function () use ($deliveryId, $status, $approvedBy) {
            $delivery = Delivery::findOrFail($deliveryId);
            $delivery->status = $status;
            
            if ($approvedBy) {
                $delivery->approved_by = $approvedBy;
            }
            
            $delivery->save();
            
            return $delivery;
        });
    }

    /**
     * Confirmar entrega por cliente
     */
    public function confirmDelivery(int $deliveryId, array $data): Delivery
    {
        return DB::transaction(function () use ($deliveryId, $data) {
            $delivery = Delivery::findOrFail($deliveryId);
            
            $delivery->accepted_by = $data['accepted_by'] ?? null;
            $delivery->signed_by_customer = $data['signed_by_customer'] ?? 'Yes';
            $delivery->confirm_signed = $data['confirm_signed'] ?? 'Yes';
            $delivery->confirm_by = $data['confirm_by'] ?? null;
            $delivery->confirm_date = now();
            $delivery->status = 'Done';
            
            $delivery->save();
            
            return $delivery;
        });
    }

    /**
     * Obtener entregas por cliente
     */
    public function getDeliveriesByCustomer(int $customerId, array $filters = [])
    {
        $query = Delivery::where('project', $customerId)
            ->with(['customer', 'transporter', 'payment']);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('issue_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('issue_date', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('issue_date', 'desc')->get();
    }

    /**
     * Obtener entregas pendientes de confirmación
     */
    public function getPendingDeliveries()
    {
        return Delivery::where('status', 'Pending')
            ->with(['customer', 'transporter'])
            ->orderBy('issue_date', 'asc')
            ->get();
    }

    /**
     * Calcular totales de entregas por transportista
     */
    public function getTransporterTotals(int $transporterId, array $filters = [])
    {
        $query = Delivery::where('t_id', $transporterId)
            ->where('status', 'Done');
        
        if (isset($filters['date_from'])) {
            $query->where('issue_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('issue_date', '<=', $filters['date_to']);
        }
        
        return $query->selectRaw('
                COUNT(*) as total_deliveries,
                SUM(quantity) as total_quantity,
                SUM(total) as total_amount
            ')
            ->first();
    }
}
