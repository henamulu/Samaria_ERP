<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view payments');
        
        $query = Payment::with(['supplier']);
        
        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }
        
        $payments = $query->orderBy('payment_date', 'desc')
            ->paginate($request->get('per_page', 15));
        
        return response()->json($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create payments');
        
        $validated = $request->validate([
            'p_type' => 'required|string|max:50',
            'po_no' => 'nullable|string|max:50',
            'supplier_id' => 'nullable|exists:sam_supplier,id',
            'net_amount' => 'required|numeric|min:0',
            'bank' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:200',
            'payment_date' => 'nullable|date',
            'payment_ref_no' => 'nullable|string|max:50',
            'payment_type' => 'nullable|string|max:50',
            'cheque_no' => 'nullable|string|max:100',
            'payment_description' => 'nullable|string',
            'pay_to' => 'nullable|string|max:100',
        ]);
        
        $validated['registered_by'] = auth()->user()->user_name ?? 'System';
        
        $payment = $this->paymentService->createPaymentRequest($validated);
        
        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view payments');
        
        $payment->load(['supplier', 'deliveries', 'salesOrders']);
        
        return response()->json($payment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        $this->authorize('edit payments');
        
        $validated = $request->validate([
            'net_amount' => 'sometimes|numeric|min:0',
            'bank' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:200',
            'payment_date' => 'nullable|date',
            'payment_ref_no' => 'nullable|string|max:50',
            'cheque_no' => 'nullable|string|max:100',
            'payment_description' => 'nullable|string',
        ]);
        
        $payment->update($validated);
        
        return response()->json($payment);
    }

    /**
     * Aprobar pago
     */
    public function approve(Payment $payment): JsonResponse
    {
        $this->authorize('approve payments');
        
        $payment = $this->paymentService->approvePayment(
            $payment->id,
            auth()->user()->user_name ?? 'System'
        );
        
        return response()->json($payment);
    }

    /**
     * Procesar pago (Settled)
     */
    public function settle(Request $request, Payment $payment): JsonResponse
    {
        $this->authorize('settle payments');
        
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_ref_no' => 'nullable|string|max:50',
            'cheque_no' => 'nullable|string|max:100',
        ]);
        
        $payment = $this->paymentService->settlePayment($payment->id, $validated);
        
        return response()->json($payment);
    }

    /**
     * Obtener pagos pendientes a transportistas
     */
    public function unpaidTransporters(Request $request): JsonResponse
    {
        $this->authorize('view transporter payments');
        
        $filters = $request->only(['transporter_id', 'date_from', 'date_to']);
        
        $payments = $this->paymentService->getUnpaidTransporterPayments($filters);
        
        return response()->json($payments);
    }
}
