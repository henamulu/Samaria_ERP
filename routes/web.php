<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\Transporter;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\BankBalance;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

// Helper function to generate next order number
function generateNextNumber($table, $column, $prefix) {
    try {
        $lastRecord = DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$lastRecord || !$lastRecord->$column) {
            return $prefix . '1';
        }
        
        // Extract number from last record (e.g., "SO123" -> 123, "SO1045" -> 1045)
        $lastNumber = preg_replace('/[^0-9]/', '', $lastRecord->$column);
        if (empty($lastNumber)) {
            return $prefix . '1';
        }
        $nextNumber = (int)$lastNumber + 1;
        
        return $prefix . $nextNumber;
    } catch (Exception $e) {
        // Fallback: simple increment based on count
        $count = DB::table($table)->where($column, 'like', $prefix . '%')->count();
        return $prefix . ($count + 1);
    }
}

// Public routes
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return Inertia::render('Login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate([
        'user_name' => 'required|string',
        'password' => 'required|string',
    ]);
    
    $user = User::where('user_name', $request->user_name)->first();
    
    if (!$user || !Hash::check($request->password, $user->user_password)) {
        return back()->withErrors([
            'user_name' => 'Invalid credentials provided.',
        ]);
    }
    
    Auth::login($user);
    $request->session()->regenerate();
    
    return redirect()->intended('/dashboard');
});

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        // Bank Balance - computed dynamically from sam_bank_balance table
        // This table is incremented on collection approval and decremented on payment approval
        $bankBalance = \DB::table('sam_bank_balance')->sum('balance') ?? 0;

        // Uncollected - only from Approved/Done SOs (actual receivables)
        // The 'remaning' field on each SO already tracks the unpaid portion
        // (it is decremented FIFO when collections are approved)
        $uncollected = \DB::table('sam_sales_order')
            ->whereIn('status', ['Approved', 'Done'])
            ->where('remaning', '>', 0)
            ->sum('remaning') ?? 0;

        // Unpaid Supplier - outstanding supplier credit balances
        $unpaidSupplier = \DB::table('sam_ext_payment_c')
            ->whereIn('status', ['Done', 'Checked', 'Approved'])
            ->where('balance', '>', 0)
            ->sum('balance') ?? 0;

        // Unpaid Transport - remaining transport obligations
        try {
            $unpaidTransport = \DB::selectOne("
                SELECT SUM(n.remaning) as total
                FROM sam_transporter_payment_net n
                INNER JOIN sam_transporter_payment p ON CONVERT(p.tp_no USING utf8mb4) = CONVERT(n.tp_no USING utf8mb4)
                WHERE p.status NOT IN ('void', 'Void')
                  AND n.remaning > 0
            ");
            $unpaidTransport = $unpaidTransport->total ?? 0;
        } catch (\Exception $e) {
            $unpaidTransport = \DB::table('sam_transporter_payment_net')
                ->where('remaning', '>', 0)
                ->sum('remaning') ?? 0;
        }
        
        // Expected VAT - IVA esperado basado en ventas pendientes
        $expectedVAT = \DB::table('sam_sales_order')
            ->where('status', '!=', 'Void')
            ->where('remaning', '>', 0)
            ->sum(\DB::raw('(remaning * 0.15) / 1.15')) ?? 0;
        
        // Unearned Income - ingresos recibidos pero no devengados
        // Avances recibidos de clientes
        $unearnedIncome = \DB::table('sam_payment')
            ->where('p_type', 'advance')
            ->where('status', 'Approved')
            ->where('advance_status', '!=', 'Settled')
            ->sum('net_amount') ?? 0;
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'customers' => Customer::count(),
                'suppliers' => Supplier::count(),
                'transporters' => Transporter::count(),
                'totalDeliveries' => Delivery::count(),
                'pendingDeliveries' => Delivery::where('status', 'Pending')->count(),
                'totalPayments' => Payment::count(),
                'pendingPayments' => Payment::where('status', 'Pending')->count(),
                'users' => User::count(),
                'salesOrders' => SalesOrder::count(),
                'purchaseOrders' => PurchaseOrder::count(),
                'bankBalance' => $bankBalance,
                'uncollected' => $uncollected,
                'unpaidSupplier' => $unpaidSupplier,
                'unpaidTransport' => $unpaidTransport,
                'expectedVAT' => $expectedVAT,
                'unearnedIncome' => $unearnedIncome,
            ]
        ]);
    })->name('dashboard');

    // ==================== CUSTOMERS ====================
    Route::get('/customers', function (Request $request) {
        $query = Customer::query();
        
        if ($request->search) {
            $query->where('company_name', 'like', "%{$request->search}%")
                  ->orWhere('tin_no', 'like', "%{$request->search}%")
                  ->orWhere('contact_person', 'like', "%{$request->search}%");
        }
        
        $customers = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return Inertia::render('Customers/Index', ['customers' => $customers, 'filters' => $request->only('search')]);
    })->name('customers.index');

    Route::get('/customers/create', function () {
        return Inertia::render('Customers/Create');
    })->name('customers.create');

    Route::post('/customers', function (Request $request) {
        $validated = $request->validate([
            'company_name' => 'required|string|max:500',
            'customer_type' => 'required|string|max:20',
            'tin_no' => 'required|string|max:20',
            'contact_person' => 'nullable|string|max:500',
            'phone_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:500',
            'office_location' => 'nullable|string|max:500',
        ]);
        
        $validated['status'] = 'Pending';
        $validated['registered_by'] = auth()->user()->user_name ?? 'Admin';
        $validated['withholding'] = $request->withholding ?? 'No';
        $validated['withhold'] = $request->withhold ?? '0';
        
        Customer::create($validated);
        
        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    })->name('customers.store');

    Route::get('/customers/{customer}/edit', function (Customer $customer) {
        return Inertia::render('Customers/Edit', ['customer' => $customer]);
    })->name('customers.edit');

    Route::put('/customers/{customer}', function (Request $request, Customer $customer) {
        $validated = $request->validate([
            'company_name' => 'required|string|max:500',
            'customer_type' => 'required|string|max:20',
            'tin_no' => 'required|string|max:20',
            'contact_person' => 'nullable|string|max:500',
            'phone_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:500',
            'office_location' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:20',
        ]);
        
        $customer->update($validated);
        
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    })->name('customers.update');

    Route::delete('/customers/{customer}', function (Customer $customer) {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    })->name('customers.destroy');

    // ==================== SUPPLIERS ====================
    Route::get('/suppliers', function (Request $request) {
        $query = Supplier::query();
        
        if ($request->search) {
            $query->where('supplier_name', 'like', "%{$request->search}%")
                  ->orWhere('supplier_tin', 'like', "%{$request->search}%");
        }
        
        $suppliers = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return Inertia::render('Suppliers/Index', ['suppliers' => $suppliers, 'filters' => $request->only('search')]);
    })->name('suppliers.index');

    Route::get('/suppliers/create', function () {
        return Inertia::render('Suppliers/Create');
    })->name('suppliers.create');

    Route::post('/suppliers', function (Request $request) {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:500',
            'supplier_tin' => 'required|string|max:20',
            'supplier_category' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);
        
        $validated['status'] = 'Active';
        $validated['registered_by'] = auth()->user()->user_name ?? 'Admin';
        
        Supplier::create($validated);
        
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully');
    })->name('suppliers.store');

    Route::get('/suppliers/{supplier}/edit', function (Supplier $supplier) {
        return Inertia::render('Suppliers/Edit', ['supplier' => $supplier]);
    })->name('suppliers.edit');

    Route::put('/suppliers/{supplier}', function (Request $request, Supplier $supplier) {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:500',
            'supplier_tin' => 'required|string|max:20',
            'supplier_category' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:50',
        ]);
        
        $supplier->update($validated);
        
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully');
    })->name('suppliers.update');

    Route::delete('/suppliers/{supplier}', function (Supplier $supplier) {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully');
    })->name('suppliers.destroy');

    // ==================== TRANSPORTERS ====================
    Route::get('/transporters', function (Request $request) {
        $query = Transporter::query();
        
        if ($request->search) {
            $query->where('company_name', 'like', "%{$request->search}%")
                  ->orWhere('tin_no', 'like', "%{$request->search}%");
        }
        
        $transporters = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return Inertia::render('Transporters/Index', ['transporters' => $transporters, 'filters' => $request->only('search')]);
    })->name('transporters.index');

    Route::get('/transporters/create', function () {
        return Inertia::render('Transporters/Create');
    })->name('transporters.create');

    Route::post('/transporters', function (Request $request) {
        $validated = $request->validate([
            'company_name' => 'required|string|max:500',
            'transporter_type' => 'required|string|max:20',
            'tin_no' => 'required|string|max:20',
            'firstname' => 'nullable|string|max:500',
            'lastname' => 'nullable|string|max:500',
            'phone_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:500',
            'contact_person' => 'nullable|string|max:500',
            'office_location' => 'nullable|string|max:500',
        ]);
        
        $validated['status'] = 'Active';
        $validated['registered_by'] = auth()->user()->user_name ?? 'Admin';
        $validated['withholding'] = $request->withholding ?? 'No';
        $validated['withhold'] = $request->withhold ?? '0';
        
        Transporter::create($validated);
        
        return redirect()->route('transporters.index')->with('success', 'Transporter created successfully');
    })->name('transporters.store');

    Route::get('/transporters/{transporter}/edit', function (Transporter $transporter) {
        return Inertia::render('Transporters/Edit', ['transporter' => $transporter]);
    })->name('transporters.edit');

    Route::put('/transporters/{transporter}', function (Request $request, Transporter $transporter) {
        $validated = $request->validate([
            'company_name' => 'required|string|max:500',
            'transporter_type' => 'required|string|max:20',
            'tin_no' => 'required|string|max:20',
            'firstname' => 'nullable|string|max:500',
            'lastname' => 'nullable|string|max:500',
            'phone_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:500',
            'contact_person' => 'nullable|string|max:500',
            'office_location' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:20',
        ]);
        
        $transporter->update($validated);
        
        return redirect()->route('transporters.index')->with('success', 'Transporter updated successfully');
    })->name('transporters.update');

    Route::delete('/transporters/{transporter}', function (Transporter $transporter) {
        $transporter->delete();
        return redirect()->route('transporters.index')->with('success', 'Transporter deleted successfully');
    })->name('transporters.destroy');

    // ==================== DELIVERIES ====================
    Route::get('/deliveries', function (Request $request) {
        $query = Delivery::query();
        
        if ($request->search) {
            $query->where('d_no', 'like', "%{$request->search}%")
                  ->orWhere('item', 'like', "%{$request->search}%")
                  ->orWhere('truck_plate_no', 'like', "%{$request->search}%");
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->date_from) {
            $query->where('issue_date', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->where('issue_date', '<=', $request->date_to);
        }
        
        $deliveries = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $statuses = Delivery::distinct()->pluck('status')->filter();
        
        return Inertia::render('Deliveries/Index', [
            'deliveries' => $deliveries,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
            'statuses' => $statuses
        ]);
    })->name('deliveries.index');

    Route::get('/deliveries/create', function () {
        $nextDNo = generateNextNumber('sam_delivery', 'd_no', 'DEL');
        
        return Inertia::render('Deliveries/Create', [
            'customers' => Customer::orderBy('company_name')->get(),
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
            'salesOrders' => SalesOrder::where('status', 'Approved')->orderBy('id', 'desc')->limit(100)->get(),
            'gits' => \DB::table('sam_git')->whereNotIn('status', ['Done', 'Void'])->orderBy('id', 'desc')->limit(100)->get(),
            'nextDNo' => $nextDNo,
        ]);
    })->name('deliveries.create');

    Route::post('/deliveries', function (Request $request) {
        // Auto-generate delivery number if not provided
        $dNo = !empty($request->d_no) ? $request->d_no : generateNextNumber('sam_delivery', 'd_no', 'DEL');
        
        $validated = $request->validate([
            'd_no' => 'required|string|max:20',
            'issue_date' => 'required|date',
            'project' => 'required|string',
            'item' => 'required|string|max:20',
        ]);

        // Validate: Sales Order must exist and be Approved (only for customer deliveries)
        if (!empty($request->siv_id)) {
            $so = SalesOrder::find($request->siv_id);
            if (!$so || $so->status !== 'Approved') {
                return back()->withErrors([
                    'siv_id' => 'The linked Sales Order must exist and be Approved before creating a delivery',
                ]);
            }
        }

        // Validate: quantity must not exceed the Sales Order quantity
        if (!empty($request->siv_id)) {
            $so = SalesOrder::find($request->siv_id);
            if ($so) {
                // Sum of all existing deliveries for this SO
                $alreadyDelivered = Delivery::where('siv_id', $so->id)
                    ->where('status', '!=', 'Rejected')
                    ->sum('quantity');
                $newTotal = $alreadyDelivered + (float)$request->quantity;
                if ($newTotal > (float)$so->quantity) {
                    return back()->withErrors([
                        'quantity' => 'Total delivered quantity (' . $newTotal . ') would exceed the Sales Order quantity (' . $so->quantity . ')',
                    ]);
                }
            }
        }
        
        $data = $request->all();
        $data['d_no'] = $dNo; // Use generated or provided number
        $data['registered_by'] = auth()->user()->user_name ?? 'Admin';
        $data['registered_date'] = now()->format('Y-m-d');
        $data['source'] = $data['source'] ?? '';
        $data['used_for'] = $data['used_for'] ?? '';
        $data['type_of_asset'] = $data['type_of_asset'] ?? '';
        $data['voucher_type'] = $data['voucher_type'] ?? '';
        $data['siv_id'] = $data['siv_id'] ?? '';
        $data['git_id'] = $data['git_id'] ?? '';
        $data['t_id'] = $data['t_id'] ?? 'none';
        $data['p_id'] = $data['p_id'] ?? '';
        $data['t_qty'] = $data['t_qty'] ?? 0;
        $data['tp_id'] = $data['tp_id'] ?? '';
        $data['delivery_no'] = $data['delivery_no'] ?? '';
        $data['pr_id'] = $data['pr_id'] ?? '';
        $data['supplier_qty'] = $data['supplier_qty'] ?? 0;
        $data['daily_cob'] = $data['daily_cob'] ?? '';
        $data['daily_cob_approve'] = $data['daily_cob_approve'] ?? '';
        $data['ex_id'] = $data['ex_id'] ?? '';
        $data['category'] = $data['category'] ?? '';
        $data['old_date'] = $data['old_date'] ?? '';
        $data['d_name_val'] = $data['d_name_val'] ?? '';
        $data['signed_by_customer'] = $data['signed_by_customer'] ?? '';
        $data['previous_qty'] = $data['previous_qty'] ?? 0;
        $data['confirm_signed'] = $data['confirm_signed'] ?? '';
        $data['confirm_date'] = $data['confirm_date'] ?? '';
        
        Delivery::create($data);
        
        return redirect()->route('deliveries.index')->with('success', 'Delivery created successfully');
    })->name('deliveries.store');

    Route::get('/deliveries/reconciliation', function (Request $request) {
        $date = $request->date ?: now()->subDay()->format('Y-m-d');
        
        $deliveries = Delivery::where('issue_date', $date)
            ->where(function($query) {
                $query->whereNull('daily_cob')
                      ->orWhere('daily_cob', '');
            })
            ->orderBy('d_no')
            ->get();
        
        return Inertia::render('Deliveries/Reconciliation', [
            'deliveries' => $deliveries,
            'defaultDate' => $date
        ]);
    })->name('deliveries.reconciliation');

    Route::post('/deliveries/reconciliation', function (Request $request) {
        $request->validate([
            'deliveries' => 'required|array',
            'deliveries.*.id' => 'required|exists:sam_delivery,id',
            'deliveries.*.supplier_qty' => 'nullable|numeric|min:0',
        ]);

        foreach ($request->deliveries as $item) {
            $delivery = Delivery::findOrFail($item['id']);
            $supplierQty = (float)($item['supplier_qty'] ?? 0);
            $deliveredQty = (float)($delivery->quantity ?? 0);
            
            // Calculate shortage and surplus
            $shortage = max(0, $supplierQty - $deliveredQty);
            $surplus = max(0, $deliveredQty - $supplierQty);
            
            // Update delivery with reconciliation data
            $delivery->update([
                'supplier_qty' => $supplierQty,
                'daily_cob' => 'Done',
                'daily_cob_approve' => 'Pending',
            ]);
            
            // Update linked TransporterPayment if exists
            if (!empty($delivery->tp_id)) {
                $transPayment = \DB::table('sam_transporter_payment')->where('id', $delivery->tp_id)->first();
                if ($transPayment) {
                    \DB::table('sam_transporter_payment')
                        ->where('id', $delivery->tp_id)
                        ->update([
                            'trans_lose' => $shortage,
                            'trans_excess' => $surplus,
                        ]);
                }
            }
        }

        return redirect()->route('deliveries.reconciliation')->with('success', 'Reconciliation submitted successfully');
    })->name('deliveries.reconciliation.submit');

    Route::put('/deliveries/reconciliation/{id}/approve', function ($id) {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['error' => 'Unauthorized: Admin role required'], 403);
        }
        
        $delivery = Delivery::findOrFail($id);
        $delivery->update(['daily_cob_approve' => 'Approved']);
        
        return response()->json(['success' => true]);
    })->name('deliveries.reconciliation.approve');

    Route::get('/deliveries/{delivery}/edit', function (Delivery $delivery) {
        return Inertia::render('Deliveries/Edit', [
            'delivery' => $delivery,
            'customers' => Customer::orderBy('company_name')->get(),
            'salesOrders' => SalesOrder::where('status', 'Approved')->orderBy('id', 'desc')->limit(100)->get(),
            'gits' => \DB::table('sam_git')->whereNotIn('status', ['Done', 'Void'])->orderBy('id', 'desc')->limit(100)->get(),
        ]);
    })->name('deliveries.edit');

    Route::put('/deliveries/{delivery}', function (Request $request, Delivery $delivery) {
        $validated = $request->validate([
            'item'            => 'sometimes|string|max:20',
            'unit'            => 'sometimes|string|max:20',
            'quantity'        => 'sometimes|numeric|min:0',
            'unit_price'      => 'sometimes|numeric|min:0',
            'truck_plate_no'  => 'nullable|string|max:200',
            'driver_name'     => 'nullable|string|max:500',
            'delivered_to'    => 'nullable|string|max:500',
            'remark'          => 'nullable|string',
        ]);

        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $qty   = $validated['quantity']   ?? $delivery->quantity;
            $price = $validated['unit_price'] ?? $delivery->unit_price;
            $validated['total'] = $qty * $price;
        }

        $delivery->update($validated);
        return redirect()->route('deliveries.index')->with('success', 'Delivery updated successfully');
    })->name('deliveries.update');

    Route::delete('/deliveries/{delivery}', function (Delivery $delivery) {
        $delivery->delete();
        return redirect()->route('deliveries.index')->with('success', 'Delivery deleted successfully');
    })->name('deliveries.destroy');

    // ==================== PAYMENTS ====================
    Route::get('/payments', function (Request $request) {
        $query = Payment::with('supplier');
        
        if ($request->search) {
            $query->where('p_no', 'like', "%{$request->search}%")
                  ->orWhere('pay_to', 'like', "%{$request->search}%");
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $payments = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $statuses = Payment::distinct()->pluck('status')->filter();
        
        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'status']),
            'statuses' => $statuses
        ]);
    })->name('payments.index');

    Route::get('/payments/create', function () {
        return Inertia::render('Payments/Create', [
            'suppliers' => Supplier::orderBy('supplier_name')->get()
        ]);
    })->name('payments.create');

    Route::post('/payments', function (Request $request) {
        $data = $request->all();
        $data['registered_by'] = auth()->user()->user_name ?? 'Admin';
        $data['date_registered'] = now()->format('Y-m-d');
        $data['bank_id'] = $data['bank_id'] ?? 0;
        $data['po_no'] = $data['po_no'] ?? '';
        $data['balance_type'] = $data['balance_type'] ?? '';
        $data['payment_status'] = $data['payment_status'] ?? '';
        $data['pdc_date'] = $data['pdc_date'] ?? '';
        $data['pdc_update_date'] = $data['pdc_update_date'] ?? '';
        $data['pdc_update_by'] = $data['pdc_update_by'] ?? '';
        $data['account_no'] = $data['account_no'] ?? '';
        $data['internal_payment_request'] = $data['internal_payment_request'] ?? '';
        $data['payment_handover'] = $data['payment_handover'] ?? '';
        $data['checked_by'] = $data['checked_by'] ?? '';
        $data['approved_by'] = $data['approved_by'] ?? '';
        $data['item_desc'] = $data['item_desc'] ?? '';
        $data['advance_balance'] = $data['advance_balance'] ?? 0;
        $data['supplier_id'] = $data['supplier_id'] ?? 0;
        $data['settlement_id'] = $data['settlement_id'] ?? '';
        $data['advance_status'] = $data['advance_status'] ?? '';
        $data['le_id'] = $data['le_id'] ?? '';
        $data['outstand'] = $data['outstand'] ?? '';
        $data['outstand_date'] = $data['outstand_date'] ?? '';
        $data['withhold'] = $data['withhold'] ?? '';
        $data['cr_status'] = $data['cr_status'] ?? '';
        $data['recon'] = $data['recon'] ?? '';
        $data['not_paid'] = $data['not_paid'] ?? 0;
        $data['refund_by'] = $data['refund_by'] ?? '';
        $data['refund_status'] = $data['refund_status'] ?? '';
        $data['rejection_status'] = $data['rejection_status'] ?? '';
        $data['rejection_reason'] = $data['rejection_reason'] ?? '';
        
        Payment::create($data);
        
        return redirect()->route('payments.index')->with('success', 'Payment created successfully');
    })->name('payments.store');

    Route::get('/payments/{payment}/edit', function (Payment $payment) {
        return Inertia::render('Payments/Edit', [
            'payment' => $payment,
            'suppliers' => Supplier::orderBy('supplier_name')->get()
        ]);
    })->name('payments.edit');

    Route::put('/payments/{payment}', function (Request $request, Payment $payment) {
        $validated = $request->validate([
            'net_amount'          => 'sometimes|numeric|min:0',
            'bank'                => 'nullable|string|max:100',
            'branch'              => 'nullable|string|max:200',
            'payment_date'        => 'nullable|date',
            'payment_ref_no'      => 'nullable|string|max:50',
            'cheque_no'           => 'nullable|string|max:100',
            'payment_description' => 'nullable|string',
            'pay_to'              => 'nullable|string|max:100',
        ]);

        $payment->update($validated);
        return redirect()->route('payments.index')->with('success', 'Payment updated successfully');
    })->name('payments.update');

    Route::delete('/payments/{payment}', function (Payment $payment) {
        $payment->delete();
        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully');
    })->name('payments.destroy');

    // ==================== SALES ORDERS ====================
    Route::get('/sales-orders', function (Request $request) {
        $query = SalesOrder::query();
        
        if ($request->search) {
            $query->where('so_no', 'like', "%{$request->search}%")
                  ->orWhere('item', 'like', "%{$request->search}%");
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $salesOrders = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $statuses = SalesOrder::distinct()->pluck('status')->filter();
        
        return Inertia::render('SalesOrders/Index', [
            'salesOrders' => $salesOrders,
            'filters' => $request->only(['search', 'status']),
            'statuses' => $statuses
        ]);
    })->name('sales-orders.index');

    Route::get('/sales-orders/create', function (Request $request) {
        $piData = null;
        if ($request->pi_id) {
            $pi = \DB::table('sam_proforma')->where('id', $request->pi_id)->first();
            if ($pi) {
                $piData = [
                    'pi_id' => $pi->id,
                    'pi_no' => $pi->pi_no,
                    'customer' => $pi->customer,
                    'item_id' => $pi->item_id,
                    'item' => $pi->item,
                    'quantity' => $pi->quantity,
                    'unit' => $pi->unit,
                    'unit_price' => $pi->unit_price,
                    'tax_p' => $pi->tax_p,
                ];
            }
        }
        
        $nextSoNo = generateNextNumber('sam_sales_order', 'so_no', 'SO');
        
        return Inertia::render('SalesOrders/Create', [
            'customers' => Customer::orderBy('company_name')->get(),
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
            'piData' => $piData,
            'nextSoNo' => $nextSoNo,
        ]);
    })->name('sales-orders.create');

    Route::post('/sales-orders', function (Request $request) {
        $data = $request->all();
        // Auto-generate SO number if not provided
        if (empty($data['so_no'])) {
            $data['so_no'] = generateNextNumber('sam_sales_order', 'so_no', 'SO');
        }
        $data['registered_by'] = auth()->user()->user_name ?? 'Admin';
        $data['status']        = 'Pending';
        $data['from_dep'] = $data['from_dep'] ?? '';
        $data['to_dep'] = $data['to_dep'] ?? '';
        $data['type'] = $data['type'] ?? '';
        $data['item_id'] = $data['item_id'] ?? '';
        $data['tax_p'] = $data['tax_p'] ?? '';
        $data['remaning'] = $data['total'] ?? 0;
        $data['p_id'] = $data['p_id'] ?? '';
        $data['p_doc'] = $data['p_doc'] ?? '';
        $data['i_id'] = $data['i_id'] ?? '';
        $data['pr_id'] = $data['pr_id'] ?? '';
        $data['pi_id'] = $data['pi_id'] ?? '';
        $data['t_km'] = $data['t_km'] ?? 0;
        $data['t_tax_p'] = $data['t_tax_p'] ?? '';
        $data['transport_unit'] = $data['transport_unit'] ?? '';
        $data['t_unit_price'] = $data['t_unit_price'] ?? 0;
        $data['transport'] = $data['transport'] ?? '';
        $data['invoice_type'] = $data['invoice_type'] ?? '';
        $data['agg_id'] = $data['agg_id'] ?? '';
        $data['previous_price'] = $data['previous_price'] ?? 0;
        $data['d_status'] = $data['d_status'] ?? '';
        $data['requested_price'] = $data['requested_price'] ?? 0;
        // Convert empty string to null for date fields
        $data['e_date'] = !empty($data['e_date']) ? $data['e_date'] : null;
        
        SalesOrder::create($data);
        
        return redirect()->route('sales-orders.index')->with('success', 'Sales Order created successfully');
    })->name('sales-orders.store');

    Route::get('/sales-orders/{salesOrder}/edit', function (SalesOrder $salesOrder) {
        return Inertia::render('SalesOrders/Edit', [
            'salesOrder' => $salesOrder,
            'customers' => Customer::orderBy('company_name')->get()
        ]);
    })->name('sales-orders.edit');

    Route::put('/sales-orders/{salesOrder}', function (Request $request, SalesOrder $salesOrder) {
        $validated = $request->validate([
            'so_no'           => 'sometimes|string|max:20',
            'registered_date' => 'sometimes|date',
            'customer'        => 'sometimes|integer',
            'item'            => 'sometimes|string|max:20',
            'unit'            => 'sometimes|string|max:20',
            'quantity'        => 'sometimes|numeric|min:0',
            'unit_price'      => 'sometimes|numeric|min:0',
            'total'           => 'sometimes|numeric|min:0',
            'payment_type'    => 'sometimes|string|max:20',
            'status'          => 'sometimes|string|max:20',
            'location'        => 'nullable|string|max:20',
            'remark'          => 'nullable|string',
            'e_date'          => 'nullable|date',
        ]);

        // Phase 5B: Price immutability after approval
        if (in_array($salesOrder->status, ['Approved', 'Done'])) {
            if (isset($validated['unit_price']) && $validated['unit_price'] != $salesOrder->unit_price) {
                return back()->withErrors(['unit_price' => 'Price cannot be changed after approval']);
            }
        }

        // Block other updates once the order has left Pending (except price check above)
        if ($salesOrder->status !== 'Pending' && !in_array($salesOrder->status, ['Approved', 'Done'])) {
            return back()->withErrors(['error' => 'Cannot edit a Sales Order that is no longer Pending']);
        }

        // Calculate total and remaining if quantity or price changed
        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $qty   = $validated['quantity']   ?? $salesOrder->quantity;
            $price = $validated['unit_price'] ?? $salesOrder->unit_price;
            $validated['total']    = $qty * $price;
            $validated['remaning'] = $qty * $price;   // reset remaining to new total
        }

        $salesOrder->update($validated);
        return redirect()->route('sales-orders.index')->with('success', 'Sales Order updated successfully');
    })->name('sales-orders.update');

    Route::delete('/sales-orders/{salesOrder}', function (SalesOrder $salesOrder) {
        $salesOrder->delete();
        return redirect()->route('sales-orders.index')->with('success', 'Sales Order deleted successfully');
    })->name('sales-orders.destroy');

    // ==================== PURCHASE ORDERS ====================
    Route::get('/purchase-orders', function (Request $request) {
        $query = PurchaseOrder::query();
        
        if ($request->search) {
            $query->where('po_no', 'like', "%{$request->search}%");
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $purchaseOrders = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $statuses = PurchaseOrder::distinct()->pluck('status')->filter();
        
        return Inertia::render('PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => $request->only(['search', 'status']),
            'statuses' => $statuses
        ]);
    })->name('purchase-orders.index');

    Route::get('/purchase-orders/create', function (Request $request) {
        $prData = null;
        if ($request->pr_id) {
            $pr = \DB::table('sam_purchase_requisition')->where('id', $request->pr_id)->first();
            if ($pr) {
                $item = \DB::table('sam_item')->where('id', $pr->item)->first();
                $prData = [
                    'pr_id' => $pr->id,
                    'pr_no' => $pr->pr_no,
                    'item' => $pr->item,
                    'item_desc' => $pr->item_desc,
                    'quantity' => $pr->pr_quantity,
                    'unit' => $pr->unit,
                    'used_for' => $pr->used_for,
                    'request_from' => $pr->request_from,
                ];
            }
        }
        
        $nextPoNo = generateNextNumber('sam_purchase_order', 'po_no', 'PO');
        
        return Inertia::render('PurchaseOrders/Create', [
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
            'prData' => $prData,
            'nextPoNo' => $nextPoNo,
        ]);
    })->name('purchase-orders.create');

    Route::post('/purchase-orders', function (Request $request) {
        $data = $request->all();
        // Auto-generate PO number if not provided
        if (empty($data['po_no'])) {
            $data['po_no'] = generateNextNumber('sam_purchase_order', 'po_no', 'PO');
        }
        $data['registered_by'] = auth()->user()->user_name ?? 'Admin';
        $data['status']        = 'Pending';
        $data['e_date'] = $data['e_date'] ?? '';
        $data['p_doc'] = $data['p_doc'] ?? '';
        $data['i_id'] = $data['i_id'] ?? '';
        $data['pr_id'] = $data['pr_id'] ?? '';
        $data['pi_id'] = $data['pi_id'] ?? '';
        $data['t_km'] = $data['t_km'] ?? 0;
        $data['t_tax_p'] = $data['t_tax_p'] ?? '';
        $data['transport_unit'] = $data['transport_unit'] ?? '';
        $data['t_unit_price'] = $data['t_unit_price'] ?? 0;
        $data['transport'] = $data['transport'] ?? '';
        $data['invoice_type'] = $data['invoice_type'] ?? '';
        $data['agg_id'] = $data['agg_id'] ?? '';
        $data['previous_price'] = $data['previous_price'] ?? 0;
        $data['d_status'] = $data['d_status'] ?? '';
        $data['requested_price'] = $data['requested_price'] ?? 0;
        
        PurchaseOrder::create($data);
        
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order created successfully');
    })->name('purchase-orders.store');

    Route::get('/purchase-orders/{purchaseOrder}/edit', function (PurchaseOrder $purchaseOrder) {
        return Inertia::render('PurchaseOrders/Edit', [
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => Supplier::orderBy('supplier_name')->get()
        ]);
    })->name('purchase-orders.edit');

    Route::put('/purchase-orders/{purchaseOrder}', function (Request $request, PurchaseOrder $purchaseOrder) {
        $validated = $request->validate([
            'supplier'   => 'sometimes|string',
            'item'       => 'sometimes|string|max:20',
            'unit'       => 'sometimes|string|max:20',
            'quantity'   => 'sometimes|numeric|min:0',
            'unit_price' => 'sometimes|numeric|min:0',
            'total'      => 'sometimes|numeric|min:0',
            'remark'     => 'nullable|string',
        ]);

        // Phase 5B: Price immutability after approval
        if (in_array($purchaseOrder->status, ['Approved', 'Done'])) {
            if (isset($validated['unit_price']) && $validated['unit_price'] != $purchaseOrder->unit_price) {
                return back()->withErrors(['unit_price' => 'Price cannot be changed after approval']);
            }
        }

        // Block other updates once the order has left Pending (except price check above)
        if ($purchaseOrder->status !== 'Pending' && !in_array($purchaseOrder->status, ['Approved', 'Done'])) {
            return back()->withErrors(['error' => 'Cannot edit a Purchase Order that is no longer Pending']);
        }

        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $qty   = $validated['quantity']   ?? $purchaseOrder->quantity;
            $price = $validated['unit_price'] ?? $purchaseOrder->unit_price;
            $validated['total'] = $qty * $price;
        }

        $purchaseOrder->update($validated);
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order updated successfully');
    })->name('purchase-orders.update');

    Route::delete('/purchase-orders/{purchaseOrder}', function (PurchaseOrder $purchaseOrder) {
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order deleted successfully');
    })->name('purchase-orders.destroy');

    Route::put('/purchase-orders/{id}/check', function ($id) {
        $po = \DB::table('sam_purchase_order')->where('id', $id)->first();
        if (!$po || $po->status !== 'Pending') {
            return back()->withErrors(['error' => 'Only Pending Purchase Orders can be checked']);
        }
        \DB::table('sam_purchase_order')->where('id', $id)->update([
            'status'     => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order checked');
    })->name('purchase-orders.check');

    Route::put('/purchase-orders/{id}/approve', function ($id) {
        $po = \DB::table('sam_purchase_order')->where('id', $id)->first();
        if (!$po || $po->status !== 'Checked') {
            return back()->withErrors(['error' => 'Only Checked Purchase Orders can be approved']);
        }
        \DB::table('sam_purchase_order')->where('id', $id)->update([
            'status'      => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order approved');
    })->name('purchase-orders.approve');

    // ==================== USERS ====================
    Route::get('/users', function (Request $request) {
        $query = User::query();
        
        if ($request->search) {
            $query->where('user_name', 'like', "%{$request->search}%")
                  ->orWhere('firstname', 'like', "%{$request->search}%")
                  ->orWhere('lastname', 'like', "%{$request->search}%")
                  ->orWhere('user_email', 'like', "%{$request->search}%");
        }
        
        $users = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        
        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
            'currentUserId' => auth()->id()
        ]);
    })->name('users.index');

    Route::get('/users/create', function () {
        return Inertia::render('Users/Create', [
            'roles' => ['Admin', 'Supervisor', 'User', 'Cashier']
        ]);
    })->name('users.create');

    Route::post('/users', function (Request $request) {
        $request->validate([
            'user_name' => 'required|string|max:20|unique:sam_user,user_name',
            'password' => 'required|string|min:6',
            'firstname' => 'required|string|max:500',
            'lastname' => 'required|string|max:500',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'user_name' => $request->user_name,
            'user_password' => Hash::make($request->password),
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'user_email' => $request->user_email,
            'phone_no' => $request->phone_no,
            'role' => $request->role,
            'department' => $request->department,
            'user_status' => 'Active',
            'status' => 'Old',
            'registered_by' => auth()->user()->user_name,
            'date_registered' => now(),
        ]);

        // Assign Spatie role to sync with the legacy role column
        try {
            $user->syncRoles([$request->role]);
        } catch (\Exception $e) {
            // Spatie roles may not be seeded yet; legacy role column is still set above
            \Log::warning('Failed to assign Spatie role: ' . $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User created successfully');
    })->name('users.store');

    Route::get('/users/{user}/edit', function (User $user) {
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => ['Admin', 'Supervisor', 'User', 'Cashier']
        ]);
    })->name('users.edit');

    Route::put('/users/{user}', function (Request $request, User $user) {
        $request->validate([
            'user_name' => 'required|string|max:20|unique:sam_user,user_name,' . $user->id,
            'firstname' => 'required|string|max:500',
            'lastname' => 'required|string|max:500',
            'role' => 'required|string',
        ]);

        $data = [
            'user_name' => $request->user_name,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'user_email' => $request->user_email,
            'phone_no' => $request->phone_no,
            'role' => $request->role,
            'user_status' => $request->user_status,
            'department' => $request->department,
        ];

        if ($request->filled('password')) {
            $data['user_password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync Spatie role with the legacy role column
        try {
            $user->syncRoles([$request->role]);
        } catch (\Exception $e) {
            // Spatie roles may not be seeded yet; legacy role column is still updated above
            \Log::warning('Failed to sync Spatie role: ' . $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    })->name('users.update');

    Route::delete('/users/{user}', function (User $user) {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Cannot delete yourself']);
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    })->name('users.destroy');

    // ==================== CUSTOMER AGREEMENTS ====================
    Route::get('/customer-agreements', function (Request $request) {
        $query = \DB::table('sam_customer_agg')
            ->where('status', '!=', 'pending')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('agg_no', 'like', "%{$request->search}%")
                  ->orWhere('item', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $agreements = $query->paginate(20)->withQueryString();
        
        // Add customer data
        foreach ($agreements as $agg) {
            $agg->customer = Customer::find($agg->customer);
        }
        
        return Inertia::render('CustomerAgreements/Index', [
            'agreements' => $agreements,
            'filters' => $request->only(['search', 'status'])
        ]);
    })->name('customer-agreements.index');

    Route::get('/customer-agreements/create', function () {
        return Inertia::render('CustomerAgreements/Create', [
            'customers' => Customer::orderBy('company_name')->get()
        ]);
    })->name('customer-agreements.create');

    Route::post('/customer-agreements', function (Request $request) {
        \DB::table('sam_customer_agg')->insert([
            'agg_no' => $request->agg_no,
            'customer' => $request->customer,
            'item' => $request->item,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'tax_p' => $request->tax_p ?? '',
            'transport_unit' => $request->transport_unit ?? '',
            't_unit_price' => $request->t_unit_price ?? 0,
            'status' => $request->status ?? 'Done',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
            'registered_time' => now()->format('H:i:s'),
        ]);
        return redirect()->route('customer-agreements.index')->with('success', 'Agreement created');
    })->name('customer-agreements.store');

    Route::get('/customer-agreements/{id}/edit', function ($id) {
        $agreement = \DB::table('sam_customer_agg')->where('id', $id)->first();
        return Inertia::render('CustomerAgreements/Edit', [
            'agreement' => $agreement,
            'customers' => Customer::orderBy('company_name')->get()
        ]);
    })->name('customer-agreements.edit');

    Route::put('/customer-agreements/{id}', function (Request $request, $id) {
        \DB::table('sam_customer_agg')->where('id', $id)->update([
            'agg_no' => $request->agg_no,
            'customer' => $request->customer,
            'item' => $request->item,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'tax_p' => $request->tax_p ?? '',
            'transport_unit' => $request->transport_unit ?? '',
            't_unit_price' => $request->t_unit_price ?? 0,
            'status' => $request->status,
        ]);
        return redirect()->route('customer-agreements.index')->with('success', 'Agreement updated');
    })->name('customer-agreements.update');

    Route::put('/customer-agreements/{id}/void', function ($id) {
        \DB::table('sam_customer_agg')->where('id', $id)->update(['status' => 'Void']);
        return redirect()->route('customer-agreements.index')->with('success', 'Agreement voided');
    })->name('customer-agreements.void');

    // ==================== SUPPLIER AGREEMENTS ====================
    Route::get('/supplier-agreements', function (Request $request) {
        $query = \DB::table('sam_supplier_agg')
            ->where('status', '!=', 'pending')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('agg_no', 'like', "%{$request->search}%")
                  ->orWhere('item', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $agreements = $query->paginate(20)->withQueryString();
        
        // Add supplier data
        foreach ($agreements as $agg) {
            $agg->supplier = Supplier::find($agg->supplier);
        }
        
        return Inertia::render('SupplierAgreements/Index', [
            'agreements' => $agreements,
            'filters' => $request->only(['search', 'status'])
        ]);
    })->name('supplier-agreements.index');

    Route::get('/supplier-agreements/create', function () {
        return Inertia::render('SupplierAgreements/Create', [
            'suppliers' => Supplier::orderBy('supplier_name')->get()
        ]);
    })->name('supplier-agreements.create');

    Route::post('/supplier-agreements', function (Request $request) {
        \DB::table('sam_supplier_agg')->insert([
            'agg_no' => $request->agg_no,
            'supplier' => $request->supplier,
            'item' => $request->item,
            'invoice_type' => $request->invoice_type ?? 'VAT',
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'status' => $request->status ?? 'Done',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
            'registered_time' => now()->format('H:i:s'),
        ]);
        return redirect()->route('supplier-agreements.index')->with('success', 'Agreement created');
    })->name('supplier-agreements.store');

    Route::get('/supplier-agreements/{id}/edit', function ($id) {
        $agreement = \DB::table('sam_supplier_agg')->where('id', $id)->first();
        return Inertia::render('SupplierAgreements/Edit', [
            'agreement' => $agreement,
            'suppliers' => Supplier::orderBy('supplier_name')->get()
        ]);
    })->name('supplier-agreements.edit');

    Route::put('/supplier-agreements/{id}', function (Request $request, $id) {
        \DB::table('sam_supplier_agg')->where('id', $id)->update([
            'agg_no' => $request->agg_no,
            'supplier' => $request->supplier,
            'item' => $request->item,
            'invoice_type' => $request->invoice_type ?? 'VAT',
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'status' => $request->status,
        ]);
        return redirect()->route('supplier-agreements.index')->with('success', 'Agreement updated');
    })->name('supplier-agreements.update');

    Route::put('/supplier-agreements/{id}/void', function ($id) {
        \DB::table('sam_supplier_agg')->where('id', $id)->update(['status' => 'Void']);
        return redirect()->route('supplier-agreements.index')->with('success', 'Agreement voided');
    })->name('supplier-agreements.void');

    // ==================== SUPPLIER BANK ACCOUNTS ====================
    Route::get('/suppliers/{supplier}/accounts', function (Supplier $supplier) {
        $accounts = \DB::table('sam_supplier_account_no')
            ->where('supplier_id', $supplier->id)
            ->get();
        
        return Inertia::render('Suppliers/Accounts', [
            'supplier' => $supplier,
            'accounts' => $accounts
        ]);
    })->name('suppliers.accounts');

    Route::post('/suppliers/{supplier}/accounts', function (Request $request, Supplier $supplier) {
        // Check if account already exists
        $exists = \DB::table('sam_supplier_account_no')
            ->where('account_no', $request->account_no)
            ->exists();
        
        if ($exists) {
            return back()->withErrors(['account_no' => 'Account number already registered']);
        }
        
        \DB::table('sam_supplier_account_no')->insert([
            'supplier_id' => $supplier->id,
            'bank' => $request->bank,
            'account_holder' => $request->account_holder,
            'account_no' => $request->account_no,
            'registered_by' => auth()->user()->user_name ?? 'Admin',
        ]);
        
        return redirect()->back()->with('success', 'Bank account added');
    })->name('suppliers.accounts.store');

    Route::delete('/suppliers/{supplier}/accounts/{account}', function (Supplier $supplier, $account) {
        \DB::table('sam_supplier_account_no')->where('id', $account)->delete();
        return redirect()->back()->with('success', 'Bank account deleted');
    })->name('suppliers.accounts.destroy');

    // ==================== REPORTS ====================
    Route::get('/reports', function () {
        return Inertia::render('Reports/Index');
    })->name('reports.index');

    // Summary Report
    Route::get('/reports/summary', function (Request $request) {
        $categories = Delivery::distinct()->pluck('category')->filter()->values();
        
        $reportData = null;
        if ($request->from_date && $request->to_date) {
            // Sales by category
            $salesQuery = Delivery::selectRaw('category, unit, SUM(quantity) as quantity, SUM(total) as total')
                ->whereBetween('issue_date', [$request->from_date, $request->to_date])
                ->groupBy('category', 'unit');
            
            if ($request->category && count($request->category)) {
                $salesQuery->whereIn('category', $request->category);
            }
            
            $salesByCategory = $salesQuery->get();
            
            // Purchases by category (from purchase orders)
            $purchasesByCategory = PurchaseOrder::selectRaw('item as category, unit, SUM(quantity) as quantity, SUM(total) as total')
                ->whereBetween('registered_date', [$request->from_date, $request->to_date])
                ->groupBy('item', 'unit')
                ->get();
            
            $totalSales = $salesByCategory->sum('total');
            $totalPurchases = $purchasesByCategory->sum('total');
            
            $reportData = [
                'total_sales' => $totalSales,
                'total_purchases' => $totalPurchases,
                'total_deliveries' => Delivery::whereBetween('issue_date', [$request->from_date, $request->to_date])->count(),
                'net_profit' => $totalSales - $totalPurchases,
                'sales_by_category' => $salesByCategory,
                'purchases_by_category' => $purchasesByCategory,
            ];
        }
        
        return Inertia::render('Reports/SummaryReport', [
            'categories' => $categories,
            'reportData' => $reportData
        ]);
    })->name('reports.summary');

    // Supplier Finance Report
    Route::get('/reports/supplier-finance', function (Request $request) {
        $suppliers = Supplier::where('status', 'Active')->orderBy('supplier_name')->get();
        
        $reportData = null;
        $selectedSupplier = null;
        
        if ($request->supplier) {
            $selectedSupplier = Supplier::find($request->supplier);
            
            $dateCondition = $request->all_dates ? [] : [
                ['payment_date', '>=', $request->from_date],
                ['payment_date', '<=', $request->to_date],
            ];
            
            // Cash purchases (payments with p_type = 'po')
            // Use LIKE BINARY for case-sensitive comparison that avoids collation issues
            $cashPurchasesQuery = Payment::whereRaw("`status` LIKE BINARY ?", ['Approved'])
                ->whereRaw("`p_type` LIKE BINARY ?", ['po'])
                ->whereRaw('CAST(`po_no` AS CHAR) COLLATE utf8mb4_unicode_ci IN (SELECT CAST(`po_no` AS CHAR) COLLATE utf8mb4_unicode_ci FROM `sam_purchase_order` WHERE `supplier` = ?)', [$request->supplier]);
            
            if (!$request->all_dates && $request->from_date && $request->to_date) {
                $cashPurchasesQuery->whereBetween('payment_date', [$request->from_date, $request->to_date]);
            }
            
            $cashPurchases = $cashPurchasesQuery->get()->map(function($p) {
                $po = PurchaseOrder::where('po_no', $p->po_no)->first();
                return [
                    'id' => $p->id,
                    'payment_date' => $p->payment_date,
                    'po_no' => $p->po_no,
                    'item' => $po?->item ?? '',
                    'description' => $p->payment_description,
                    'amount' => $p->net_amount,
                    'quantity' => $po?->quantity ?? 0,
                    'balance' => $po?->c_balance ?? 0,
                ];
            });
            
            // Advance payments
            // Use LIKE BINARY for case-sensitive comparison that avoids collation issues
            $advanceQuery = Payment::whereRaw("`status` LIKE BINARY ?", ['Approved'])
                ->whereRaw("`p_type` LIKE BINARY ?", ['advance'])
                ->where('supplier_id', $request->supplier);
            
            if (!$request->all_dates && $request->from_date && $request->to_date) {
                $advanceQuery->whereBetween('payment_date', [$request->from_date, $request->to_date]);
            }
            
            $advancePayments = $advanceQuery->get()->map(function($p) {
                return [
                    'id' => $p->id,
                    'payment_date' => $p->payment_date,
                    'p_no' => $p->p_no,
                    'description' => $p->payment_description,
                    'amount' => $p->net_amount,
                    'remaining' => $p->advance_balance ?? 0,
                ];
            });
            
            // Credit purchases
            // Use LIKE BINARY for case-sensitive comparison that avoids collation issues
            $creditQuery = Payment::whereRaw("`status` LIKE BINARY ?", ['Approved'])
                ->whereRaw("`p_type` LIKE BINARY ?", ['credit'])
                ->where('supplier_id', $request->supplier);
            
            if (!$request->all_dates && $request->from_date && $request->to_date) {
                $creditQuery->whereBetween('payment_date', [$request->from_date, $request->to_date]);
            }
            
            $creditPurchases = $creditQuery->get()->map(function($p) {
                return [
                    'id' => $p->id,
                    'date' => $p->payment_date,
                    'payment_no' => $p->p_no,
                    'description' => $p->payment_description,
                    'amount' => $p->net_amount,
                ];
            });
            
            $reportData = [
                'total_cash_purchase' => $cashPurchases->sum('amount'),
                'total_advance' => $advancePayments->sum('amount'),
                'total_credit_purchase' => $creditPurchases->sum('amount'),
                'total_outstanding_credit' => $creditPurchases->sum('amount') - Payment::where('supplier_id', $request->supplier)->whereRaw("`status` LIKE BINARY ?", ['Approved'])->sum('net_amount'),
                'cash_purchases' => $cashPurchases,
                'advance_payments' => $advancePayments,
                'credit_purchases' => $creditPurchases,
            ];
        }
        
        return Inertia::render('Reports/SupplierFinanceReport', [
            'suppliers' => $suppliers,
            'reportData' => $reportData,
            'selectedSupplier' => $selectedSupplier,
        ]);
    })->name('reports.supplier-finance');

    // Delivered Items Report
    Route::get('/reports/delivered-items', function (Request $request) {
        $query = Delivery::with('customer')->where('status', '!=', 'Void');
        
        if ($request->from_date) {
            $query->where('issue_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('issue_date', '<=', $request->to_date);
        }
        if ($request->customer) {
            $query->where('customer', $request->customer);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        $deliveries = $query->orderBy('issue_date', 'desc')->paginate(50);
        $customers = Customer::where('status', 'Active')->orderBy('company_name')->get();
        $categories = Delivery::distinct()->pluck('category')->filter()->values();
        
        return Inertia::render('Reports/DeliveredItems', [
            'deliveries' => $deliveries,
            'customers' => $customers,
            'categories' => $categories,
            'filters' => $request->only(['from_date', 'to_date', 'customer', 'category'])
        ]);
    })->name('reports.delivered-items');

    // Delivered by Category Report
    Route::get('/reports/delivered-by-category', function (Request $request) {
        $query = Delivery::selectRaw('category, unit, SUM(quantity) as total_quantity, SUM(total) as total_amount, COUNT(*) as count')
            ->where('status', '!=', 'Void')
            ->groupBy('category', 'unit');
        
        if ($request->from_date) {
            $query->where('issue_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('issue_date', '<=', $request->to_date);
        }
        
        $reportData = $query->orderBy('category')->get();
        $totalAmount = $reportData->sum('total_amount');
        $totalQuantity = $reportData->sum('total_quantity');
        
        return Inertia::render('Reports/DeliveredByCategory', [
            'reportData' => $reportData,
            'totalAmount' => $totalAmount,
            'totalQuantity' => $totalQuantity,
            'filters' => $request->only(['from_date', 'to_date'])
        ]);
    })->name('reports.delivered-by-category');

    // Sales Order by Customer Report
    Route::get('/reports/sales-order-by-customer', function (Request $request) {
        $query = SalesOrder::with('customer')
            ->selectRaw('customer, SUM(quantity) as total_quantity, SUM(total) as total_amount, COUNT(*) as count')
            ->where('status', '!=', 'Void')
            ->groupBy('customer');
        
        if ($request->from_date) {
            $query->where('so_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('so_date', '<=', $request->to_date);
        }
        
        $reportData = $query->orderBy('customer')->get()->map(function($item) {
            $customer = Customer::find($item->customer);
            return [
                'customer_id' => $item->customer,
                'customer_name' => $customer?->company_name ?? 'Unknown',
                'total_quantity' => $item->total_quantity,
                'total_amount' => $item->total_amount,
                'count' => $item->count,
            ];
        });
        
        return Inertia::render('Reports/SalesOrderByCustomer', [
            'reportData' => $reportData,
            'filters' => $request->only(['from_date', 'to_date'])
        ]);
    })->name('reports.sales-order-by-customer');

    // Uncollected Sales Orders Report
    Route::get('/reports/uncollected-sales-orders', function (Request $request) {
        $query = SalesOrder::with('customer')
            ->where('status', '!=', 'Void')
            ->whereRaw('remaning > 0');
        
        if ($request->from_date) {
            $query->where('so_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('so_date', '<=', $request->to_date);
        }
        if ($request->customer) {
            $query->where('customer', $request->customer);
        }
        
        $salesOrders = $query->orderBy('so_date', 'desc')->paginate(50);
        $customers = Customer::where('status', 'Active')->orderBy('company_name')->get();
        $totalUncollected = SalesOrder::where('status', '!=', 'Void')->whereRaw('remaning > 0')->sum('remaning');
        
        return Inertia::render('Reports/UncollectedSalesOrders', [
            'salesOrders' => $salesOrders,
            'customers' => $customers,
            'totalUncollected' => $totalUncollected,
            'filters' => $request->only(['from_date', 'to_date', 'customer'])
        ]);
    })->name('reports.uncollected-sales-orders');

    // Purchase Balance Report
    Route::get('/reports/purchase-balance', function (Request $request) {
        $query = PurchaseOrder::with('supplier')
            ->where('status', '!=', 'Void')
            ->whereRaw('c_balance > 0');
        
        if ($request->supplier) {
            $query->where('supplier', $request->supplier);
        }
        if ($request->from_date) {
            $query->where('registered_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('registered_date', '<=', $request->to_date);
        }
        
        $purchaseOrders = $query->orderBy('registered_date', 'desc')->paginate(50);
        $suppliers = Supplier::where('status', 'Active')->orderBy('supplier_name')->get();
        $totalBalance = PurchaseOrder::where('status', '!=', 'Void')->whereRaw('c_balance > 0')->sum('c_balance');
        
        return Inertia::render('Reports/PurchaseBalance', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
            'totalBalance' => $totalBalance,
            'filters' => $request->only(['from_date', 'to_date', 'supplier'])
        ]);
    })->name('reports.purchase-balance');

    // PO Not Paid Report
    Route::get('/reports/po-not-paid', function (Request $request) {
        $paidPoNos = Payment::whereRaw("`status` LIKE BINARY ?", ['Approved'])
            ->whereRaw("`p_type` LIKE BINARY ?", ['po'])
            ->distinct()
            ->pluck('po_no')
            ->filter();
        
        $query = PurchaseOrder::with('supplier')
            ->where('status', '!=', 'Void')
            ->whereNotIn('po_no', $paidPoNos);
        
        if ($request->supplier) {
            $query->where('supplier', $request->supplier);
        }
        if ($request->from_date) {
            $query->where('registered_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('registered_date', '<=', $request->to_date);
        }
        
        $purchaseOrders = $query->orderBy('registered_date', 'desc')->paginate(50);
        $suppliers = Supplier::where('status', 'Active')->orderBy('supplier_name')->get();
        $totalUnpaid = $purchaseOrders->sum('total');
        
        return Inertia::render('Reports/PONotPaid', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
            'totalUnpaid' => $totalUnpaid,
            'filters' => $request->only(['from_date', 'to_date', 'supplier'])
        ]);
    })->name('reports.po-not-paid');

    // Payment Summary Report
    Route::get('/reports/payment-summary', function (Request $request) {
        $query = Payment::with('supplier')
            ->whereRaw("`status` LIKE BINARY ?", ['Approved']);
        
        if ($request->from_date) {
            $query->where('payment_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('payment_date', '<=', $request->to_date);
        }
        if ($request->p_type) {
            $query->whereRaw("`p_type` LIKE BINARY ?", [$request->p_type]);
        }
        
        $payments = $query->orderBy('payment_date', 'desc')->paginate(50);
        
        $summary = [
            'total_amount' => $query->sum('net_amount'),
            'by_type' => Payment::whereRaw("`status` LIKE BINARY ?", ['Approved'])
                ->when($request->from_date, fn($q) => $q->where('payment_date', '>=', $request->from_date))
                ->when($request->to_date, fn($q) => $q->where('payment_date', '<=', $request->to_date))
                ->selectRaw('p_type, SUM(net_amount) as total, COUNT(*) as count')
                ->groupBy('p_type')
                ->get(),
        ];
        
        return Inertia::render('Reports/PaymentSummary', [
            'payments' => $payments,
            'summary' => $summary,
            'filters' => $request->only(['from_date', 'to_date', 'p_type'])
        ]);
    })->name('reports.payment-summary');

    // Advance Balance Report
    Route::get('/reports/advance-balance', function (Request $request) {
        $query = Payment::with('supplier')
            ->whereRaw("`status` LIKE BINARY ?", ['Approved'])
            ->whereRaw("`p_type` LIKE BINARY ?", ['advance'])
            ->whereRaw('advance_balance > 0');
        
        if ($request->supplier) {
            $query->where('supplier_id', $request->supplier);
        }
        if ($request->from_date) {
            $query->where('payment_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('payment_date', '<=', $request->to_date);
        }
        
        $advances = $query->orderBy('payment_date', 'desc')->paginate(50);
        $suppliers = Supplier::where('status', 'Active')->orderBy('supplier_name')->get();
        $totalAdvance = Payment::whereRaw("`status` LIKE BINARY ?", ['Approved'])
            ->whereRaw("`p_type` LIKE BINARY ?", ['advance'])
            ->whereRaw('advance_balance > 0')
            ->sum('advance_balance');
        
        return Inertia::render('Reports/AdvanceBalance', [
            'advances' => $advances,
            'suppliers' => $suppliers,
            'totalAdvance' => $totalAdvance,
            'filters' => $request->only(['from_date', 'to_date', 'supplier'])
        ]);
    })->name('reports.advance-balance');

    // Unpaid Transport Report
    Route::get('/reports/unpaid-transport', function (Request $request) {
        $query = \DB::table('sam_transporter_payment as tp')
            ->join('sam_transporter_payment_net as tpn', 'tp.tp_no', '=', 'tpn.tp_no')
            ->join('sam_transporter_agg as ta', 'tp.ta_id', '=', 'ta.id')
            ->join('sam_transporter as t', 'ta.t_id', '=', 't.id')
            ->whereRaw("tp.status LIKE BINARY ?", ['Approved'])
            ->whereRaw('tpn.remaning > 0')
            ->select('tp.*', 'tpn.remaning', 't.transporter_name', 'ta.a_no');
        
        if ($request->from_date) {
            $query->where('tp.tp_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('tp.tp_date', '<=', $request->to_date);
        }
        
        $transporterPayments = $query->orderBy('tp.tp_date', 'desc')->paginate(50);
        $totalUnpaid = \DB::table('sam_transporter_payment as tp')
            ->join('sam_transporter_payment_net as tpn', 'tp.tp_no', '=', 'tpn.tp_no')
            ->whereRaw("tp.status LIKE BINARY ?", ['Approved'])
            ->whereRaw('tpn.remaning > 0')
            ->sum('tpn.remaning');
        
        return Inertia::render('Reports/UnpaidTransport', [
            'transporterPayments' => $transporterPayments,
            'totalUnpaid' => $totalUnpaid,
            'filters' => $request->only(['from_date', 'to_date'])
        ]);
    })->name('reports.unpaid-transport');

    // ==================== BUDGET MANAGEMENT ====================
    // Budget Requests
    Route::get('/budget-requests', function (Request $request) {
        $query = DB::table('sam_budget_request');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('b_no', 'like', '%' . $request->search . '%')
                  ->orWhere('project', 'like', '%' . $request->search . '%')
                  ->orWhere('item', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $budgetRequests = $query->orderBy('id', 'desc')->paginate(20);
        
        // Get items for dropdown
        $items = DB::table('sam_item')->where('status', 'Active')->where('item_type', 'sales')->orderBy('item')->get();
        $units = DB::table('sam_unit')->orderBy('unit')->get();
        
        return Inertia::render('BudgetRequests/Index', [
            'budgetRequests' => $budgetRequests,
            'items' => $items,
            'units' => $units,
            'filters' => $request->only(['search', 'status'])
        ]);
    })->name('budget-requests.index');

    Route::post('/budget-requests', function (Request $request) {
        $request->validate([
            'item_id' => 'required|exists:sam_item,id',
            'unit' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'profit' => 'nullable|numeric|min:0|max:100',
            'overhead' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $item = DB::table('sam_item')->where('id', $request->item_id)->first();
        $amount = $request->unit_price * $request->quantity;
        
        // Apply profit and overhead if provided
        if ($request->profit) {
            $amount = $amount * (1 + $request->profit / 100);
        }
        if ($request->overhead) {
            $amount = $amount * (1 + $request->overhead / 100);
        }
        
        DB::table('sam_budget_request')->insert([
            'item' => $item->item,
            'item_id' => $request->item_id,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'quantity' => $request->quantity,
            'amount' => $amount,
            'profit' => $request->profit ?? 0,
            'overhead' => $request->overhead ?? 0,
            'status' => 'pending',
            'registered_by' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'registered_date' => now()->format('Y-m-d'),
            'b_no' => '',
            'used_for' => '',
            'project' => '',
            'purpose' => '',
            'category' => '',
            'request_from' => '',
            'balance' => $amount,
            'b_doc' => '',
            'b_doc_by' => '',
            'b_doc_date' => '',
        ]);
        
        return redirect()->route('budget-requests.index')->with('success', 'Budget request created successfully.');
    })->name('budget-requests.store');

    Route::get('/budget-requests/{id}/edit', function ($id) {
        $budgetRequest = DB::table('sam_budget_request')->where('id', $id)->first();
        
        if (!$budgetRequest) {
            abort(404);
        }
        
        $items = DB::table('sam_item')->where('status', 'Active')->where('item_type', 'sales')->orderBy('item')->get();
        $units = DB::table('sam_unit')->orderBy('unit')->get();
        
        return Inertia::render('BudgetRequests/Edit', [
            'budgetRequest' => $budgetRequest,
            'items' => $items,
            'units' => $units,
        ]);
    })->name('budget-requests.edit');

    Route::put('/budget-requests/{id}', function (Request $request, $id) {
        $request->validate([
            'item_id' => 'required|exists:sam_item,id',
            'unit' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'profit' => 'nullable|numeric|min:0|max:100',
            'overhead' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $item = DB::table('sam_item')->where('id', $request->item_id)->first();
        $amount = $request->unit_price * $request->quantity;
        
        if ($request->profit) {
            $amount = $amount * (1 + $request->profit / 100);
        }
        if ($request->overhead) {
            $amount = $amount * (1 + $request->overhead / 100);
        }
        
        DB::table('sam_budget_request')->where('id', $id)->update([
            'item' => $item->item,
            'item_id' => $request->item_id,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'quantity' => $request->quantity,
            'amount' => $amount,
            'profit' => $request->profit ?? 0,
            'overhead' => $request->overhead ?? 0,
        ]);
        
        return redirect()->route('budget-requests.index')->with('success', 'Budget request updated successfully.');
    })->name('budget-requests.update');

    Route::delete('/budget-requests/{id}', function ($id) {
        DB::table('sam_budget_request')->where('id', $id)->delete();
        return redirect()->route('budget-requests.index')->with('success', 'Budget request deleted successfully.');
    })->name('budget-requests.destroy');

    Route::put('/budget-requests/{id}/complete', function (Request $request, $id) {
        $request->validate([
            'b_no' => 'required|string',
        ]);
        
        DB::table('sam_budget_request')->where('id', $id)->update([
            'b_no' => $request->b_no,
            'status' => 'Done',
        ]);
        
        return redirect()->route('budget-requests.index')->with('success', 'Budget request completed successfully.');
    })->name('budget-requests.complete');

    // Budget List
    Route::get('/budgets', function (Request $request) {
        $query = DB::table('sam_budget')
            ->select('b_no', 'project', DB::raw('SUM(CAST(amount AS DECIMAL(20,2))) as total_amount'), 'status', 'registered_by', 'registered_date')
            ->groupBy('b_no', 'project', 'status', 'registered_by', 'registered_date');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('b_no', 'like', '%' . $request->search . '%')
                  ->orWhere('project', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $budgets = $query->orderBy('registered_date', 'desc')->paginate(20);
        
        // Budget balances by project
        $budgetBalances = DB::table('sam_budget_balance')
            ->where('status', 'Active')
            ->orderBy('project')
            ->get();
        
        return Inertia::render('Budgets/Index', [
            'budgets' => $budgets,
            'budgetBalances' => $budgetBalances,
            'filters' => $request->only(['search', 'status'])
        ]);
    })->name('budgets.index');

    Route::get('/budgets/create', function (Request $request) {
        // Get approved budget requests
        $budgetRequests = DB::table('sam_budget_request')
            ->where('status', 'Done')
            ->where('balance', '>', 0)
            ->orderBy('b_no')
            ->orderBy('id')
            ->get();
        
        // Get projects from customers
        $projects = DB::table('sam_customer')
            ->where('status', 'Active')
            ->select('id', 'company_name as project')
            ->orderBy('company_name')
            ->get();
        
        return Inertia::render('Budgets/Create', [
            'budgetRequests' => $budgetRequests,
            'projects' => $projects,
            'b_id' => $request->b_id, // Optional: pre-select a budget request
        ]);
    })->name('budgets.create');

    Route::post('/budgets', function (Request $request) {
        $request->validate([
            'b_id' => 'required|exists:sam_budget_request,id',
            'project' => 'required|string',
            'b_no' => 'required|string|unique:sam_budget,b_no',
        ]);
        
        $budgetRequest = DB::table('sam_budget_request')->where('id', $request->b_id)->first();
        
        if (!$budgetRequest || $budgetRequest->status != 'Done') {
            return back()->withErrors(['b_id' => 'Invalid or incomplete budget request.']);
        }
        
        if (floatval($budgetRequest->balance) <= 0) {
            return back()->withErrors(['b_id' => 'Budget request has no remaining balance.']);
        }
        
        $amount = floatval($budgetRequest->balance);
        
        // Create budget
        DB::table('sam_budget')->insert([
            'b_no' => $request->b_no,
            'b_id' => $request->b_id,
            'project' => $request->project,
            'amount' => $amount,
            'status' => 'Done',
            'registered_by' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'registered_date' => now()->format('Y-m-d'),
        ]);
        
        // Update or create budget balance
        $existingBalance = DB::table('sam_budget_balance')->where('project', $request->project)->first();
        
        if ($existingBalance) {
            DB::table('sam_budget_balance')->where('project', $request->project)->update([
                'balance' => floatval($existingBalance->balance) + $amount,
            ]);
        } else {
            DB::table('sam_budget_balance')->insert([
                'project' => $request->project,
                'balance' => $amount,
                'status' => 'Active',
            ]);
        }
        
        return redirect()->route('budgets.index')->with('success', 'Budget created successfully.');
    })->name('budgets.store');

    Route::get('/budgets/{b_no}', function ($b_no) {
        $budget = DB::table('sam_budget')
            ->where('b_no', $b_no)
            ->first();
        
        if (!$budget) {
            abort(404);
        }
        
        $budgetDetails = DB::table('sam_budget')
            ->where('b_no', $b_no)
            ->get();
        
        return Inertia::render('Budgets/Show', [
            'budget' => $budget,
            'budgetDetails' => $budgetDetails,
        ]);
    })->name('budgets.show');

    // ==================== MARKET PRICE ====================
    // Market Price List
    Route::get('/market-prices', function (Request $request) {
        $query = DB::table('sam_market_price');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('item', 'like', '%' . $request->search . '%')
                  ->orWhere('price_type', 'like', '%' . $request->search . '%')
                  ->orWhere('agg_no', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->customer) {
            $query->where('customer', $request->customer);
        }
        
        if ($request->price_type) {
            $query->where('price_type', $request->price_type);
        }
        
        $marketPrices = $query->orderBy('registered_date', 'desc')->paginate(20);
        
        $customers = Customer::where('status', 'Active')->orderBy('company_name')->get();
        $items = DB::table('sam_item')->where('status', 'Active')->where('item_type', 'sales')->orderBy('item')->get();
        
        return Inertia::render('MarketPrices/Index', [
            'marketPrices' => $marketPrices,
            'customers' => $customers,
            'items' => $items,
            'filters' => $request->only(['search', 'status', 'customer', 'price_type'])
        ]);
    })->name('market-prices.index');

    // Update Sales Price
    Route::get('/market-prices/create', function (Request $request) {
        $customers = Customer::where('status', 'Active')->orderBy('company_name')->get();
        $items = DB::table('sam_item')->where('status', 'Active')->where('item_type', 'sales')->orderBy('item')->get();
        $units = DB::table('sam_unit')->orderBy('unit')->get();
        
        // Get customer agreements for selected customer
        $customerAgreements = [];
        if ($request->customer) {
            $customerAgreements = DB::table('sam_customer_agg')
                ->where('customer', $request->customer)
                ->where('status', 'Approved')
                ->orderBy('a_no')
                ->get();
        }
        
        return Inertia::render('MarketPrices/Create', [
            'customers' => $customers,
            'items' => $items,
            'units' => $units,
            'customerAgreements' => $customerAgreements,
            'selectedCustomer' => $request->customer,
        ]);
    })->name('market-prices.create');

    Route::post('/market-prices', function (Request $request) {
        $request->validate([
            'item_id' => 'required|exists:sam_item,id',
            'unit' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'tax_p' => 'required|numeric|min:0|max:100',
            'customer' => 'nullable|exists:sam_customer,id',
            'price_type' => 'required|string|in:agreement,market',
            'agg_no' => 'nullable|string',
        ]);
        
        $item = DB::table('sam_item')->where('id', $request->item_id)->first();
        
        DB::table('sam_market_price')->insert([
            'item_id' => $request->item_id,
            'item' => $item->item,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'tax_p' => $request->tax_p,
            'status' => 'pending',
            'registered_by' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'registered_date' => now()->format('Y-m-d'),
            'registered_time' => now()->format('h:i:s A'),
            'price_type' => $request->price_type,
            'agg_no' => $request->agg_no ?? '',
            'customer' => $request->customer ?? '',
            'transport' => $request->transport ?? 'No',
            'transport_unit' => $request->transport_unit ?? '',
            't_unit_price' => $request->t_unit_price ?? 0,
            't_tax_p' => $request->t_tax_p ?? '',
        ]);
        
        return redirect()->route('market-prices.index')->with('success', 'Market price created successfully. Waiting for approval.');
    })->name('market-prices.store');

    Route::get('/market-prices/{id}/edit', function ($id) {
        $price = DB::table('sam_market_price')->where('id', $id)->first();
        
        if (!$price) {
            abort(404);
        }
        
        $customers = Customer::where('status', 'Active')->orderBy('company_name')->get();
        $items = DB::table('sam_item')->where('status', 'Active')->where('item_type', 'sales')->orderBy('item')->get();
        $units = DB::table('sam_unit')->orderBy('unit')->get();
        
        // Load customer agreements if price type is agreement
        $agreements = [];
        if ($price->price_type === 'agreement' && $price->customer) {
            $agreements = DB::table('sam_customer_agg')
                ->where('customer', $price->customer)
                ->where('status', 'Approved')
                ->orderBy('agg_no')
                ->get();
        }
        
        return Inertia::render('MarketPrices/Edit', [
            'price' => $price,
            'customers' => $customers,
            'items' => $items,
            'units' => $units,
            'agreements' => $agreements
        ]);
    })->name('market-prices.edit');

    Route::put('/market-prices/{id}', function ($id, Request $request) {
        $request->validate([
            'item_id' => 'required|exists:sam_item,id',
            'unit' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'tax_p' => 'required|numeric|min:0|max:100',
            'customer' => 'nullable|exists:sam_customer,id',
            'price_type' => 'required|string|in:agreement,market',
            'agg_no' => 'nullable|string',
        ]);
        
        $item = DB::table('sam_item')->where('id', $request->item_id)->first();
        
        DB::table('sam_market_price')->where('id', $id)->update([
            'item_id' => $request->item_id,
            'item' => $item->item,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'tax_p' => $request->tax_p,
            'price_type' => $request->price_type,
            'agg_no' => $request->agg_no ?? '',
            'customer' => $request->customer ?? '',
            'transport' => $request->transport ?? 'No',
            'transport_unit' => $request->transport_unit ?? '',
            't_unit_price' => $request->t_unit_price ?? 0,
            't_tax_p' => $request->t_tax_p ?? '',
        ]);
        
        return redirect()->route('market-prices.index')->with('success', 'Market price updated successfully.');
    })->name('market-prices.update');

    // Approve Market Price
    Route::get('/market-prices/approve', function (Request $request) {
        $query = DB::table('sam_market_price')->where('status', 'pending');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('item', 'like', '%' . $request->search . '%')
                  ->orWhere('agg_no', 'like', '%' . $request->search . '%');
            });
        }
        
        $pendingPrices = $query->orderBy('registered_date', 'desc')->paginate(20);
        
        return Inertia::render('MarketPrices/Approve', [
            'pendingPrices' => $pendingPrices,
            'filters' => $request->only(['search'])
        ]);
    })->name('market-prices.approve');

    Route::put('/market-prices/{id}/approve', function ($id) {
        $marketPrice = DB::table('sam_market_price')->where('id', $id)->first();
        
        if (!$marketPrice) {
            abort(404);
        }
        
        // Update status to Approved
        DB::table('sam_market_price')->where('id', $id)->update([
            'status' => 'Approved',
        ]);
        
        // If there's a previous price for this item and customer, move it to previous table
        $previousPrice = DB::table('sam_market_price')
            ->where('item_id', $marketPrice->item_id)
            ->where('customer', $marketPrice->customer)
            ->where('status', 'Approved')
            ->where('id', '!=', $id)
            ->first();
        
        if ($previousPrice) {
            // Check if already exists in previous table
            $exists = DB::table('sam_market_price_previous')
                ->where('item_id', $previousPrice->item_id)
                ->where('customer', $previousPrice->customer)
                ->where('registered_date', $previousPrice->registered_date)
                ->exists();
            
            if (!$exists) {
                DB::table('sam_market_price_previous')->insert([
                    'item_id' => $previousPrice->item_id,
                    'item' => $previousPrice->item,
                    'unit' => $previousPrice->unit,
                    'unit_price' => $previousPrice->unit_price,
                    'tax_p' => $previousPrice->tax_p,
                    'status' => $previousPrice->status,
                    'registered_by' => $previousPrice->registered_by,
                    'registered_date' => $previousPrice->registered_date,
                    'price_type' => $previousPrice->price_type,
                    'agg_no' => $previousPrice->agg_no,
                    'registered_time' => $previousPrice->registered_time,
                    'transport' => $previousPrice->transport,
                    'transport_unit' => $previousPrice->transport_unit,
                    't_unit_price' => $previousPrice->t_unit_price,
                    't_tax_p' => $previousPrice->t_tax_p,
                ]);
            }
        }
        
        return redirect()->route('market-prices.approve')->with('success', 'Market price approved successfully.');
    })->name('market-prices.approve-action');

    Route::delete('/market-prices/{id}', function ($id) {
        DB::table('sam_market_price')->where('id', $id)->delete();
        return redirect()->route('market-prices.index')->with('success', 'Market price deleted successfully.');
    })->name('market-prices.destroy');

    // API: Get customer agreements for selected customer
    Route::get('/api/customer-agreements', function (Request $request) {
        if (!$request->customer) {
            return response()->json([]);
        }
        
        $agreements = DB::table('sam_customer_agg')
            ->where('customer', $request->customer)
            ->where('status', 'Approved')
            ->orderBy('a_no')
            ->get();
        
        return response()->json($agreements);
    });

    // ==================== COUPON MANAGEMENT ====================
    // Coupon List
    Route::get('/coupons', function (Request $request) {
        $query = DB::table('sam_cupon');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('r_no', 'like', '%' . $request->search . '%')
                  ->orWhere('supplier_name', 'like', '%' . $request->search . '%')
                  ->orWhere('po_no', 'like', '%' . $request->search . '%')
                  ->orWhere('ref_no', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->supplier) {
            $query->where('supplier', $request->supplier);
        }
        
        $coupons = $query->orderBy('registered_date', 'desc')->paginate(20);
        
        $suppliers = Supplier::where('status', 'Active')->orderBy('supplier_name')->get();
        
        return Inertia::render('Coupons/Index', [
            'coupons' => $coupons,
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'status', 'supplier'])
        ]);
    })->name('coupons.index');

    // Request Coupon
    Route::get('/coupons/request', function (Request $request) {
        $suppliers = Supplier::where('status', 'Active')->orderBy('supplier_name')->get();
        $purchaseOrders = DB::table('sam_purchase_order')
            ->where('status', '!=', 'Void')
            ->orderBy('po_no', 'desc')
            ->get();
        
        return Inertia::render('Coupons/Request', [
            'suppliers' => $suppliers,
            'purchaseOrders' => $purchaseOrders,
            'selectedPO' => $request->po_no,
        ]);
    })->name('coupons.request');

    Route::post('/coupons/request', function (Request $request) {
        $request->validate([
            'supplier' => 'required|exists:sam_supplier,id',
            'po_no' => 'required|string',
            'ref_no' => 'nullable|string',
            'purchase_type' => 'nullable|string',
            'cupon_tag' => 'nullable|string',
        ]);
        
        $supplier = Supplier::findOrFail($request->supplier);
        
        // Get next r_no
        $lastCoupon = DB::table('sam_cupon')->orderBy('id', 'desc')->first();
        $nextRNo = $lastCoupon ? str_pad((int)$lastCoupon->r_no + 1, 4, '0', STR_PAD_LEFT) : '0001';
        
        $couponId = DB::table('sam_cupon')->insertGetId([
            'r_no' => $nextRNo,
            'supplier' => $request->supplier,
            'po_no' => $request->po_no,
            'purchase_type' => $request->purchase_type ?? '',
            'supplier_name' => $supplier->supplier_name,
            'registered_by' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'registered_date' => now()->format('Y-m-d'),
            'ref_no' => $request->ref_no ?? '',
            'status' => 'pending',
            'cupon_tag' => $request->cupon_tag ?? '',
        ]);
        
        return redirect()->route('coupons.index')->with('success', 'Coupon request created successfully.');
    })->name('coupons.request.store');

    // Receive Coupon
    Route::get('/coupons/receive', function (Request $request) {
        $coupons = DB::table('sam_cupon')
            ->where('status', 'pending')
            ->orderBy('registered_date', 'desc')
            ->get();
        
        return Inertia::render('Coupons/Receive', [
            'coupons' => $coupons,
            'selectedCoupon' => $request->r_id,
        ]);
    })->name('coupons.receive');

    Route::post('/coupons/receive', function (Request $request) {
        $request->validate([
            'r_id' => 'required|exists:sam_cupon,id',
            'cupon_numbers' => 'required|array|min:1',
            'cupon_numbers.*' => 'required|string',
            'order_type' => 'required|string',
            'cupon_tag' => 'nullable|string',
        ]);
        
        $coupon = DB::table('sam_cupon')->where('id', $request->r_id)->first();
        
        if (!$coupon) {
            return back()->withErrors(['r_id' => 'Coupon request not found.']);
        }
        
        foreach ($request->cupon_numbers as $cuponNo) {
            DB::table('sam_cupon_no')->insert([
                'cupon_no' => $cuponNo,
                'r_id' => $request->r_id,
                'registered_by' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'registered_date' => now()->format('Y-m-d'),
                'order_type' => $request->order_type,
                'status' => 'pending',
                'supplier' => $coupon->supplier,
                'dispatcher' => '',
                'pass_by' => '',
                'h_no' => '',
                'ref_no' => $coupon->ref_no,
                'cupon_tag' => $request->cupon_tag ?? $coupon->cupon_tag ?? '',
                'd_r' => '',
                'd_r_date' => '',
                'd_r_by' => '',
                'filename' => '',
            ]);
        }
        
        // Update coupon status
        DB::table('sam_cupon')->where('id', $request->r_id)->update([
            'status' => 'received',
        ]);
        
        return redirect()->route('coupons.index')->with('success', 'Coupons received successfully.');
    })->name('coupons.receive.store');

    // Handover Coupon
    Route::get('/coupons/handover', function (Request $request) {
        $couponNumbers = DB::table('sam_cupon_no')
            ->where('status', 'pending')
            ->orWhere('status', 'received')
            ->orderBy('registered_date', 'desc')
            ->get();
        
        $coupons = DB::table('sam_cupon')
            ->whereIn('id', $couponNumbers->pluck('r_id')->unique())
            ->get()
            ->keyBy('id');
        
        return Inertia::render('Coupons/Handover', [
            'couponNumbers' => $couponNumbers,
            'coupons' => $coupons,
        ]);
    })->name('coupons.handover');

    Route::post('/coupons/handover', function (Request $request) {
        $request->validate([
            'cupon_ids' => 'required|array|min:1',
            'cupon_ids.*' => 'required|exists:sam_cupon_no,id',
            'dispatcher' => 'required|string',
            'pass_by' => 'required|string',
            'h_no' => 'nullable|string',
        ]);
        
        foreach ($request->cupon_ids as $cuponId) {
            DB::table('sam_cupon_no')->where('id', $cuponId)->update([
                'dispatcher' => $request->dispatcher,
                'pass_by' => $request->pass_by,
                'h_no' => $request->h_no ?? '',
                'status' => 'handed_over',
            ]);
        }
        
        return redirect()->route('coupons.index')->with('success', 'Coupons handed over successfully.');
    })->name('coupons.handover.store');

    // API: Get coupon request details
    Route::get('/api/coupon-request/{id}', function ($id) {
        $coupon = DB::table('sam_cupon')->where('id', $id)->first();
        
        if (!$coupon) {
            return response()->json(['error' => 'Coupon not found'], 404);
        }
        
        $couponNumbers = DB::table('sam_cupon_no')->where('r_id', $id)->get();
        
        return response()->json([
            'coupon' => $coupon,
            'couponNumbers' => $couponNumbers,
        ]);
    });

    Route::delete('/coupons/{id}', function ($id) {
        DB::table('sam_cupon_no')->where('r_id', $id)->delete();
        DB::table('sam_cupon')->where('id', $id)->delete();
        return redirect()->route('coupons.index')->with('success', 'Coupon deleted successfully.');
    })->name('coupons.destroy');

    // ==================== INSURANCE POLICY ====================
    // Insurance Companies List
    Route::get('/insurances', function (Request $request) {
        $query = DB::table('sam_insurance');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('insurance_name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                  ->orWhere('branch', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status) {
            $query->where('insurance_status', $request->status);
        }
        
        $insurances = $query->orderBy('insurance_name')->paginate(20);
        
        return Inertia::render('Insurances/Index', [
            'insurances' => $insurances,
            'filters' => $request->only(['search', 'status'])
        ]);
    })->name('insurances.index');

    Route::get('/insurances/create', function () {
        return Inertia::render('Insurances/Create');
    })->name('insurances.create');

    Route::post('/insurances', function (Request $request) {
        $request->validate([
            'insurance_name' => 'required|string|max:100',
            'branch' => 'nullable|string|max:50',
            'contact_person' => 'required|string|max:50',
            'phone_number' => 'required|string|max:50',
            'insurance_status' => 'required|string|in:Active,Inactive',
        ]);
        
        DB::table('sam_insurance')->insert([
            'insurance_name' => $request->insurance_name,
            'branch' => $request->branch ?? '',
            'contact_person' => $request->contact_person,
            'phone_number' => $request->phone_number,
            'insurance_status' => $request->insurance_status,
        ]);
        
        return redirect()->route('insurances.index')->with('success', 'Insurance company created successfully.');
    })->name('insurances.store');

    Route::get('/insurances/{id}/edit', function ($id) {
        $insurance = DB::table('sam_insurance')->where('id', $id)->first();
        
        if (!$insurance) {
            abort(404);
        }
        
        return Inertia::render('Insurances/Edit', [
            'insurance' => $insurance
        ]);
    })->name('insurances.edit');

    Route::put('/insurances/{id}', function ($id, Request $request) {
        $request->validate([
            'insurance_name' => 'required|string|max:100',
            'branch' => 'nullable|string|max:50',
            'contact_person' => 'required|string|max:50',
            'phone_number' => 'required|string|max:50',
            'insurance_status' => 'required|string|in:Active,Inactive',
        ]);
        
        DB::table('sam_insurance')->where('id', $id)->update([
            'insurance_name' => $request->insurance_name,
            'branch' => $request->branch ?? '',
            'contact_person' => $request->contact_person,
            'phone_number' => $request->phone_number,
            'insurance_status' => $request->insurance_status,
        ]);
        
        return redirect()->route('insurances.index')->with('success', 'Insurance company updated successfully.');
    })->name('insurances.update');

    Route::delete('/insurances/{id}', function ($id) {
        DB::table('sam_insurance')->where('id', $id)->delete();
        return redirect()->route('insurances.index')->with('success', 'Insurance company deleted successfully.');
    })->name('insurances.destroy');

    // Insurance Policies
    Route::get('/insurance-policies', function (Request $request) {
        $query = DB::table('sam_insurance_policy');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('i_no', 'like', '%' . $request->search . '%')
                  ->orWhere('insurance_company', 'like', '%' . $request->search . '%')
                  ->orWhere('c_no', 'like', '%' . $request->search . '%')
                  ->orWhere('p_no', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->insurance_company) {
            $query->where('insurance_company', $request->insurance_company);
        }
        
        $policies = $query->orderBy('registered_date', 'desc')->paginate(20);
        
        $insuranceCompanies = DB::table('sam_insurance')->where('insurance_status', 'Active')->orderBy('insurance_name')->get();
        
        return Inertia::render('InsurancePolicies/Index', [
            'policies' => $policies,
            'insuranceCompanies' => $insuranceCompanies,
            'filters' => $request->only(['search', 'status', 'insurance_company'])
        ]);
    })->name('insurance-policies.index');

    Route::get('/insurance-policies/create', function () {
        $insuranceCompanies = DB::table('sam_insurance')->where('insurance_status', 'Active')->orderBy('insurance_name')->get();
        
        // Get next i_no
        $lastPolicy = DB::table('sam_insurance_policy')->orderBy('id', 'desc')->first();
        $nextINo = $lastPolicy ? str_pad((int)$lastPolicy->i_no + 1, 4, '0', STR_PAD_LEFT) : '0001';
        
        return Inertia::render('InsurancePolicies/Create', [
            'insuranceCompanies' => $insuranceCompanies,
            'nextINo' => $nextINo
        ]);
    })->name('insurance-policies.create');

    Route::post('/insurance-policies', function (Request $request) {
        $request->validate([
            'i_no' => 'required|string',
            'insurance_type' => 'required|string|max:250',
            'p_type' => 'required|string|max:20',
            'insurance_company' => 'required|string|max:500',
            'insured' => 'required|string',
            'c_no' => 'required|string|max:20',
            'p_no' => 'required|string|max:20',
            'issued_date' => 'required|date',
            'period' => 'required|string|max:20',
            'fund_tariff' => 'required|numeric|min:0',
            'premium_tariff' => 'required|numeric|min:0',
            'notification' => 'nullable|string|max:20',
            'previous' => 'nullable|string|max:20',
        ]);
        
        DB::table('sam_insurance_policy')->insert([
            'i_no' => $request->i_no,
            'insurance_type' => $request->insurance_type,
            'p_type' => $request->p_type,
            'insurance_company' => $request->insurance_company,
            'insured' => $request->insured,
            'c_no' => $request->c_no,
            'p_no' => $request->p_no,
            'issued_date' => $request->issued_date,
            'period' => $request->period,
            'fund_tariff' => $request->fund_tariff,
            'premium_tariff' => $request->premium_tariff,
            'notification' => $request->notification ?? '',
            'status' => 'pending',
            'registered_by' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'registered_date' => now()->format('Y-m-d'),
            'registered_time' => now()->format('h:i:s A'),
            'previous' => $request->previous ?? '',
        ]);
        
        return redirect()->route('insurance-policies.index')->with('success', 'Insurance policy created successfully. Waiting for approval.');
    })->name('insurance-policies.store');

    Route::get('/insurance-policies/{id}/edit', function ($id) {
        $policy = DB::table('sam_insurance_policy')->where('id', $id)->first();
        
        if (!$policy) {
            abort(404);
        }
        
        $insuranceCompanies = DB::table('sam_insurance')->where('insurance_status', 'Active')->orderBy('insurance_name')->get();
        
        return Inertia::render('InsurancePolicies/Edit', [
            'policy' => $policy,
            'insuranceCompanies' => $insuranceCompanies
        ]);
    })->name('insurance-policies.edit');

    Route::put('/insurance-policies/{id}', function ($id, Request $request) {
        $request->validate([
            'i_no' => 'required|string',
            'insurance_type' => 'required|string|max:250',
            'p_type' => 'required|string|max:20',
            'insurance_company' => 'required|string|max:500',
            'insured' => 'required|string',
            'c_no' => 'required|string|max:20',
            'p_no' => 'required|string|max:20',
            'issued_date' => 'required|date',
            'period' => 'required|string|max:20',
            'fund_tariff' => 'required|numeric|min:0',
            'premium_tariff' => 'required|numeric|min:0',
            'notification' => 'nullable|string|max:20',
            'previous' => 'nullable|string|max:20',
        ]);
        
        DB::table('sam_insurance_policy')->where('id', $id)->update([
            'i_no' => $request->i_no,
            'insurance_type' => $request->insurance_type,
            'p_type' => $request->p_type,
            'insurance_company' => $request->insurance_company,
            'insured' => $request->insured,
            'c_no' => $request->c_no,
            'p_no' => $request->p_no,
            'issued_date' => $request->issued_date,
            'period' => $request->period,
            'fund_tariff' => $request->fund_tariff,
            'premium_tariff' => $request->premium_tariff,
            'notification' => $request->notification ?? '',
            'previous' => $request->previous ?? '',
        ]);
        
        return redirect()->route('insurance-policies.index')->with('success', 'Insurance policy updated successfully.');
    })->name('insurance-policies.update');

    Route::get('/insurance-policies/approve', function (Request $request) {
        $query = DB::table('sam_insurance_policy')->where('status', 'pending');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('i_no', 'like', '%' . $request->search . '%')
                  ->orWhere('insurance_company', 'like', '%' . $request->search . '%');
            });
        }
        
        $pendingPolicies = $query->orderBy('registered_date', 'desc')->paginate(20);
        
        return Inertia::render('InsurancePolicies/Approve', [
            'pendingPolicies' => $pendingPolicies,
            'filters' => $request->only(['search'])
        ]);
    })->name('insurance-policies.approve');

    Route::put('/insurance-policies/{id}/approve', function ($id) {
        DB::table('sam_insurance_policy')->where('id', $id)->update([
            'status' => 'Approved',
        ]);
        
        return redirect()->route('insurance-policies.approve')->with('success', 'Insurance policy approved successfully.');
    })->name('insurance-policies.approve-action');

    Route::delete('/insurance-policies/{id}', function ($id) {
        DB::table('sam_insurance_policy')->where('id', $id)->delete();
        return redirect()->route('insurance-policies.index')->with('success', 'Insurance policy deleted successfully.');
    })->name('insurance-policies.destroy');

    // API-style routes for report data
    Route::get('/api/reports/deliveries', function (Request $request) {
        $query = Delivery::with('customer');
        
        if ($request->from_date) {
            $query->where('issue_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('issue_date', '<=', $request->to_date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        return response()->json($query->orderBy('issue_date', 'desc')->limit(500)->get());
    });

    Route::get('/api/reports/payments', function (Request $request) {
        $query = Payment::with('supplier');
        
        if ($request->from_date) {
            $query->where('payment_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->where('payment_date', '<=', $request->to_date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        return response()->json($query->orderBy('payment_date', 'desc')->limit(500)->get());
    });

    Route::get('/api/reports/customers', function (Request $request) {
        $query = Customer::query();
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        return response()->json($query->orderBy('id', 'desc')->limit(500)->get());
    });

    // ==================== APPROVALS ====================
    Route::get('/approvals', function () {
        return Inertia::render('Approvals/Index', [
            // Pending — waiting to be Checked
            'pendingDeliveries' => Delivery::with('customer')
                ->where('status', 'Pending')
                ->orderBy('id', 'desc')->limit(50)->get(),
            'pendingPayments' => Payment::with('supplier')
                ->where('status', 'Pending')
                ->orderBy('id', 'desc')->limit(50)->get(),
            'pendingSalesOrders' => SalesOrder::where('status', 'Pending')
                ->orderBy('id', 'desc')->limit(50)->get(),
            'pendingCustomers' => Customer::where('status', 'Pending')
                ->orderBy('id', 'desc')->limit(50)->get(),

            // Checked — waiting to be Approved
            'checkedDeliveries' => Delivery::with('customer')
                ->where('status', 'Checked')
                ->orderBy('id', 'desc')->limit(50)->get(),
            'checkedPayments' => Payment::with('supplier')
                ->where('status', 'Checked')
                ->orderBy('id', 'desc')->limit(50)->get(),
            'checkedSalesOrders' => SalesOrder::where('status', 'Checked')
                ->orderBy('id', 'desc')->limit(50)->get(),
            'checkedCustomers' => Customer::where('status', 'Checked')
                ->orderBy('id', 'desc')->limit(50)->get(),
        ]);
    })->name('approvals.index');

    // --- Check endpoints (Level 1: Admin or Supervisor can check) ---
    Route::post('/api/approvals/delivery/{id}/check', function ($id) {
        // Role guard: only Admin and Supervisor can check
        if (!in_array(auth()->user()->role, ['Admin', 'Supervisor'])) {
            return response()->json(['error' => 'Unauthorized: Supervisor or Admin role required to check'], 403);
        }
        $delivery = Delivery::findOrFail($id);
        if ($delivery->status !== 'Pending') {
            return response()->json(['error' => 'Only Pending deliveries can be checked'], 422);
        }
        $delivery->update([
            'status'     => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/payment/{id}/check', function ($id) {
        if (!in_array(auth()->user()->role, ['Admin', 'Supervisor'])) {
            return response()->json(['error' => 'Unauthorized: Supervisor or Admin role required to check'], 403);
        }
        $payment = Payment::findOrFail($id);
        if ($payment->status !== 'Pending') {
            return response()->json(['error' => 'Only Pending payments can be checked'], 422);
        }
        $payment->update([
            'status'     => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/salesOrder/{id}/check', function ($id) {
        if (!in_array(auth()->user()->role, ['Admin', 'Supervisor'])) {
            return response()->json(['error' => 'Unauthorized: Supervisor or Admin role required to check'], 403);
        }
        $order = SalesOrder::findOrFail($id);
        // Normalize status comparison (trim and case-insensitive)
        $status = trim($order->status ?? '');
        if (strtolower($status) !== 'pending') {
            return response()->json([
                'error' => 'Only Pending sales orders can be checked',
                'current_status' => $order->status, // Debug info
            ], 422);
        }
        $order->update([
            'status'     => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/customer/{id}/check', function ($id) {
        if (!in_array(auth()->user()->role, ['Admin', 'Supervisor'])) {
            return response()->json(['error' => 'Unauthorized: Supervisor or Admin role required to check'], 403);
        }
        $customer = Customer::findOrFail($id);
        if ($customer->status !== 'Pending') {
            return response()->json(['error' => 'Only Pending customers can be checked'], 422);
        }
        $customer->update([
            'status'     => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });

    // --- Approve endpoints (Level 2: Admin only can approve) ---
    Route::post('/api/approvals/delivery/{id}/approve', function ($id) {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['error' => 'Unauthorized: Admin role required to approve'], 403);
        }
        $delivery = Delivery::findOrFail($id);
        if ($delivery->status !== 'Checked') {
            return response()->json(['error' => 'Delivery must be Checked before approval'], 422);
        }
        $delivery->update([
            'status'      => 'Done',
            'approved_by' => auth()->user()->user_name,
        ]);

        // Phase 4D: If delivery is linked to an SO, check if all deliveries are Done → mark SO as Done
        if (!empty($delivery->siv_id)) {
            $so = SalesOrder::find($delivery->siv_id);
            if ($so) {
                $pendingDeliveries = Delivery::where('siv_id', $so->id)
                    ->whereNotIn('status', ['Done', 'Rejected'])
                    ->count();
                if ($pendingDeliveries === 0) {
                    $so->update(['status' => 'Done']);
                }
            }
        }

        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/delivery/{id}/reject', function ($id) {
        if (!in_array(auth()->user()->role, ['Admin', 'Supervisor'])) {
            return response()->json(['error' => 'Unauthorized: Supervisor or Admin role required to reject'], 403);
        }
        $delivery = Delivery::findOrFail($id);
        $delivery->update(['status' => 'Rejected']);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/payment/{id}/approve', function ($id) {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['error' => 'Unauthorized: Admin role required to approve'], 403);
        }
        $payment = Payment::findOrFail($id);
        if ($payment->status !== 'Checked') {
            return response()->json(['error' => 'Payment must be Checked before approval'], 422);
        }

        // For PO-linked payments, verify at least one delivery is Done
        if ($payment->p_type === 'po' && !empty($payment->po_no)) {
            $confirmedDelivery = Delivery::where('status', 'Done')
                ->where('pr_id', $payment->po_no)
                ->exists();
            $hasDeliveries = Delivery::where('pr_id', $payment->po_no)->exists();
            if ($hasDeliveries && !$confirmedDelivery) {
                return response()->json(['error' => 'At least one delivery must be confirmed (Done) before payment can be approved'], 422);
            }
        }

        $payment->update([
            'status'      => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);

        // Phase 4B: Decrement bank balance on payment approval
        if (!empty($payment->bank_id)) {
            $balanceExists = \DB::table('sam_bank_balance')->where('bank_id', $payment->bank_id)->exists();
            if ($balanceExists) {
                \DB::table('sam_bank_balance')
                    ->where('bank_id', $payment->bank_id)
                    ->decrement('balance', (float)($payment->net_amount ?? 0));
            }
        }

        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/payment/{id}/reject', function ($id) {
        if (!in_array(auth()->user()->role, ['Admin', 'Supervisor'])) {
            return response()->json(['error' => 'Unauthorized: Supervisor or Admin role required to reject'], 403);
        }
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'Rejected']);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/salesOrder/{id}/approve', function ($id) {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['error' => 'Unauthorized: Admin role required to approve'], 403);
        }
        $order = SalesOrder::findOrFail($id);
        if ($order->status !== 'Checked') {
            return response()->json(['error' => 'Sales Order must be Checked before approval'], 422);
        }
        $order->update([
            'status'      => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/salesOrder/{id}/reject', function ($id) {
        if (!in_array(auth()->user()->role, ['Admin', 'Supervisor'])) {
            return response()->json(['error' => 'Unauthorized: Supervisor or Admin role required to reject'], 403);
        }
        $order = SalesOrder::findOrFail($id);
        $order->update(['status' => 'Rejected']);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/customer/{id}/approve', function ($id) {
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['error' => 'Unauthorized: Admin role required to approve'], 403);
        }
        $customer = Customer::findOrFail($id);
        if ($customer->status !== 'Checked') {
            return response()->json(['error' => 'Customer must be Checked before approval'], 422);
        }
        $customer->update([
            'status'      => 'Active',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/customer/{id}/reject', function ($id) {
        if (!in_array(auth()->user()->role, ['Admin', 'Supervisor'])) {
            return response()->json(['error' => 'Unauthorized: Supervisor or Admin role required to reject'], 403);
        }
        $customer = Customer::findOrFail($id);
        $customer->update(['status' => 'Rejected']);
        return response()->json(['success' => true]);
    });

    // ==================== BANK LIST ====================
    Route::get('/banks', function (Request $request) {
        $query = \DB::table('sam_bank')->orderBy('id');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%")
                  ->orWhere('bank_ac_no', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('bank_status', $request->status);
        }

        $banks = $query->get();

        $stats = [
            'total'    => \DB::table('sam_bank')->count(),
            'active'   => \DB::table('sam_bank')->where('bank_status', 'Active')->count(),
            'inactive' => \DB::table('sam_bank')->where('bank_status', 'Inactive')->count(),
        ];

        return Inertia::render('Banks/Index', [
            'banks'   => $banks,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    })->name('banks.index');

    Route::get('/banks/create', function () {
        return Inertia::render('Banks/Create');
    })->name('banks.create');

    Route::post('/banks', function (Request $request) {
        $request->validate([
            'bank_name'   => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'bank_ac_no'  => 'required|string|max:50',
        ]);

        $bankId = \DB::table('sam_bank')->insertGetId([
            'bank_name'          => $request->bank_name,
            'branch_name'        => $request->branch_name,
            'bank_ac_no'         => $request->bank_ac_no,
            'contact_person'     => $request->contact_person ?? '',
            'beginning_balance'  => $request->beginning_balance ?? 0,
            'have_termloan'      => $request->have_termloan ?? 'No',
            'tl_amount'          => $request->tl_amount ?? 0,
            'tl_interest_rate'   => $request->tl_interest_rate ?? 0,
            'have_overdraft'     => $request->have_overdraft ?? 'No',
            'od_amount'          => $request->od_amount ?? 0,
            'od_interest_rate'   => $request->od_interest_rate ?? 0,
            'od_balance'         => $request->od_balance ?? 0,
            'bank_status'        => $request->bank_status ?? 'Active',
            'registered_by'      => auth()->user()->user_name,
            'registered_date'    => now()->format('Y-m-d'),
        ]);

        // Create matching bank_balance row
        \DB::table('sam_bank_balance')->insert([
            'bank_id'     => $bankId,
            'bank_name'   => $request->bank_name,
            'branch_name' => $request->branch_name,
            'acc_no'      => $request->bank_ac_no,
            'balance'     => $request->beginning_balance ?? 0,
            'last_update' => (int) date('Y'),
            'status'      => '',
        ]);

        return redirect()->route('banks.index')->with('success', 'Bank created');
    })->name('banks.store');

    Route::get('/banks/{id}/edit', function ($id) {
        $bank = \DB::table('sam_bank')->where('id', $id)->first();
        if (!$bank) abort(404);
        return Inertia::render('Banks/Edit', ['bank' => $bank]);
    })->name('banks.edit');

    Route::put('/banks/{id}', function (Request $request, $id) {
        $request->validate([
            'bank_name'   => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'bank_ac_no'  => 'required|string|max:50',
        ]);

        \DB::table('sam_bank')->where('id', $id)->update([
            'bank_name'        => $request->bank_name,
            'branch_name'      => $request->branch_name,
            'bank_ac_no'       => $request->bank_ac_no,
            'contact_person'   => $request->contact_person ?? '',
            'have_termloan'    => $request->have_termloan ?? 'No',
            'tl_amount'        => $request->tl_amount ?? 0,
            'tl_interest_rate' => $request->tl_interest_rate ?? 0,
            'have_overdraft'   => $request->have_overdraft ?? 'No',
            'od_amount'        => $request->od_amount ?? 0,
            'od_interest_rate' => $request->od_interest_rate ?? 0,
            'od_balance'       => $request->od_balance ?? 0,
            'bank_status'      => $request->bank_status ?? 'Active',
        ]);

        // Sync bank_balance denormalized fields
        \DB::table('sam_bank_balance')->where('bank_id', $id)->update([
            'bank_name'   => $request->bank_name,
            'branch_name' => $request->branch_name,
            'acc_no'      => $request->bank_ac_no,
        ]);

        return redirect()->route('banks.index')->with('success', 'Bank updated');
    })->name('banks.update');

    // ==================== BANK BALANCE DETAIL ====================
    Route::get('/bank-balance', function () {
        $banks = \DB::table('sam_bank')
            ->where('bank_status', 'Active')
            ->orderBy('bank_name')
            ->get();

        $rows = [];
        foreach ($banks as $bank) {
            $bal = \DB::table('sam_bank_balance')->where('bank_id', $bank->id)->first();

            $totalPayment = \DB::table('sam_payment')
                ->where('bank_id', $bank->id)
                ->where('status', 'Approved')
                ->sum('net_amount');

            $totalCollection = \DB::table('sam_collection')
                ->where('bank', $bank->bank_name)
                ->where('status', 'Approved')
                ->sum('amount');

            $rows[] = [
                'id'            => $bank->id,
                'bank_name'     => $bank->bank_name,
                'branch_name'   => $bank->branch_name,
                'bank_ac_no'    => $bank->bank_ac_no,
                'balance'       => $bal ? (float) $bal->balance : 0,
                'status'        => $bal ? $bal->status : '',
                'total_payment' => (float) $totalPayment,
                'total_collection' => (float) $totalCollection,
            ];
        }

        $totalBalance = array_sum(array_column($rows, 'balance'));

        return Inertia::render('BankBalance/Index', [
            'rows'         => $rows,
            'totalBalance' => $totalBalance,
        ]);
    })->name('bank-balance.index');

    // ==================== BANK TRANSFERS ====================
    Route::get('/bank-transfers', function (Request $request) {
        $query = \DB::table('sam_bank_transfer')
            ->select('id', 't_no as btr_no', 't_date as transfer_date', 'from_bank', 'to_bank', 
                     'transfer_from as from_account', 'transfer_to as to_account',
                     'i_amount as amount', 'status', 'registered_by', 'registered_date')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('t_no', 'like', "%{$request->search}%")
                  ->orWhere('from_bank', 'like', "%{$request->search}%")
                  ->orWhere('to_bank', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $transfers = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_bank_transfer')->whereNotIn('status', ['Approved', 'Void'])->count(),
            'approved' => \DB::table('sam_bank_transfer')->where('status', 'Approved')->count(),
            'totalAmount' => \DB::table('sam_bank_transfer')->where('status', 'Approved')->sum('i_amount'),
        ];
        
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        
        return Inertia::render('BankTransfers/Index', [
            'transfers' => $transfers,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
            'banks' => $banks,
        ]);
    })->name('bank-transfers.index');

    Route::get('/bank-transfers/create', function () {
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        return Inertia::render('BankTransfers/Create', ['banks' => $banks]);
    })->name('bank-transfers.create');

    Route::post('/bank-transfers', function (Request $request) {
        \DB::table('sam_bank_transfer')->insert([
            't_no' => $request->btr_no,
            't_date' => $request->transfer_date,
            'from_bank' => $request->from_bank,
            'transfer_from' => $request->from_account,
            'to_bank' => $request->to_bank,
            'transfer_to' => $request->to_account,
            'i_amount' => $request->amount,
            'i_amount_left' => $request->amount,
            'payment_from' => '',
            'bank_id' => 0,
            'recon' => '',
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('bank-transfers.index')->with('success', 'Bank Transfer created');
    })->name('bank-transfers.store');

    Route::put('/bank-transfers/{id}/check', function ($id) {
        \DB::table('sam_bank_transfer')->where('id', $id)->update([
            'status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('bank-transfers.index')->with('success', 'Transfer checked');
    })->name('bank-transfers.check');

    Route::put('/bank-transfers/{id}/approve', function ($id) {
        $transfer = \DB::table('sam_bank_transfer')->where('id', $id)->first();
        if (!$transfer) {
            return redirect()->route('bank-transfers.index')->withErrors(['error' => 'Transfer not found']);
        }

        \DB::table('sam_bank_transfer')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);

        $amount = (float)($transfer->i_amount ?? 0);
        if ($amount > 0) {
            // Decrement source bank balance
            $fromBank = \DB::table('sam_bank')->where('bank_name', $transfer->from_bank)->first();
            if ($fromBank) {
                \DB::table('sam_bank_balance')
                    ->where('bank_id', $fromBank->id)
                    ->decrement('balance', $amount);
            }
            // Increment destination bank balance
            $toBank = \DB::table('sam_bank')->where('bank_name', $transfer->to_bank)->first();
            if ($toBank) {
                \DB::table('sam_bank_balance')
                    ->where('bank_id', $toBank->id)
                    ->increment('balance', $amount);
            }
        }

        return redirect()->route('bank-transfers.index')->with('success', 'Transfer approved');
    })->name('bank-transfers.approve');

    Route::get('/bank-transfers/{id}/edit', function ($id) {
        $transfer = \DB::table('sam_bank_transfer')->where('id', $id)->first();
        if (!$transfer) {
            return redirect()->route('bank-transfers.index')->with('error', 'Transfer not found');
        }
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        return Inertia::render('BankTransfers/Edit', [
            'transfer' => $transfer,
            'banks' => $banks,
        ]);
    })->name('bank-transfers.edit');

    Route::put('/bank-transfers/{id}', function ($id, Request $request) {
        $transfer = \DB::table('sam_bank_transfer')->where('id', $id)->first();
        if (!$transfer || $transfer->status !== 'Pending') {
            return redirect()->route('bank-transfers.index')->with('error', 'Only Pending transfers can be edited');
        }
        \DB::table('sam_bank_transfer')->where('id', $id)->update([
            't_no' => $request->btr_no,
            't_date' => $request->transfer_date,
            'from_bank' => $request->from_bank,
            'transfer_from' => $request->from_account,
            'to_bank' => $request->to_bank,
            'transfer_to' => $request->to_account,
            'i_amount' => $request->amount,
            'i_amount_left' => $request->amount,
        ]);
        return redirect()->route('bank-transfers.index')->with('success', 'Transfer updated');
    })->name('bank-transfers.update');

    // ==================== BANK RECONCILIATION ====================
    Route::get('/bank-reconciliation', function (Request $request) {
        // Mapeo de meses en inglés (números) a meses etíopes
        $ethiopianMonths = [
            '1' => 'MESKEREM',   // Septiembre
            '2' => 'TIKEMT',     // Octubre
            '3' => 'HIDAR',      // Noviembre
            '4' => 'TAHISAS',    // Diciembre
            '5' => 'TIR',        // Enero
            '6' => 'YEKATIT',    // Febrero
            '7' => 'MEGABIT',    // Marzo
            '8' => 'MIAZIA',     // Abril
            '9' => 'GINBOT',     // Mayo
            '10' => 'SENE',      // Junio
            '11' => 'HAMLE',     // Julio
            '12' => 'NEHASE',    // Agosto
        ];
        
        // Primero obtener todos los registros con filtros aplicados
        $baseQuery = \DB::table('sam_bank_reconcilation');
        
        if ($request->bank) {
            $baseQuery->where('bank_name', $request->bank);
        }
        
        if ($request->status) {
            $baseQuery->where('status', $request->status);
        }
        
        if ($request->month && isset($ethiopianMonths[$request->month])) {
            $baseQuery->where('month', $ethiopianMonths[$request->month]);
        }
        
        if ($request->year) {
            $baseQuery->where('year', (string)$request->year);
        }
        
        // Obtener el ID máximo de cada grupo único
        $maxIds = $baseQuery
            ->selectRaw('MAX(id) as max_id')
            ->groupBy('br_no', 'bank_name', 'month', 'year')
            ->pluck('max_id')
            ->toArray();
        
        if (empty($maxIds)) {
            $maxIds = [0]; // Evitar error si no hay resultados
        }
        
        // Consulta principal con los IDs únicos
        $query = \DB::table('sam_bank_reconcilation')
            ->select('id', 'br_no', 'bank_name', 'month', 'year', 'balance as bank_balance', 
                     'beginning_balance as book_balance', 'diff as difference', 'status')
            ->whereIn('id', $maxIds)
            ->orderBy('id', 'desc');
        
        $reconciliations = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_bank_reconcilation')->where('status', 'Pending')->count(),
            'approved' => \DB::table('sam_bank_reconcilation')->where('status', 'Approved')->count(),
            'difference' => \DB::table('sam_bank_reconcilation')->sum('diff'),
        ];
        
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        
        return Inertia::render('BankReconciliation/Index', [
            'reconciliations' => $reconciliations,
            'banks' => $banks,
            'filters' => $request->only(['bank', 'status', 'month', 'year']),
            'stats' => $stats,
        ]);
    })->name('bank-reconciliation.index');

    Route::get('/bank-reconciliation/create', function () {
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        return Inertia::render('BankReconciliation/Create', ['banks' => $banks]);
    })->name('bank-reconciliation.create');

    Route::get('/bank-reconciliation/{id}', function ($id) {
        $reconciliation = \DB::table('sam_bank_reconcilation')
            ->where('id', $id)
            ->first();
        
        if (!$reconciliation) {
            abort(404, 'Reconciliation not found');
        }
        
        // Obtener todos los registros relacionados con el mismo br_no, bank_name, month, year
        $relatedRecords = \DB::table('sam_bank_reconcilation')
            ->where('br_no', $reconciliation->br_no)
            ->where('bank_name', $reconciliation->bank_name)
            ->where('month', $reconciliation->month)
            ->where('year', $reconciliation->year)
            ->orderBy('id')
            ->get();
        
        return Inertia::render('BankReconciliation/Show', [
            'reconciliation' => $reconciliation,
            'relatedRecords' => $relatedRecords,
        ]);
    })->name('bank-reconciliation.show');

    Route::post('/bank-reconciliation', function (Request $request) {
        // Mapeo de meses en inglés (números) a meses etíopes
        $ethiopianMonths = [
            '1' => 'MESKEREM',   // Septiembre
            '2' => 'TIKEMT',     // Octubre
            '3' => 'HIDAR',      // Noviembre
            '4' => 'TAHISAS',    // Diciembre
            '5' => 'TIR',        // Enero
            '6' => 'YEKATIT',    // Febrero
            '7' => 'MEGABIT',    // Marzo
            '8' => 'MIAZIA',     // Abril
            '9' => 'GINBOT',     // Mayo
            '10' => 'SENE',      // Junio
            '11' => 'HAMLE',     // Julio
            '12' => 'NEHASE',    // Agosto
        ];
        
        $bank = \DB::table('sam_bank')->where('bank_name', $request->bank_name)->first();
        
        \DB::table('sam_bank_reconcilation')->insert([
            'br_no' => $request->br_no,
            'bank_id' => $bank->id ?? 0,
            'bank_name' => $request->bank_name,
            'month' => $ethiopianMonths[$request->period_month] ?? 'MESKEREM',
            'year' => (string)$request->period_year,
            'balance' => $request->bank_balance ?? 0,
            'beginning_balance' => $request->book_balance ?? 0,
            'diff' => $request->difference ?? 0,
            'payment_type' => '',
            'amount' => 0,
            'statement' => '',
            'statement_size' => 0,
            'statement_type' => '',
            'type_of_balance' => '',
            'od_balance' => 0,
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'date_registered' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('bank-reconciliation.index')->with('success', 'Bank Reconciliation created');
    })->name('bank-reconciliation.store');

    Route::put('/bank-reconciliation/{id}/check', function ($id) {
        \DB::table('sam_bank_reconcilation')->where('id', $id)->update([
            'status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('bank-reconciliation.index')->with('success', 'Reconciliation checked');
    })->name('bank-reconciliation.check');

    Route::put('/bank-reconciliation/{id}/approve', function ($id) {
        \DB::table('sam_bank_reconcilation')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('bank-reconciliation.index')->with('success', 'Reconciliation approved');
    })->name('bank-reconciliation.approve');

    // ==================== COLLECTIONS ====================
    Route::get('/collections', function (Request $request) {
        $query = \DB::table('sam_collection')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('collection_no', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%")
                  ->orWhere('source', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        // Date filters
        if ($request->date_from) {
            $query->where('collection_date', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->where('collection_date', '<=', $request->date_to);
        }
        
        $collections = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_collection')->where('status', 'Pending')->count(),
            'approved' => \DB::table('sam_collection')->where('status', 'Approved')->count(),
            'totalAmount' => \DB::table('sam_collection')->where('status', 'Approved')->sum('amount'),
        ];
        
        return Inertia::render('Collections/Index', [
            'collections' => $collections,
            'filters' => $request->only(['search', 'status', 'type', 'date_from', 'date_to']),
            'stats' => $stats,
        ]);
    })->name('collections.index');

    Route::get('/collections/create', function (Request $request) {
        $customers = Customer::orderBy('company_name')->get();
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        return Inertia::render('Collections/Create', [
            'customers' => $customers,
            'banks' => $banks,
        ]);
    })->name('collections.create');

    Route::post('/collections', function (Request $request) {
        $customer = $request->customer_id ? Customer::find($request->customer_id) : null;
        
        \DB::table('sam_collection')->insert([
            'collection_no' => $request->collection_no,
            'collection_date' => $request->collection_date,
            'type' => $request->type,
            'customer_id' => $request->customer_id,
            'customer_name' => $customer?->company_name,
            'source' => $request->source,
            'collection_type' => $request->collection_type,
            'bank' => $request->bank,
            'amount' => $request->amount,
            'cheque_no' => $request->cheque_no,
            'cheque_date' => $request->cheque_date,
            'reference_no' => $request->reference_no,
            'deposit_date' => $request->deposit_date,
            'description' => $request->description,
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('collections.index')->with('success', 'Collection created');
    })->name('collections.store');

    Route::put('/collections/{id}/check', function ($id) {
        \DB::table('sam_collection')->where('id', $id)->update([
            'status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('collections.index')->with('success', 'Collection checked');
    })->name('collections.check');

    Route::put('/collections/{id}/approve', function ($id) {
        $collection = \DB::table('sam_collection')->where('id', $id)->first();
        if (!$collection) {
            abort(404);
        }

        \DB::table('sam_collection')->where('id', $id)->update([
            'status'      => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);

        // Decrement remaning on the linked Sales Order(s) for this customer (FIFO)
        if (!empty($collection->customer_id)) {
            $amount = (float)($collection->amount ?? 0);

            // Find Sales Orders for this customer that still have a balance, oldest first
            $orders = \DB::table('sam_sales_order')
                ->where('customer', $collection->customer_id)
                ->where('remaning', '>', 0)
                ->where('status', 'Approved')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($orders as $order) {
                if ($amount <= 0) break;

                $deduct = min($amount, (float)$order->remaning);

                \DB::table('sam_sales_order')
                    ->where('id', $order->id)
                    ->update(['remaning' => $order->remaning - $deduct]);

                $amount -= $deduct;
            }
        }

        // Increase bank balance when collection is approved
        if (!empty($collection->bank)) {
            $bank = \DB::table('sam_bank')->where('bank_name', $collection->bank)->first();
            if ($bank) {
                $balanceExists = \DB::table('sam_bank_balance')->where('bank_id', $bank->id)->exists();
                if ($balanceExists) {
                    \DB::table('sam_bank_balance')
                        ->where('bank_id', $bank->id)
                        ->increment('balance', (float)($collection->amount ?? 0));
                }
            }
        }

        return redirect()->route('collections.index')->with('success', 'Collection approved');
    })->name('collections.approve');

    // ==================== GOODS IN TRANSIT (GIT) ====================
    Route::get('/goods-in-transit', function (Request $request) {
        $query = \DB::table('sam_git')
            ->leftJoin('sam_supplier', 'sam_git.supplier_id', '=', 'sam_supplier.id')
            ->leftJoin('sam_customer', 'sam_git.customer', '=', 'sam_customer.id')
            ->select('sam_git.id', 'sam_git.g_no as git_no', 'sam_git.issued_date as git_date',
                     'sam_supplier.supplier_name', 'sam_customer.company_name as customer_name',
                     'sam_git.item as item_name', 'sam_git.quantity', 'sam_git.unit_price',
                     'sam_git.truck_plate_no as plate_no', 'sam_git.driver_name',
                     'sam_git.ta_id as transporter_id', 'sam_git.status')
            ->orderBy('sam_git.id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('sam_git.g_no', 'like', "%{$request->search}%")
                  ->orWhere('sam_supplier.supplier_name', 'like', "%{$request->search}%")
                  ->orWhere('sam_customer.company_name', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('sam_git.status', $request->status);
        }
        
        $gits = $query->paginate(20)->withQueryString();
        
        $stats = [
            'inTransit' => \DB::table('sam_git')->whereNotIn('status', ['Done', 'Delivered', 'hold', 'Hold', 'Void'])->count(),
            'delivered' => \DB::table('sam_git')->whereIn('status', ['Done', 'Delivered'])->count(),
            'onHold' => \DB::table('sam_git')->whereIn('status', ['hold', 'Hold'])->count(),
            'totalQty' => \DB::table('sam_git')->sum('quantity'),
        ];
        
        return Inertia::render('GoodsInTransit/Index', [
            'gits' => $gits,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    })->name('goods-in-transit.index');

    Route::get('/goods-in-transit/create', function () {
        return Inertia::render('GoodsInTransit/Create', [
            'purchaseOrders' => PurchaseOrder::orderBy('id', 'desc')->limit(100)->get(),
            'salesOrders' => SalesOrder::orderBy('id', 'desc')->limit(100)->get(),
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
            'customers' => Customer::orderBy('company_name')->get(),
            'transporters' => Transporter::orderBy('company_name')->get(),
            'items' => \DB::table('sam_item')->orderBy('item')->get(),
        ]);
    })->name('goods-in-transit.create');

    Route::post('/goods-in-transit', function (Request $request) {
        \DB::table('sam_git')->insert([
            'g_no' => $request->git_no,
            'issued_date' => $request->git_date,
            'git_type' => $request->git_type ?? 'purchase',
            'po_id' => $request->po_id ?? 0,
            'so_id' => $request->so_id ?? 0,
            'p_id' => 0,
            'supplier_id' => $request->supplier_id ?? 0,
            'customer' => $request->customer_id ?? 0,
            'item' => $request->item_name ?? '',
            'quantity' => $request->quantity ?? 0,
            'unit_price' => $request->unit_price ?? 0,
            'total' => ($request->quantity ?? 0) * ($request->unit_price ?? 0),
            'tax_p' => '',
            'ta_id' => $request->transporter_id ?? 0,
            'trans_type' => '',
            'truck_plate_no' => $request->plate_no ?? '',
            'driver_name' => $request->driver_name ?? '',
            'driver_phone_no' => '',
            'delivered_to' => $request->destination ?? '',
            'remark' => $request->remarks ?? '',
            'd_id' => 0,
            'd_balance' => 0,
            'cupon_no' => '',
            'estimated_km' => 0,
            'trans_asc' => '',
            'requested_price' => 0,
            'old_price' => 0,
            'r_status' => '',
            'r_approved_by' => '',
            'status' => 'pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('goods-in-transit.index')->with('success', 'GIT created');
    })->name('goods-in-transit.store');

    Route::put('/goods-in-transit/{id}/delivered', function ($id) {
        \DB::table('sam_git')->where('id', $id)->update(['status' => 'Done']);
        return redirect()->route('goods-in-transit.index')->with('success', 'GIT marked as delivered');
    })->name('goods-in-transit.delivered');

    Route::put('/goods-in-transit/{id}/hold', function ($id) {
        \DB::table('sam_git')->where('id', $id)->update(['status' => 'hold']);
        return redirect()->route('goods-in-transit.index')->with('success', 'GIT put on hold');
    })->name('goods-in-transit.hold');

    Route::get('/goods-in-transit/{id}/edit', function ($id) {
        $git = \DB::table('sam_git')->where('id', $id)->first();
        
        if (!$git) {
            abort(404, 'GIT not found');
        }
        
        return Inertia::render('GoodsInTransit/Edit', [
            'git' => $git,
            'purchaseOrders' => PurchaseOrder::orderBy('id', 'desc')->limit(100)->get(),
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
            'customers' => Customer::orderBy('company_name')->get(),
            'transporters' => Transporter::orderBy('company_name')->get(),
            'items' => \DB::table('sam_item')->orderBy('item')->get(),
        ]);
    })->name('goods-in-transit.edit');

    Route::put('/goods-in-transit/{id}', function ($id, Request $request) {
        \DB::table('sam_git')->where('id', $id)->update([
            'g_no' => $request->git_no,
            'issued_date' => $request->git_date,
            'po_id' => $request->po_id ?? 0,
            'supplier_id' => $request->supplier_id ?? 0,
            'customer' => $request->customer_id ?? 0,
            'item' => $request->item_name ?? '',
            'quantity' => $request->quantity ?? 0,
            'unit_price' => $request->unit_price ?? 0,
            'total' => ($request->quantity ?? 0) * ($request->unit_price ?? 0),
            'ta_id' => $request->transporter_id ?? 0,
            'truck_plate_no' => $request->plate_no ?? '',
            'driver_name' => $request->driver_name ?? '',
            'delivered_to' => $request->destination ?? '',
            'remark' => $request->remarks ?? '',
        ]);
        return redirect()->route('goods-in-transit.index')->with('success', 'GIT updated');
    })->name('goods-in-transit.update');

    // ==================== SETTLEMENTS ====================
    Route::get('/settlements', function (Request $request) {
        $query = \DB::table('sam_settlement')
            ->orderBy('id', 'desc');
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $settlements = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_settlement')->where('status', 'Pending')->count(),
            'approved' => \DB::table('sam_settlement')->where('status', 'Approved')->count(),
            'totalAmount' => \DB::table('sam_settlement')->sum('settled_amount'),
        ];
        
        return Inertia::render('Settlements/Index', [
            'settlements' => $settlements,
            'filters' => $request->only(['type', 'status']),
            'stats' => $stats,
        ]);
    })->name('settlements.index');

    Route::get('/settlements/create', function (Request $request) {
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        return Inertia::render('Settlements/Create', [
            'purchaseOrders' => PurchaseOrder::orderBy('id', 'desc')->limit(100)->get(),
            'recipients' => Transporter::orderBy('company_name')->get(),
            'banks' => $banks,
        ]);
    })->name('settlements.create');

    Route::post('/settlements', function (Request $request) {
        $typeMap = [
            'pos' => 'POS',
            'pcs' => 'PCS',
            'crs' => 'CRS',
            'advance' => 'Advance',
            'transporter' => 'Transporter',
        ];
        
        \DB::table('sam_settlement')->insert([
            'settlement_no' => $request->settlement_no,
            'settlement_date' => $request->settlement_date,
            'type' => $typeMap[$request->type] ?? $request->type,
            'po_id' => $request->po_id,
            'recipient_id' => $request->recipient_id,
            'reference_no' => $request->reference_no,
            'original_amount' => $request->original_amount,
            'settled_amount' => $request->settled_amount,
            'balance' => $request->balance,
            'petty_cash_account' => $request->petty_cash_account,
            'bank' => $request->bank,
            'remarks' => $request->remarks,
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('settlements.index')->with('success', 'Settlement created');
    })->name('settlements.store');

    Route::put('/settlements/{id}/approve', function ($id) {
        \DB::table('sam_settlement')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('settlements.index')->with('success', 'Settlement approved');
    })->name('settlements.approve');

    // ==================== STOCK BALANCE ====================
    Route::get('/stock-balance', function (Request $request) {
        // Obtener todos los items activos
        $items = \DB::table('sam_item')
            ->where('status', 'Active')
            ->orderBy('item')
            ->get();
        
        $stockBalances = [];
        
        foreach ($items as $item) {
            // Calcular entradas: Goods Receive
            $incoming = \DB::table('sam_goods_receive')
                ->where('item', $item->id)
                ->where('status', 'Approved')
                ->sum('remaning') ?? 0;
            
            // Calcular salidas: Deliveries entregadas
            $outgoing = \DB::table('sam_delivery')
                ->where('item', $item->id)
                ->where('status', 'Done')
                ->sum('quantity') ?? 0;
            
            // Stock balance registrado
            $registeredBalance = \DB::table('sam_stock_balance')
                ->where('item', $item->id)
                ->where('status', 'Done')
                ->sum(\DB::raw('CAST(stock_balance AS DECIMAL(10,2))')) ?? 0;
            
            // Balance calculado = Entradas - Salidas
            $calculatedBalance = $incoming - $outgoing;
            
            // Usar el mayor entre balance registrado y calculado
            $currentBalance = max($registeredBalance, $calculatedBalance);
            
            if ($request->item && $item->id != $request->item) {
                continue;
            }
            
            if ($request->min_balance && $currentBalance >= $request->min_balance) {
                continue;
            }
            
            $stockBalances[] = [
                'item_id' => $item->id,
                'item_name' => $item->item,
                'item_category' => $item->item_category,
                'unit' => $item->unit,
                'incoming' => $incoming,
                'outgoing' => $outgoing,
                'registered_balance' => $registeredBalance,
                'calculated_balance' => $calculatedBalance,
                'current_balance' => $currentBalance,
            ];
        }
        
        // Ordenar por balance (menor primero para ver items con bajo stock)
        usort($stockBalances, function($a, $b) {
            return $a['current_balance'] <=> $b['current_balance'];
        });
        
        $itemsList = \DB::table('sam_item')
            ->where('status', 'Active')
            ->orderBy('item')
            ->get();
        
        return Inertia::render('StockBalance/Index', [
            'stockBalances' => $stockBalances,
            'items' => $itemsList,
            'filters' => $request->only(['item', 'min_balance']),
        ]);
    })->name('stock-balance.index');

    Route::get('/stock-balance/{itemId}', function ($itemId) {
        $item = \DB::table('sam_item')->where('id', $itemId)->first();
        
        if (!$item) {
            abort(404, 'Item not found');
        }
        
        // Historial de entradas
        $incoming = \DB::table('sam_goods_receive')
            ->where('item', $itemId)
            ->where('status', 'Approved')
            ->select('id', 'gr_no as grv_no', 'received_qty as quantity', 'remaning', 'grv_date as date', 'registered_by')
            ->orderBy('grv_date', 'desc')
            ->get();
        
        // Historial de salidas
        $outgoing = \DB::table('sam_delivery')
            ->where('item', $itemId)
            ->where('status', 'Done')
            ->select('id', 'd_no', 'quantity', 'issue_date as date', 'registered_by', 'project')
            ->orderBy('issue_date', 'desc')
            ->get();
        
        // Stock balance registrado
        $registeredBalances = \DB::table('sam_stock_balance')
            ->where('item', $itemId)
            ->where('status', 'Done')
            ->orderBy('registered_date', 'desc')
            ->get();
        
        // Calcular balance actual
        $totalIncoming = $incoming->sum('remaning');
        $totalOutgoing = $outgoing->sum('quantity');
        $currentBalance = $totalIncoming - $totalOutgoing;
        
        return Inertia::render('StockBalance/Show', [
            'item' => $item,
            'currentBalance' => $currentBalance,
            'totalIncoming' => $totalIncoming,
            'totalOutgoing' => $totalOutgoing,
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'registeredBalances' => $registeredBalances,
        ]);
    })->name('stock-balance.show');

    // ==================== PURCHASE REQUISITION (PR) ====================
    Route::get('/purchase-requisitions', function (Request $request) {
        $query = \DB::table('sam_purchase_requisition')
            ->leftJoin('sam_item', 'sam_purchase_requisition.item', '=', 'sam_item.id')
            ->select('sam_purchase_requisition.*', 'sam_item.item as item_name')
            ->orderBy('sam_purchase_requisition.id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('sam_purchase_requisition.pr_no', 'like', "%{$request->search}%")
                  ->orWhere('sam_purchase_requisition.item_desc', 'like', "%{$request->search}%")
                  ->orWhere('sam_purchase_requisition.request_from', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('sam_purchase_requisition.status', $request->status);
        }
        
        if ($request->pr_status) {
            $query->where('sam_purchase_requisition.pr_status', $request->pr_status);
        }
        
        $requisitions = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_purchase_requisition')->where('status', 'Pending')->count(),
            'checked' => \DB::table('sam_purchase_requisition')->where('status', 'Checked')->count(),
            'approved' => \DB::table('sam_purchase_requisition')->where('status', 'Approved')->count(),
            'po_done' => \DB::table('sam_purchase_requisition')->where('pr_status', 'po_done')->count(),
        ];
        
        return Inertia::render('PurchaseRequisition/Index', [
            'requisitions' => $requisitions,
            'filters' => $request->only(['search', 'status', 'pr_status']),
            'stats' => $stats,
        ]);
    })->name('purchase-requisitions.index');

    Route::get('/purchase-requisitions/create', function (Request $request) {
        $srData = null;
        if ($request->sr_id) {
            $sr = \DB::table('sam_store_requisition')->where('id', $request->sr_id)->first();
            if ($sr) {
                $srData = [
                    'sr_id' => $sr->id,
                    'sr_no' => $sr->sr_no,
                    'item' => $sr->item,
                    'item_desc' => $sr->item_desc,
                    'sr_quantity' => $sr->sr_quantity,
                    'unit' => $sr->unit,
                    'used_for' => $sr->used_for,
                    'request_from' => $sr->sr_from,
                ];
            }
        }
        
        $nextPrNo = generateNextNumber('sam_purchase_requisition', 'pr_no', 'PR');
        
        return Inertia::render('PurchaseRequisition/Create', [
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
            'storeRequisitions' => \DB::table('sam_store_requisition')
                ->where('status', 'Approved')
                ->where('pr_status', '')
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get(),
            'srData' => $srData,
            'nextPrNo' => $nextPrNo,
        ]);
    })->name('purchase-requisitions.create');

    Route::post('/purchase-requisitions', function (Request $request) {
        $item = \DB::table('sam_item')->where('id', $request->item)->first();
        
        // Auto-generate PR number if not provided
        $prNo = !empty($request->pr_no) ? $request->pr_no : generateNextNumber('sam_purchase_requisition', 'pr_no', 'PR');
        
        \DB::table('sam_purchase_requisition')->insert([
            'pr_no' => $prNo,
            'sr_id' => $request->sr_id ?? '',
            'pr_type' => $request->pr_type ?? 'direct',
            'used_for' => $request->used_for ?? '',
            'item' => $request->item,
            'item_desc' => $item->item ?? $request->item_desc,
            'unit' => $item->unit ?? $request->unit,
            'pr_quantity' => $request->pr_quantity,
            'pr_balance' => $request->pr_quantity,
            'pr_status' => 'pr_pending',
            'remark' => $request->remark ?? '',
            'status' => 'Pending',
            'request_from' => $request->request_from ?? '',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
            'e_date' => $request->e_date ?? now()->format('Y-m-d'),
        ]);
        
        // Si viene de Store Requisition, actualizar el status
        if ($request->sr_id) {
            \DB::table('sam_store_requisition')
                ->where('id', $request->sr_id)
                ->update(['pr_status' => 'pr_created']);
        }
        
        return redirect()->route('purchase-requisitions.index')->with('success', 'Purchase Requisition created');
    })->name('purchase-requisitions.store');

    Route::get('/purchase-requisitions/{id}/edit', function ($id) {
        $requisition = \DB::table('sam_purchase_requisition')->where('id', $id)->first();
        
        if (!$requisition) {
            abort(404, 'Purchase Requisition not found');
        }
        
        return Inertia::render('PurchaseRequisition/Edit', [
            'requisition' => $requisition,
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
        ]);
    })->name('purchase-requisitions.edit');

    Route::put('/purchase-requisitions/{id}', function ($id, Request $request) {
        $item = \DB::table('sam_item')->where('id', $request->item)->first();
        
        \DB::table('sam_purchase_requisition')->where('id', $id)->update([
            'pr_no' => $request->pr_no,
            'item' => $request->item,
            'item_desc' => $item->item ?? $request->item_desc,
            'unit' => $item->unit ?? $request->unit,
            'pr_quantity' => $request->pr_quantity,
            'pr_balance' => $request->pr_balance ?? $request->pr_quantity,
            'used_for' => $request->used_for ?? '',
            'remark' => $request->remark ?? '',
            'request_from' => $request->request_from ?? '',
            'e_date' => $request->e_date ?? now()->format('Y-m-d'),
        ]);
        
        return redirect()->route('purchase-requisitions.index')->with('success', 'Purchase Requisition updated');
    })->name('purchase-requisitions.update');

    Route::put('/purchase-requisitions/{id}/check', function ($id) {
        \DB::table('sam_purchase_requisition')->where('id', $id)->update([
            'status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('purchase-requisitions.index')->with('success', 'Purchase Requisition checked');
    })->name('purchase-requisitions.check');

    Route::put('/purchase-requisitions/{id}/approve', function ($id) {
        \DB::table('sam_purchase_requisition')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('purchase-requisitions.index')->with('success', 'Purchase Requisition approved');
    })->name('purchase-requisitions.approve');

    Route::put('/purchase-requisitions/{id}/convert-to-po', function ($id) {
        $pr = \DB::table('sam_purchase_requisition')->where('id', $id)->first();
        
        if (!$pr || $pr->status != 'Approved') {
            return redirect()->route('purchase-requisitions.index')->with('error', 'PR must be approved to convert to PO');
        }
        
        // Marcar PR como convertido a PO
        \DB::table('sam_purchase_requisition')->where('id', $id)->update([
            'pr_status' => 'po_done',
        ]);
        
        // Redirigir a crear PO con el PR prellenado
        return redirect()->route('purchase-orders.create', ['pr_id' => $id])->with('success', 'PR ready to convert to PO');
    })->name('purchase-requisitions.convert-to-po');

    // ==================== PROFORMA INVOICE ====================
    Route::get('/proforma-invoices', function (Request $request) {
        $query = \DB::table('sam_proforma')
            ->leftJoin('sam_customer', 'sam_proforma.customer', '=', 'sam_customer.id')
            ->leftJoin('sam_item', 'sam_proforma.item_id', '=', 'sam_item.id')
            ->select('sam_proforma.*', 'sam_customer.company_name as customer_name', 'sam_item.item as item_name')
            ->orderBy('sam_proforma.id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('sam_proforma.pi_no', 'like', "%{$request->search}%")
                  ->orWhere('sam_proforma.item', 'like', "%{$request->search}%")
                  ->orWhere('sam_customer.company_name', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('sam_proforma.status', $request->status);
        }
        
        if ($request->customer) {
            $query->where('sam_proforma.customer', $request->customer);
        }
        
        $proformas = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_proforma')->where('status', 'Pending')->count(),
            'checked' => \DB::table('sam_proforma')->where('status', 'Checked')->count(),
            'approved' => \DB::table('sam_proforma')->where('status', 'Approved')->count(),
            'void' => \DB::table('sam_proforma')->where('status', 'Void')->count(),
        ];
        
        $customers = Customer::orderBy('company_name')->get();
        
        return Inertia::render('ProformaInvoice/Index', [
            'proformas' => $proformas,
            'customers' => $customers,
            'filters' => $request->only(['search', 'status', 'customer']),
            'stats' => $stats,
        ]);
    })->name('proforma-invoices.index');

    Route::get('/proforma-invoices/create', function () {
        return Inertia::render('ProformaInvoice/Create', [
            'customers' => Customer::orderBy('company_name')->get(),
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
            'salesOrders' => SalesOrder::orderBy('id', 'desc')->limit(100)->get(),
        ]);
    })->name('proforma-invoices.create');

    Route::post('/proforma-invoices', function (Request $request) {
        $item = \DB::table('sam_item')->where('id', $request->item_id)->first();
        $customer = Customer::find($request->customer);
        
        $unitPrice = floatval($request->unit_price) || 0;
        $quantity = floatval($request->quantity) || 0;
        $taxP = floatval($request->tax_p) || 0;
        $lineTotal = $unitPrice * $quantity;
        $vat = $lineTotal * ($taxP / 100);
        
        \DB::table('sam_proforma')->insert([
            's_id' => $request->s_id ?? '',
            'pi_no' => $request->pi_no,
            'item_id' => $request->item_id,
            'item' => $item->item ?? '',
            'quantity' => $quantity,
            'unit' => $request->unit ?? $item->unit ?? '',
            'unit_price' => (string)$unitPrice,
            'tax_p' => (string)$taxP,
            'line_total' => (string)$lineTotal,
            'validity' => $request->validity ?? '30',
            'd_time' => $request->d_time ?? '',
            'term_of_payment' => $request->term_of_payment ?? 'full payment',
            'payment_percent' => $request->payment_percent ?? '0',
            'remark' => $request->remark ?? '',
            'p_date' => $request->p_date ?? now()->format('Y-m-d'),
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
            'registered_time' => now()->format('H:i:s'),
            'customer' => $request->customer,
            'customer_name' => $customer->company_name ?? '',
            'transport' => $request->transport ?? 'No',
            't_unit_price' => floatval($request->t_unit_price) || 0,
            'transport_unit' => $request->transport_unit ?? '',
            't_tax_p' => floatval($request->t_tax_p) || 0,
            't_km' => floatval($request->t_km) || 0,
            'location' => $request->location ?? '',
        ]);
        
        return redirect()->route('proforma-invoices.index')->with('success', 'Proforma Invoice created');
    })->name('proforma-invoices.store');

    Route::get('/proforma-invoices/{id}/edit', function ($id) {
        $proforma = \DB::table('sam_proforma')->where('id', $id)->first();
        
        if (!$proforma) {
            abort(404, 'Proforma Invoice not found');
        }
        
        return Inertia::render('ProformaInvoice/Edit', [
            'proforma' => $proforma,
            'customers' => Customer::orderBy('company_name')->get(),
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
        ]);
    })->name('proforma-invoices.edit');

    Route::put('/proforma-invoices/{id}', function ($id, Request $request) {
        $item = \DB::table('sam_item')->where('id', $request->item_id)->first();
        $customer = Customer::find($request->customer);
        
        $unitPrice = floatval($request->unit_price) || 0;
        $quantity = floatval($request->quantity) || 0;
        $taxP = floatval($request->tax_p) || 0;
        $lineTotal = $unitPrice * $quantity;
        
        \DB::table('sam_proforma')->where('id', $id)->update([
            'pi_no' => $request->pi_no,
            'item_id' => $request->item_id,
            'item' => $item->item ?? '',
            'quantity' => $quantity,
            'unit' => $request->unit ?? $item->unit ?? '',
            'unit_price' => (string)$unitPrice,
            'tax_p' => (string)$taxP,
            'line_total' => (string)$lineTotal,
            'validity' => $request->validity ?? '30',
            'd_time' => $request->d_time ?? '',
            'term_of_payment' => $request->term_of_payment ?? 'full payment',
            'payment_percent' => $request->payment_percent ?? '0',
            'remark' => $request->remark ?? '',
            'p_date' => $request->p_date ?? now()->format('Y-m-d'),
            'customer' => $request->customer,
            'customer_name' => $customer->company_name ?? '',
            'transport' => $request->transport ?? 'No',
            't_unit_price' => floatval($request->t_unit_price) || 0,
            'transport_unit' => $request->transport_unit ?? '',
            't_tax_p' => floatval($request->t_tax_p) || 0,
            't_km' => floatval($request->t_km) || 0,
            'location' => $request->location ?? '',
        ]);
        
        return redirect()->route('proforma-invoices.index')->with('success', 'Proforma Invoice updated');
    })->name('proforma-invoices.update');

    Route::put('/proforma-invoices/{id}/check', function ($id) {
        \DB::table('sam_proforma')->where('id', $id)->update([
            'status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('proforma-invoices.index')->with('success', 'Proforma Invoice checked');
    })->name('proforma-invoices.check');

    Route::put('/proforma-invoices/{id}/approve', function ($id) {
        \DB::table('sam_proforma')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('proforma-invoices.index')->with('success', 'Proforma Invoice approved');
    })->name('proforma-invoices.approve');

    Route::put('/proforma-invoices/{id}/convert-to-so', function ($id) {
        $proforma = \DB::table('sam_proforma')->where('id', $id)->first();
        
        if (!$proforma || $proforma->status != 'Approved') {
            return redirect()->route('proforma-invoices.index')->with('error', 'Proforma must be approved to convert to SO');
        }
        
        // Redirigir a crear SO con el proforma prellenado
        return redirect()->route('sales-orders.create', ['pi_id' => $id])->with('success', 'Proforma ready to convert to Sales Order');
    })->name('proforma-invoices.convert-to-so');

    // ==================== STORE REQUISITION (SR) ====================
    Route::get('/store-requisitions', function (Request $request) {
        $query = \DB::table('sam_store_requisition')
            ->leftJoin('sam_item', 'sam_store_requisition.item', '=', 'sam_item.id')
            ->select('sam_store_requisition.*', 'sam_item.item as item_name')
            ->orderBy('sam_store_requisition.id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('sam_store_requisition.sr_no', 'like', "%{$request->search}%")
                  ->orWhere('sam_store_requisition.item_desc', 'like', "%{$request->search}%")
                  ->orWhere('sam_store_requisition.sr_from', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('sam_store_requisition.status', $request->status);
        }
        
        if ($request->pr_status) {
            $query->where('sam_store_requisition.pr_status', $request->pr_status);
        }
        
        $requisitions = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_store_requisition')->where('status', 'Pending')->count(),
            'checked' => \DB::table('sam_store_requisition')->where('status', 'Checked')->count(),
            'approved' => \DB::table('sam_store_requisition')->where('status', 'Approved')->count(),
            'pr_created' => \DB::table('sam_store_requisition')->where('pr_status', 'pr_created')->count(),
        ];
        
        return Inertia::render('StoreRequisition/Index', [
            'requisitions' => $requisitions,
            'filters' => $request->only(['search', 'status', 'pr_status']),
            'stats' => $stats,
        ]);
    })->name('store-requisitions.index');

    Route::get('/store-requisitions/create', function () {
        $nextSrNo = generateNextNumber('sam_store_requisition', 'sr_no', 'SR');
        
        return Inertia::render('StoreRequisition/Create', [
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
            'salesOrders' => SalesOrder::orderBy('id', 'desc')->limit(100)->get(),
            'nextSrNo' => $nextSrNo,
        ]);
    })->name('store-requisitions.create');

    Route::post('/store-requisitions', function (Request $request) {
        $item = \DB::table('sam_item')->where('id', $request->item)->first();
        
        // Auto-generate SR number if not provided
        $srNo = !empty($request->sr_no) ? $request->sr_no : generateNextNumber('sam_store_requisition', 'sr_no', 'SR');
        
        \DB::table('sam_store_requisition')->insert([
            'source' => $request->source ?? '',
            'sr_no' => $srNo,
            'item' => $request->item,
            'item_desc' => $item->item ?? $request->item_desc,
            'unit' => $item->unit ?? $request->unit,
            'sr_quantity' => $request->sr_quantity,
            'expected_delivery_date' => $request->expected_delivery_date ?? now()->format('Y-m-d'),
            'priority' => $request->priority ?? 'Normal',
            'urgency_reason' => $request->urgency_reason ?? '',
            'remark' => $request->remark ?? '',
            'used_for' => $request->used_for ?? '',
            'sr_from' => $request->sr_from ?? '',
            'sr_to' => $request->sr_to ?? '',
            'request_type' => $request->request_type ?? 'internal',
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'date_registered' => now()->format('Y-m-d'),
            'so_id' => $request->so_id ?? 0,
            'stock_status' => $request->stock_status ?? 'pending',
        ]);
        
        return redirect()->route('store-requisitions.index')->with('success', 'Store Requisition created');
    })->name('store-requisitions.store');

    Route::get('/store-requisitions/{id}/edit', function ($id) {
        $requisition = \DB::table('sam_store_requisition')->where('id', $id)->first();
        
        if (!$requisition) {
            abort(404, 'Store Requisition not found');
        }
        
        return Inertia::render('StoreRequisition/Edit', [
            'requisition' => $requisition,
            'items' => \DB::table('sam_item')
                ->where('status', 'Active')
                ->whereNotNull('item')
                ->where('item', '!=', '')
                ->orderBy('item')
                ->get(),
        ]);
    })->name('store-requisitions.edit');

    Route::put('/store-requisitions/{id}', function ($id, Request $request) {
        $item = \DB::table('sam_item')->where('id', $request->item)->first();
        
        \DB::table('sam_store_requisition')->where('id', $id)->update([
            'sr_no' => $request->sr_no,
            'item' => $request->item,
            'item_desc' => $item->item ?? $request->item_desc,
            'unit' => $item->unit ?? $request->unit,
            'sr_quantity' => $request->sr_quantity,
            'expected_delivery_date' => $request->expected_delivery_date ?? now()->format('Y-m-d'),
            'priority' => $request->priority ?? 'Normal',
            'urgency_reason' => $request->urgency_reason ?? '',
            'remark' => $request->remark ?? '',
            'used_for' => $request->used_for ?? '',
            'sr_from' => $request->sr_from ?? '',
            'sr_to' => $request->sr_to ?? '',
            'request_type' => $request->request_type ?? 'internal',
            'stock_status' => $request->stock_status ?? 'pending',
        ]);
        
        return redirect()->route('store-requisitions.index')->with('success', 'Store Requisition updated');
    })->name('store-requisitions.update');

    Route::put('/store-requisitions/{id}/check', function ($id) {
        \DB::table('sam_store_requisition')->where('id', $id)->update([
            'status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('store-requisitions.index')->with('success', 'Store Requisition checked');
    })->name('store-requisitions.check');

    Route::put('/store-requisitions/{id}/approve', function ($id) {
        \DB::table('sam_store_requisition')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('store-requisitions.index')->with('success', 'Store Requisition approved');
    })->name('store-requisitions.approve');

    Route::put('/store-requisitions/{id}/convert-to-pr', function ($id) {
        $sr = \DB::table('sam_store_requisition')->where('id', $id)->first();
        
        if (!$sr || $sr->status != 'Approved') {
            return redirect()->route('store-requisitions.index')->with('error', 'SR must be approved to convert to PR');
        }
        
        // Redirigir a crear PR con el SR prellenado
        return redirect()->route('purchase-requisitions.create', ['sr_id' => $id])->with('success', 'SR ready to convert to Purchase Requisition');
    })->name('store-requisitions.convert-to-pr');

    // ==================== CREDIT PAYMENTS ====================
    Route::get('/credit-payments', function (Request $request) {
        $query = \DB::table('sam_credit_payment')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('request_no', 'like', "%{$request->search}%")
                  ->orWhere('supplier_name', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $creditPayments = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_credit_payment')->where('status', 'Pending')->count(),
            'approved' => \DB::table('sam_credit_payment')->where('status', 'Approved')->count(),
            'totalAmount' => \DB::table('sam_credit_payment')->where('status', 'Approved')->sum('amount'),
        ];
        
        return Inertia::render('CreditPayments/Index', [
            'creditPayments' => $creditPayments,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    })->name('credit-payments.index');

    Route::get('/credit-payments/create', function () {
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        return Inertia::render('CreditPayments/Create', [
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
            'purchaseOrders' => PurchaseOrder::orderBy('id', 'desc')->limit(100)->get(),
            'banks' => $banks,
        ]);
    })->name('credit-payments.create');

    Route::post('/credit-payments', function (Request $request) {
        $supplier = $request->supplier_id ? Supplier::find($request->supplier_id) : null;
        $po = $request->po_id ? PurchaseOrder::find($request->po_id) : null;
        
        \DB::table('sam_credit_payment')->insert([
            'request_no' => $request->request_no,
            'request_date' => $request->request_date,
            'supplier_id' => $request->supplier_id,
            'supplier_name' => $supplier?->supplier_name,
            'po_id' => $request->po_id,
            'po_no' => $po?->po_no,
            'invoice_no' => $request->invoice_no,
            'invoice_date' => $request->invoice_date,
            'amount' => $request->amount,
            'vat_amount' => $request->vat_amount,
            'withholding_tax' => $request->withholding_tax,
            'net_amount' => $request->net_amount,
            'due_date' => $request->due_date,
            'payment_method' => $request->payment_method,
            'bank' => $request->bank,
            'remarks' => $request->remarks,
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('credit-payments.index')->with('success', 'Credit Payment Request created');
    })->name('credit-payments.store');

    Route::put('/credit-payments/{id}/check', function ($id) {
        \DB::table('sam_credit_payment')->where('id', $id)->update([
            'status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('credit-payments.index')->with('success', 'Request checked');
    })->name('credit-payments.check');

    Route::put('/credit-payments/{id}/approve', function ($id) {
        \DB::table('sam_credit_payment')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('credit-payments.index')->with('success', 'Request approved');
    })->name('credit-payments.approve');

    Route::put('/credit-payments/{id}/paid', function ($id) {
        \DB::table('sam_credit_payment')->where('id', $id)->update([
            'status' => 'Paid',
            'paid_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('credit-payments.index')->with('success', 'Request marked as paid');
    })->name('credit-payments.paid');

    // ==================== GOODS RECEIVE (GRN) ====================
    Route::get('/goods-receive', function (Request $request) {
        $query = \DB::table('sam_goods_receive')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('grn_no', 'like', "%{$request->search}%")
                  ->orWhere('supplier_name', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $goodsReceives = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_goods_receive')->where('status', 'Pending')->count(),
            'accepted' => \DB::table('sam_goods_receive')->where('status', 'Accepted')->count(),
            'totalItems' => \DB::table('sam_goods_receive')->sum('received_qty'),
        ];
        
        return Inertia::render('GoodsReceive/Index', [
            'goodsReceives' => $goodsReceives,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    })->name('goods-receive.index');

    Route::put('/goods-receive/{id}/inspect', function ($id) {
        \DB::table('sam_goods_receive')->where('id', $id)->update([
            'status' => 'Inspected',
            'inspected_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('goods-receive.index')->with('success', 'GRN inspected');
    })->name('goods-receive.inspect');

    Route::put('/goods-receive/{id}/accept', function ($id) {
        \DB::table('sam_goods_receive')->where('id', $id)->update([
            'status' => 'Accepted',
            'accepted_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('goods-receive.index')->with('success', 'GRN accepted');
    })->name('goods-receive.accept');

    Route::get('/goods-receive/create', function (Request $request) {
        $poData = null;
        if ($request->po_id) {
            $po = PurchaseOrder::find($request->po_id);
            if ($po) {
                $poData = [
                    'po_id'      => $po->id,
                    'po_no'      => $po->po_no,
                    'supplier'   => $po->supplier,
                    'item'       => $po->item,
                    'quantity'   => $po->quantity,
                    'unit'       => $po->unit,
                    'unit_price' => $po->unit_price,
                ];
            }
        }

        return Inertia::render('GoodsReceive/Create', [
            'suppliers'     => Supplier::orderBy('supplier_name')->get(),
            'purchaseOrders'=> PurchaseOrder::orderBy('id', 'desc')->limit(100)->get(),
            'items'         => \DB::table('sam_item')->orderBy('item')->get(),
            'poData'        => $poData,
        ]);
    })->name('goods-receive.create');

    Route::post('/goods-receive', function (Request $request) {
        $request->validate([
            'gr_no'      => 'required|string|max:30',
            'po_no'       => 'required|string|max:50',
            'supplier_id' => 'required|exists:sam_supplier,id',
            'item'        => 'required|string|max:20',
            'quantity'    => 'required|numeric|min:0',
            'received_qty'=> 'required|numeric|min:0',
        ]);

        // received_qty must not exceed ordered quantity from the PO
        $po = \DB::table('sam_purchase_order')
            ->where('po_no', $request->po_no)
            ->first();

        if ($po && (float)$request->received_qty > (float)$po->quantity) {
            return back()->withErrors([
                'received_qty' => 'Received quantity cannot exceed the ordered quantity (' . $po->quantity . ')',
            ]);
        }

        $supplier = Supplier::find($request->supplier_id);

        \DB::table('sam_goods_receive')->insert([
            'gr_no'            => $request->gr_no,
            'po_no'             => $request->po_no,
            'supplier_id'       => $request->supplier_id,
            'supplier_name'     => $supplier?->supplier_name ?? '',
            'item'              => $request->item,
            'item_id'           => $request->item_id ?? 0,
            'quantity'          => $request->quantity,
            'received_qty'      => $request->received_qty,
            'unit'              => $request->unit ?? '',
            'unit_price'        => $request->unit_price ?? 0,
            'total'             => ($request->received_qty ?? 0) * ($request->unit_price ?? 0),
            'remaning'          => ($request->quantity ?? 0) - ($request->received_qty ?? 0),
            'status'            => 'Pending',
            'grv_date'          => $request->grv_date ?? now()->format('Y-m-d'),
            'registered_by'     => auth()->user()->user_name ?? 'Admin',
            'registered_date'   => now()->format('Y-m-d'),
            'inspection_status' => '',
            'remark'            => $request->remark ?? '',
        ]);

        return redirect()->route('goods-receive.index')->with('success', 'Goods Receive created');
    })->name('goods-receive.store');

    // ==================== TRANSPORTER AGREEMENTS ====================
    Route::get('/transporter-agreements', function (Request $request) {
        $query = \DB::table('sam_transporter_agg')
            ->orderBy('id', 'desc');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('a_no', 'like', "%{$request->search}%")
                  ->orWhere('plate_no', 'like', "%{$request->search}%")
                  ->orWhere('owner', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $agreements = $query->paginate(20)->withQueryString();

        $stats = [
            'inactive' => \DB::table('sam_transporter_agg')->where('status', 'InActive')->count(),
            'approved' => \DB::table('sam_transporter_agg')->where('status', 'Approved')->count(),
            'active'   => \DB::table('sam_transporter_agg')->where('status', 'Active')->count(),
        ];

        return Inertia::render('TransporterAgreements/Index', [
            'agreements' => $agreements,
            'filters'    => $request->only(['search', 'status']),
            'stats'      => $stats,
        ]);
    })->name('transporter-agreements.index');

    Route::get('/transporter-agreements/create', function () {
        return Inertia::render('TransporterAgreements/Create', [
            'transporters' => Transporter::orderBy('company_name')->get(),
            'suppliers'    => Supplier::orderBy('supplier_name')->get(),
            'customers'    => Customer::orderBy('company_name')->get(),
            'items'        => \DB::table('sam_item')->orderBy('item')->get(),
        ]);
    })->name('transporter-agreements.create');

    Route::post('/transporter-agreements', function (Request $request) {
        $request->validate([
            'a_no'       => 'required|string|max:20',
            't_id'       => 'required|exists:sam_transporter,id',
            'plate_no'   => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
        ]);

        \DB::table('sam_transporter_agg')->insert([
            'a_no'            => $request->a_no,
            't_id'            => $request->t_id,
            'item_id'         => $request->item_id ?? 0,
            'item'            => $request->item ?? '',
            'unit_price'      => $request->unit_price,
            'tax_p'           => $request->tax_p ?? 0,
            'size'            => $request->size ?? '',
            'plate_no'        => $request->plate_no,
            'site'            => $request->site ?? 0,
            'owner'           => $request->owner ?? '',
            'supplier'        => $request->supplier ?? 0,
            'status'          => 'InActive',
            'agg_status'      => 'Pending',
            'registered_by'   => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
            'valid_from'      => $request->valid_from ?? '',
            'valid_to'        => $request->valid_to ?? '',
            'remark'          => $request->remark ?? '',
        ]);

        return redirect()->route('transporter-agreements.index')->with('success', 'Transporter Agreement created');
    })->name('transporter-agreements.store');

    Route::get('/transporter-agreements/{id}/edit', function ($id) {
        $agreement = \DB::table('sam_transporter_agg')->where('id', $id)->first();
        if (!$agreement) {
            abort(404);
        }
        return Inertia::render('TransporterAgreements/Edit', [
            'agreement'    => $agreement,
            'transporters' => Transporter::orderBy('company_name')->get(),
            'suppliers'    => Supplier::orderBy('supplier_name')->get(),
            'customers'    => Customer::orderBy('company_name')->get(),
            'items'        => \DB::table('sam_item')->orderBy('item')->get(),
        ]);
    })->name('transporter-agreements.edit');

    Route::put('/transporter-agreements/{id}', function (Request $request, $id) {
        $agreement = \DB::table('sam_transporter_agg')->where('id', $id)->first();
        if (!$agreement || $agreement->status === 'Active') {
            return back()->withErrors(['error' => 'Cannot edit an Active agreement']);
        }

        \DB::table('sam_transporter_agg')->where('id', $id)->update([
            'a_no'       => $request->a_no,
            'item_id'    => $request->item_id ?? 0,
            'item'       => $request->item ?? '',
            'unit_price' => $request->unit_price,
            'tax_p'      => $request->tax_p ?? 0,
            'size'       => $request->size ?? '',
            'plate_no'   => $request->plate_no,
            'site'       => $request->site ?? 0,
            'owner'      => $request->owner ?? '',
            'supplier'   => $request->supplier ?? 0,
            'valid_from' => $request->valid_from ?? '',
            'valid_to'   => $request->valid_to ?? '',
            'remark'     => $request->remark ?? '',
        ]);

        return redirect()->route('transporter-agreements.index')->with('success', 'Agreement updated');
    })->name('transporter-agreements.update');

    // Check: InActive → Checked
    Route::put('/transporter-agreements/{id}/check', function ($id) {
        \DB::table('sam_transporter_agg')->where('id', $id)->update([
            'agg_status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('transporter-agreements.index')->with('success', 'Agreement checked');
    })->name('transporter-agreements.check');

    // Approve: Checked → Approved
    Route::put('/transporter-agreements/{id}/approve', function ($id) {
        \DB::table('sam_transporter_agg')->where('id', $id)->update([
            'status'      => 'Approved',
            'agg_status'  => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('transporter-agreements.index')->with('success', 'Agreement approved');
    })->name('transporter-agreements.approve');

    // Activate: Approved → Active (once first delivery is made)
    Route::put('/transporter-agreements/{id}/activate', function ($id) {
        \DB::table('sam_transporter_agg')->where('id', $id)->update([
            'status' => 'Active',
        ]);
        return redirect()->route('transporter-agreements.index')->with('success', 'Agreement activated');
    })->name('transporter-agreements.activate');

    // Void
    Route::put('/transporter-agreements/{id}/void', function ($id) {
        \DB::table('sam_transporter_agg')->where('id', $id)->update([
            'status'     => 'Void',
            'agg_status' => 'Void',
        ]);
        return redirect()->route('transporter-agreements.index')->with('success', 'Agreement voided');
    })->name('transporter-agreements.void');

    Route::delete('/transporter-agreements/{id}', function ($id) {
        \DB::table('sam_transporter_agg')->where('id', $id)->delete();
        return redirect()->route('transporter-agreements.index')->with('success', 'Agreement deleted successfully');
    })->name('transporter-agreements.destroy');

    // ==================== TRANSPORTER PAYMENTS ====================
    Route::get('/transporter-payments', function (Request $request) {
        $query = \DB::table('sam_transporter_payment')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('request_no', 'like', "%{$request->search}%")
                  ->orWhere('transporter_name', 'like', "%{$request->search}%")
                  ->orWhere('plate_no', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $payments = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_transporter_payment')->where('status', 'Pending')->count(),
            'approved' => \DB::table('sam_transporter_payment')->where('status', 'Approved')->count(),
            'paid' => \DB::table('sam_transporter_payment')->where('status', 'Settled')->count(),
            'totalAmount' => \DB::table('sam_transporter_payment')->whereIn('status', ['Approved', 'Settled'])->sum('total'),
        ];
        
        return Inertia::render('TransporterPayments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    })->name('transporter-payments.index');

    Route::get('/transporter-payments/create', function () {
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        return Inertia::render('TransporterPayments/Create', [
            'transporters' => Transporter::orderBy('company_name')->get(),
            'deliveries' => Delivery::orderBy('id', 'desc')->limit(100)->get(['id', 'd_no', 'truck_plate_no']),
            'banks' => $banks,
        ]);
    })->name('transporter-payments.create');

    Route::post('/transporter-payments', function (Request $request) {
        $transporter = $request->transporter_id ? Transporter::find($request->transporter_id) : null;
        $delivery = $request->delivery_id ? Delivery::find($request->delivery_id) : null;
        
        \DB::table('sam_transporter_payment')->insert([
            'request_no' => $request->request_no,
            'request_date' => $request->request_date,
            'transporter_id' => $request->transporter_id,
            'transporter_name' => $transporter?->company_name,
            'delivery_id' => $request->delivery_id,
            'delivery_no' => $delivery?->d_no,
            'plate_no' => $request->plate_no,
            'driver_name' => $request->driver_name,
            'quantity' => $request->quantity,
            'rate' => $request->rate,
            'amount' => $request->amount,
            'withholding_tax' => $request->withholding_tax,
            'net_amount' => $request->net_amount,
            'payment_method' => $request->payment_method,
            'bank' => $request->bank,
            'remarks' => $request->remarks,
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
        ]);

        // Create corresponding payment_net record so the unpaid-transport report works
        $tpRecord = \DB::table('sam_transporter_payment')
            ->where('request_no', $request->request_no)
            ->latest('id')
            ->first();

        if ($tpRecord) {
            \DB::table('sam_transporter_payment_net')->insert([
                'tp_no'    => $tpRecord->request_no,   // matches tp.tp_no in the report JOIN
                'net_amount' => $request->net_amount ?? $request->amount ?? 0,
                'remaning' => $request->net_amount ?? $request->amount ?? 0,   // initially full amount is unpaid
            ]);
        }

        return redirect()->route('transporter-payments.index')->with('success', 'Payment Request created');
    })->name('transporter-payments.store');

    Route::get('/transporter-payments/{id}/edit', function ($id) {
        $payment = \DB::table('sam_transporter_payment')->where('id', $id)->first();
        if (!$payment) {
            return redirect()->route('transporter-payments.index')->with('error', 'Payment not found');
        }
        $banks = \DB::table('sam_bank')->orderBy('bank_name')->get();
        return Inertia::render('TransporterPayments/Edit', [
            'payment' => $payment,
            'transporters' => Transporter::orderBy('company_name')->get(),
            'deliveries' => Delivery::orderBy('id', 'desc')->limit(100)->get(['id', 'd_no', 'truck_plate_no']),
            'banks' => $banks,
        ]);
    })->name('transporter-payments.edit');

    Route::put('/transporter-payments/{id}', function ($id, Request $request) {
        $payment = \DB::table('sam_transporter_payment')->where('id', $id)->first();
        if (!$payment) {
            return redirect()->route('transporter-payments.index')->with('error', 'Payment not found');
        }
        if (!in_array($payment->status, ['Pending', 'Checked'])) {
            return redirect()->route('transporter-payments.index')->with('error', 'Only Pending or Checked payments can be edited');
        }
        $transporter = $request->transporter_id ? Transporter::find($request->transporter_id) : null;
        $delivery = $request->delivery_id ? Delivery::find($request->delivery_id) : null;

        \DB::table('sam_transporter_payment')->where('id', $id)->update([
            'request_no' => $request->request_no,
            'request_date' => $request->request_date,
            'transporter_id' => $request->transporter_id,
            'transporter_name' => $transporter?->company_name,
            'delivery_id' => $request->delivery_id,
            'delivery_no' => $delivery?->d_no,
            'plate_no' => $request->plate_no,
            'driver_name' => $request->driver_name,
            'quantity' => $request->quantity,
            'rate' => $request->rate,
            'amount' => $request->amount,
            'withholding_tax' => $request->withholding_tax,
            'net_amount' => $request->net_amount,
            'payment_method' => $request->payment_method,
            'bank' => $request->bank,
            'remarks' => $request->remarks,
        ]);
        return redirect()->route('transporter-payments.index')->with('success', 'Payment updated');
    })->name('transporter-payments.update');

    Route::put('/transporter-payments/{id}/check', function ($id) {
        \DB::table('sam_transporter_payment')->where('id', $id)->update([
            'status' => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('transporter-payments.index')->with('success', 'Payment checked');
    })->name('transporter-payments.check');

    Route::put('/transporter-payments/{id}/approve', function ($id) {
        \DB::table('sam_transporter_payment')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('transporter-payments.index')->with('success', 'Payment approved');
    })->name('transporter-payments.approve');

    Route::put('/transporter-payments/{id}/paid', function ($id) {
        \DB::table('sam_transporter_payment')->where('id', $id)->update([
            'status'    => 'Settled',
            'paid_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('transporter-payments.index')->with('success', 'Payment settled');
    })->name('transporter-payments.paid');

    // ==================== PAYMENT REFUNDS ====================
    Route::get('/payment-refunds', function (Request $request) {
        $query = \DB::table('sam_payment_refund')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('refund_no', 'like', "%{$request->search}%")
                  ->orWhere('payee_name', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $refunds = $query->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => \DB::table('sam_payment_refund')->where('status', 'Pending')->count(),
            'approved' => \DB::table('sam_payment_refund')->where('status', 'Approved')->count(),
            'totalAmount' => \DB::table('sam_payment_refund')->whereIn('status', ['Approved', 'Processed'])->sum('amount'),
        ];
        
        return Inertia::render('PaymentRefunds/Index', [
            'refunds' => $refunds,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    })->name('payment-refunds.index');

    Route::get('/payment-refunds/create', function () {
        return Inertia::render('PaymentRefunds/Create', [
            'payments' => Payment::orderBy('id', 'desc')->limit(100)->get(['id', 'p_no', 'pay_to', 'net_amount']),
        ]);
    })->name('payment-refunds.create');

    Route::post('/payment-refunds', function (Request $request) {
        $payment = $request->payment_id ? Payment::find($request->payment_id) : null;
        
        \DB::table('sam_payment_refund')->insert([
            'refund_no' => $request->refund_no,
            'refund_date' => $request->refund_date,
            'payment_id' => $request->payment_id,
            'original_payment_no' => $payment?->p_no,
            'payee_name' => $request->payee_name ?? $payment?->pay_to,
            'reason' => $request->reason,
            'amount' => $request->amount,
            'status' => 'Pending',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('payment-refunds.index')->with('success', 'Refund request created');
    })->name('payment-refunds.store');

    Route::put('/payment-refunds/{id}/approve', function ($id) {
        \DB::table('sam_payment_refund')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('payment-refunds.index')->with('success', 'Refund approved');
    })->name('payment-refunds.approve');

    Route::put('/payment-refunds/{id}/process', function ($id) {
        \DB::table('sam_payment_refund')->where('id', $id)->update([
            'status' => 'Processed',
            'processed_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('payment-refunds.index')->with('success', 'Refund processed');
    })->name('payment-refunds.process');

    // ==================== ITEMS API ====================
    Route::post('/api/items', function (Request $request) {
        $validated = $request->validate([
            'item' => 'required|string|max:200',
            'item_name' => 'nullable|string|max:200',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:20',
            'item_type' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        // Check if item already exists
        $existing = \DB::table('sam_item')
            ->where('item', $validated['item'])
            ->orWhere(function($q) use ($validated) {
                if (!empty($validated['item_name'])) {
                    $q->where('item_name', $validated['item_name']);
                }
            })
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Item already exists',
                'item' => $existing
            ], 422);
        }

        $itemId = \DB::table('sam_item')->insertGetId([
            'item' => $validated['item'],
            'item_name' => $validated['item_name'] ?? $validated['item'],
            'category' => $validated['category'] ?? '',
            'unit' => $validated['unit'] ?? 'M3',
            'item_type' => $validated['item_type'] ?? 'sales',
            'description' => $validated['description'] ?? '',
            'status' => 'Active',
            'registered_by' => auth()->user()->user_name ?? 'Admin',
            'registered_date' => now()->format('Y-m-d'),
        ]);

        $newItem = \DB::table('sam_item')->where('id', $itemId)->first();
        
        return response()->json([
            'success' => true,
            'item' => $newItem
        ]);
    })->name('api.items.store');

    // Logout
    Route::post('/logout', function (Request $request) {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
