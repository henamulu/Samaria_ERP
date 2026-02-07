<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    public function __construct(
        protected DeliveryService $deliveryService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view deliveries');
        
        $filters = $request->only(['status', 'customer_id', 'transporter_id', 'date_from', 'date_to']);
        
        $query = Delivery::with(['customer', 'transporter', 'payment']);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['customer_id'])) {
            $query->where('project', $filters['customer_id']);
        }
        
        if (isset($filters['transporter_id'])) {
            $query->where('t_id', $filters['transporter_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('issue_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('issue_date', '<=', $filters['date_to']);
        }
        
        $deliveries = $query->orderBy('issue_date', 'desc')
            ->paginate($request->get('per_page', 15));
        
        return response()->json($deliveries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create deliveries');
        
        $validated = $request->validate([
            'd_no' => 'required|string|max:20',
            'source' => 'required|string|max:20',
            'used_for' => 'required|string|max:20',
            'project' => 'required|exists:sam_customer,id',
            'item' => 'required|string|max:20',
            'unit' => 'required|string|max:20',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'truck_plate_no' => 'nullable|string|max:200',
            'driver_name' => 'nullable|string|max:500',
            'delivered_to' => 'nullable|string|max:500',
            'issue_date' => 'required|date',
        ]);
        
        $validated['total'] = $validated['quantity'] * $validated['unit_price'];
        $validated['registered_by'] = auth()->user()->user_name ?? 'System';
        $validated['registered_date'] = now();
        $validated['status'] = 'Pending';
        
        $delivery = $this->deliveryService->createDelivery($validated);
        
        return response()->json($delivery, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Delivery $delivery): JsonResponse
    {
        $this->authorize('view deliveries');
        
        $delivery->load(['customer', 'transporter', 'payment', 'transporterPayment']);
        
        return response()->json($delivery);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Delivery $delivery): JsonResponse
    {
        $this->authorize('edit deliveries');
        
        $validated = $request->validate([
            'quantity' => 'sometimes|numeric|min:0',
            'unit_price' => 'sometimes|numeric|min:0',
            'truck_plate_no' => 'nullable|string|max:200',
            'driver_name' => 'nullable|string|max:500',
            'delivered_to' => 'nullable|string|max:500',
            'remark' => 'nullable|string',
        ]);
        
        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $quantity = $validated['quantity'] ?? $delivery->quantity;
            $unitPrice = $validated['unit_price'] ?? $delivery->unit_price;
            $validated['total'] = $quantity * $unitPrice;
        }
        
        $delivery->update($validated);
        
        return response()->json($delivery);
    }

    /**
     * Aprobar entrega
     */
    public function approve(Delivery $delivery): JsonResponse
    {
        $this->authorize('approve deliveries');
        
        $delivery = $this->deliveryService->updateDeliveryStatus(
            $delivery->id,
            'Done',
            auth()->user()->user_name ?? 'System'
        );
        
        return response()->json($delivery);
    }

    /**
     * Confirmar entrega por cliente
     */
    public function confirm(Request $request, Delivery $delivery): JsonResponse
    {
        $this->authorize('confirm deliveries');
        
        $validated = $request->validate([
            'accepted_by' => 'required|string|max:500',
            'signed_by_customer' => 'required|in:Yes,No',
            'confirm_signed' => 'required|in:Yes,No',
        ]);
        
        $validated['confirm_by'] = auth()->user()->user_name ?? 'System';
        
        $delivery = $this->deliveryService->confirmDelivery($delivery->id, $validated);
        
        return response()->json($delivery);
    }

    /**
     * Obtener entregas pendientes
     */
    public function pending(): JsonResponse
    {
        $this->authorize('view deliveries');
        
        $deliveries = $this->deliveryService->getPendingDeliveries();
        
        return response()->json($deliveries);
    }
}
