<?php

namespace App\Http\Controllers;

use App\Models\TransporterPayment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransporterPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view transporter payments');
        
        $query = TransporterPayment::with(['transporter', 'delivery']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('transporter_id')) {
            $query->where('t_id', $request->transporter_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('from_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('to_date', '<=', $request->date_to);
        }
        
        $payments = $query->orderBy('registered_date', 'desc')
            ->paginate($request->get('per_page', 15));
        
        return response()->json($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create transporter payments');
        
        $validated = $request->validate([
            't_id' => 'required|exists:sam_transporter,id',
            'delivery_ids' => 'required|array',
            'delivery_ids.*' => 'exists:sam_delivery,id',
            'unit_price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'owner' => 'nullable|string|max:20',
        ]);
        
        $validated['registered_by'] = auth()->user()->user_name ?? 'System';
        $validated['registered_date'] = now();
        $validated['status'] = 'Pending';
        
        $payment = $this->paymentService->createTransporterPayment($validated);
        
        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TransporterPayment $transporterPayment): JsonResponse
    {
        $this->authorize('view transporter payments');
        
        $transporterPayment->load(['transporter', 'delivery']);
        
        return response()->json($transporterPayment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransporterPayment $transporterPayment): JsonResponse
    {
        $this->authorize('edit transporter payments');
        
        $validated = $request->validate([
            'unit_price' => 'sometimes|numeric|min:0',
            'total' => 'sometimes|numeric|min:0',
            'trans_lose' => 'nullable|numeric',
            'trans_excess' => 'nullable|numeric',
        ]);
        
        $transporterPayment->update($validated);
        
        return response()->json($transporterPayment);
    }

    /**
     * Aprobar pago a transportista
     */
    public function approve(TransporterPayment $transporterPayment): JsonResponse
    {
        $this->authorize('approve transporter payments');
        
        $transporterPayment->update([
            'approved_by' => auth()->user()->user_name ?? 'System',
            'status' => 'Approved',
        ]);
        
        return response()->json($transporterPayment);
    }

    /**
     * Procesar pago (Settled)
     */
    public function settle(TransporterPayment $transporterPayment): JsonResponse
    {
        $this->authorize('settle transporter payments');
        
        $transporterPayment->update([
            'status' => 'Settled',
        ]);
        
        return response()->json($transporterPayment);
    }
}
