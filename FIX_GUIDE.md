# FIX GUIDE — samaria-erp

All issues found by the test against `business_flows.md`, ordered by priority.
Every section includes the exact file, the current code, and the replacement code.

---

## Table of Contents

1. [Priority 1 — Mass Assignment (CRITICAL Security)](#priority-1--mass-assignment-critical-security)
2. [Priority 2 — Missing Status on Sales Order & Purchase Order Creation](#priority-2--missing-status-on-sales-order--purchase-order-creation)
3. [Priority 3 — GIT Type Hardcoded as 'purchase'](#priority-3--git-type-hardcoded-as-purchase)
4. [Priority 4 — Missing Transporter Agreement Routes](#priority-4--missing-transporter-agreement-routes)
5. [Priority 5 — Missing GRV (Goods Receive) Create Route](#priority-5--missing-grv-goods-receive-create-route)
6. [Priority 6 — Missing Purchase Order Check / Approve Routes](#priority-6--missing-purchase-order-check--approve-routes)
7. [Priority 7 — Add "Checked" Step to Approvals Page Endpoints](#priority-7--add-checked-step-to-approvals-page-endpoints)
8. [Priority 8 — Business Validations (5 rules from §7)](#priority-8--business-validations-5-rules-from-7)
9. [Priority 9 — Transporter Payment: Status Naming & payment_net Records](#priority-9--transporter-payment-status-naming--payment_net-records)
10. [Priority 10 — Customer Model: withholding Cast Mismatch](#priority-10--customer-model-withholding-cast-mismatch)
11. [Priority 11 — Collections: Decrement Sales Order `remaning`](#priority-11--collections-decrement-sales-order-remaning)

---

---

## Priority 1 — Mass Assignment (CRITICAL Security)

**Problem:**  Four `update` routes pass `$request->all()` directly into the model.
Any authenticated user can set `status`, `approved_by`, or any other column
through the edit form, completely bypassing the approval workflow.

**File:** `routes/web.php`

---

### 1a — Delivery Update (line 408-411)

**Current code:**
```php
    Route::put('/deliveries/{delivery}', function (Request $request, Delivery $delivery) {
        $delivery->update($request->all());
        return redirect()->route('deliveries.index')->with('success', 'Delivery updated successfully');
    })->name('deliveries.update');
```

**Replace with:**
```php
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
```

---

### 1b — Payment Update (line 492-495)

**Current code:**
```php
    Route::put('/payments/{payment}', function (Request $request, Payment $payment) {
        $payment->update($request->all());
        return redirect()->route('payments.index')->with('success', 'Payment updated successfully');
    })->name('payments.update');
```

**Replace with:**
```php
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
```

---

### 1c — Sales Order Update (line 588-591)

**Current code:**
```php
    Route::put('/sales-orders/{salesOrder}', function (Request $request, SalesOrder $salesOrder) {
        $salesOrder->update($request->all());
        return redirect()->route('sales-orders.index')->with('success', 'Sales Order updated successfully');
    })->name('sales-orders.update');
```

**Replace with:**
```php
    Route::put('/sales-orders/{salesOrder}', function (Request $request, SalesOrder $salesOrder) {
        // Block updates once the order has left Pending
        if ($salesOrder->status !== 'Pending') {
            return back()->withErrors(['error' => 'Cannot edit a Sales Order that is no longer Pending']);
        }

        $validated = $request->validate([
            'customer'   => 'sometimes|string',
            'item'       => 'sometimes|string|max:20',
            'unit'       => 'sometimes|string|max:20',
            'quantity'   => 'sometimes|numeric|min:0',
            'unit_price' => 'sometimes|numeric|min:0',
            'total'      => 'sometimes|numeric|min:0',
            'remark'     => 'nullable|string',
            'e_date'     => 'nullable|date',
        ]);

        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $qty   = $validated['quantity']   ?? $salesOrder->quantity;
            $price = $validated['unit_price'] ?? $salesOrder->unit_price;
            $validated['total']    = $qty * $price;
            $validated['remaning'] = $qty * $price;   // reset remaining to new total
        }

        $salesOrder->update($validated);
        return redirect()->route('sales-orders.index')->with('success', 'Sales Order updated successfully');
    })->name('sales-orders.update');
```

---

### 1d — Purchase Order Update (line 676-679)

**Current code:**
```php
    Route::put('/purchase-orders/{purchaseOrder}', function (Request $request, PurchaseOrder $purchaseOrder) {
        $purchaseOrder->update($request->all());
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order updated successfully');
    })->name('purchase-orders.update');
```

**Replace with:**
```php
    Route::put('/purchase-orders/{purchaseOrder}', function (Request $request, PurchaseOrder $purchaseOrder) {
        if ($purchaseOrder->status !== 'Pending') {
            return back()->withErrors(['error' => 'Cannot edit a Purchase Order that is no longer Pending']);
        }

        $validated = $request->validate([
            'supplier'   => 'sometimes|string',
            'item'       => 'sometimes|string|max:20',
            'unit'       => 'sometimes|string|max:20',
            'quantity'   => 'sometimes|numeric|min:0',
            'unit_price' => 'sometimes|numeric|min:0',
            'total'      => 'sometimes|numeric|min:0',
            'remark'     => 'nullable|string',
        ]);

        if (isset($validated['quantity']) || isset($validated['unit_price'])) {
            $qty   = $validated['quantity']   ?? $purchaseOrder->quantity;
            $price = $validated['unit_price'] ?? $purchaseOrder->unit_price;
            $validated['total'] = $qty * $price;
        }

        $purchaseOrder->update($validated);
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order updated successfully');
    })->name('purchase-orders.update');
```

---
---

## Priority 2 — Missing Status on Sales Order & Purchase Order Creation

**Problem:** Neither the Sales Order nor the Purchase Order creation route sets
`status = 'Pending'`. The value will be whatever the frontend sends (or `null`),
which means the approval workflow has no defined starting point.

**File:** `routes/web.php`

---

### 2a — Sales Order Create (line 550-579)

Add this single line **after** line 552 (`$data['registered_by'] = ...`):

```php
        $data['status'] = 'Pending';
```

Full context after the fix:
```php
    Route::post('/sales-orders', function (Request $request) {
        $data = $request->all();
        $data['registered_by'] = auth()->user()->user_name ?? 'Admin';
        $data['status']        = 'Pending';                          // <-- ADD THIS
        $data['from_dep']      = $data['from_dep'] ?? '';
        // ... rest unchanged
```

---

### 2b — Purchase Order Create (line 645-667)

Add this single line **after** line 647 (`$data['registered_by'] = ...`):

```php
        $data['status'] = 'Pending';
```

Full context after the fix:
```php
    Route::post('/purchase-orders', function (Request $request) {
        $data = $request->all();
        $data['registered_by'] = auth()->user()->user_name ?? 'Admin';
        $data['status']        = 'Pending';                          // <-- ADD THIS
        $data['e_date']        = $data['e_date'] ?? '';
        // ... rest unchanged
```

---
---

## Priority 3 — GIT Type Hardcoded as 'purchase'

**Problem:** `git_type` is hardcoded to `'purchase'` on line 2775.
When a GIT is created for a customer delivery (outbound), this value is wrong.
The GIT create form already receives both `purchaseOrders` and `customers`
(line 2763-2765), so the type should be dynamic based on context.

**File:** `routes/web.php`

### 3a — Make git_type dynamic (line 2771-2806)

Change line 2775 from:
```php
            'git_type' => 'purchase',
```

To:
```php
            'git_type' => $request->git_type ?? 'purchase',
```

### 3b — Link GIT to Sales Order when present (line 2777)

Change line 2777 from:
```php
            'so_id' => 0,
```

To:
```php
            'so_id' => $request->so_id ?? 0,
```

> **Frontend note:** The `GoodsInTransit/Create.vue` component must also be updated
> to include a `git_type` select (`purchase` / `sales`) and a `so_id` field that
> is shown when the type is `sales`. The `salesOrders` list already needs to be
> passed from the route — add `'salesOrders' => SalesOrder::orderBy('id','desc')->limit(100)->get()`
> to the create route data (line 2762-2768).

---
---

## Priority 4 — Missing Transporter Agreement Routes

**Problem:** The `TransporterAgreement` model exists with all fields and
relationships defined, but there are **zero routes** to create, list, edit, or
approve agreements. Users cannot manage transport contracts at all.

**File:** `routes/web.php`

**Action:** Insert the following block **before** the `// TRANSPORTER PAYMENTS` section
(before line 3662):

```php
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
```

> **Frontend note:** A `TransporterAgreements/Index.vue`, `Create.vue`, and `Edit.vue`
> must also be created. Follow the same pattern used by `SupplierAgreements/`.
> Add a sidebar link to `/transporter-agreements`.

---
---

## Priority 5 — Missing GRV (Goods Receive) Create Route

**Problem:** Only list, inspect, and accept routes exist for Goods Receive.
There is no way to register a new GRV from the UI.

**File:** `routes/web.php`

**Action:** Insert the following two routes **after** the goods-receive index route
(after line 3644, before the inspect route):

```php
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
            'grn_no'      => 'required|string|max:30',
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
            'grn_no'            => $request->grn_no,
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
```

> **Frontend note:** Create a `GoodsReceive/Create.vue`. When a `po_id` query
> parameter is present the form auto-fills supplier, item, quantity, price.

---
---

## Priority 6 — Missing Purchase Order Check / Approve Routes

**Problem:** Purchase Orders only have CRUD. The documented flow requires
`Pending → Checked → Approved (Confirmed)`.  The model already has
`checked_by` and `approved_by` in `$fillable`.

**File:** `routes/web.php`

**Action:** Insert the following **after** the purchase-orders destroy route (after line 684):

```php
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
```

---
---

## Priority 7 — Add "Checked" Step to Approvals Page Endpoints

**Problem:** The Approvals page endpoints (lines 2327-2385) go directly
`Pending → Approved / Done`. The documented flow requires a `Checked` intermediate
step for Sales Orders, Deliveries, Payments, and Customers.

**File:** `routes/web.php`

### 7a — Add four new "check" endpoints

Insert these **before** the existing approve/reject block (before line 2327):

```php
    // --- Check endpoints (Level 1: Supervisor) ---
    Route::post('/api/approvals/delivery/{id}/check', function ($id) {
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
        $order = SalesOrder::findOrFail($id);
        if ($order->status !== 'Pending') {
            return response()->json(['error' => 'Only Pending sales orders can be checked'], 422);
        }
        $order->update([
            'status'     => 'Checked',
            'checked_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });

    Route::post('/api/approvals/customer/{id}/check', function ($id) {
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
```

### 7b — Guard the existing approve endpoints to require "Checked" first

Replace lines 2327-2334 (delivery approve):
```php
    Route::post('/api/approvals/delivery/{id}/approve', function ($id) {
        $delivery = Delivery::findOrFail($id);
        $delivery->update([
            'status' => 'Done',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });
```

With:
```php
    Route::post('/api/approvals/delivery/{id}/approve', function ($id) {
        $delivery = Delivery::findOrFail($id);
        if ($delivery->status !== 'Checked') {
            return response()->json(['error' => 'Delivery must be Checked before approval'], 422);
        }
        $delivery->update([
            'status'      => 'Done',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });
```

Replace lines 2342-2349 (payment approve):
```php
    Route::post('/api/approvals/payment/{id}/approve', function ($id) {
        $payment = Payment::findOrFail($id);
        $payment->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });
```

With:
```php
    Route::post('/api/approvals/payment/{id}/approve', function ($id) {
        $payment = Payment::findOrFail($id);
        if ($payment->status !== 'Checked') {
            return response()->json(['error' => 'Payment must be Checked before approval'], 422);
        }
        $payment->update([
            'status'      => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });
```

Replace lines 2357-2364 (sales order approve):
```php
    Route::post('/api/approvals/salesOrder/{id}/approve', function ($id) {
        $order = SalesOrder::findOrFail($id);
        $order->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });
```

With:
```php
    Route::post('/api/approvals/salesOrder/{id}/approve', function ($id) {
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
```

Replace lines 2372-2379 (customer approve):
```php
    Route::post('/api/approvals/customer/{id}/approve', function ($id) {
        $customer = Customer::findOrFail($id);
        $customer->update([
            'status' => 'Active',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });
```

With:
```php
    Route::post('/api/approvals/customer/{id}/approve', function ($id) {
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
```

### 7c — Update the Approvals page data

Replace lines 2302-2324 to also fetch `Checked` records as a second tab:

```php
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
```

> **Frontend note:** Update `Approvals/Index.vue` to render two tabs:
> **"To Check"** (calls the `/check` endpoints) and **"To Approve"**
> (calls the existing `/approve` endpoints). Each row shows the appropriate
> action button based on which tab it appears in.

---
---

## Priority 8 — Business Validations (5 rules from §7)

**File:** `routes/web.php`

---

### 8a — "No delivery without an approved Sales Order"

**Location:** Delivery create route (line 361-399)

Add this guard **after** the `$validated = $request->validate(...)` block (after line 367)
and **before** `$data = $request->all()`:

```php
        // Validate: Sales Order must exist and be Approved (only for customer deliveries)
        if (!empty($request->siv_id)) {
            $so = SalesOrder::find($request->siv_id);
            if (!$so || $so->status !== 'Approved') {
                return back()->withErrors([
                    'siv_id' => 'The linked Sales Order must exist and be Approved before creating a delivery',
                ]);
            }
        }
```

---

### 8b — "Delivered quantity cannot exceed ordered quantity"

**Location:** Same delivery create route, after the SO guard above.

```php
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
```

---

### 8c — "No payment approval without confirmed deliveries"

**Location:** Payment approve endpoint (lines 2342-2349, after the Priority 7 fix)

Add this check **before** the `$payment->update(...)` call in the payment approve route:

```php
    Route::post('/api/approvals/payment/{id}/approve', function ($id) {
        $payment = Payment::findOrFail($id);
        if ($payment->status !== 'Checked') {
            return response()->json(['error' => 'Payment must be Checked before approval'], 422);
        }

        // For PO-linked payments, verify at least one delivery is Done
        if ($payment->p_type === 'po' && !empty($payment->po_no)) {
            $confirmedDelivery = Delivery::where('status', 'Done')
                ->where('pr_id', $payment->po_no)  // or link through PO
                ->exists();
            // Only block if there are deliveries expected but none confirmed
            $hasDeliveries = Delivery::where('pr_id', $payment->po_no)->exists();
            if ($hasDeliveries && !$confirmedDelivery) {
                return response()->json(['error' => 'At least one delivery must be confirmed (Done) before payment can be approved'], 422);
            }
        }

        $payment->update([
            'status'      => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return response()->json(['success' => true]);
    });
```

---

### 8d — "No payment settlement without prior approval"

**Location:** Payment update route (after Priority 1 fix for line 492).
The fix in Priority 1 already prevents `status` from being set through the
update form. Additionally, add a guard to the existing `settle` API route
in `PaymentController.php` (line 133):

**File:** `app/Http/Controllers/PaymentController.php`

Replace the settle method (lines 133-146):
```php
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
```

With:
```php
    public function settle(Request $request, Payment $payment): JsonResponse
    {
        $this->authorize('settle payments');

        if ($payment->status !== 'Approved') {
            return response()->json(['error' => 'Payment must be Approved before it can be settled'], 422);
        }

        $validated = $request->validate([
            'payment_date'  => 'required|date',
            'payment_ref_no'=> 'nullable|string|max:50',
            'cheque_no'     => 'nullable|string|max:100',
        ]);

        $payment = $this->paymentService->settlePayment($payment->id, $validated);

        return response()->json($payment);
    }
```

---

### 8e — "Price cannot change after approval"

This is already handled by Priority 1 fixes 1c and 1d: the Sales Order and
Purchase Order update routes now block edits when `status !== 'Pending'`.
No additional change is needed.

---
---

## Priority 9 — Transporter Payment: Status Naming & payment_net Records

**Problem 1:** Web route sets status `'Paid'` (line 3750), API sets `'Settled'`.
The unpaid-transport report only filters by `'Approved'`, but downstream queries
elsewhere may depend on consistent naming.

**Problem 2:** The unpaid-transport report JOINs `sam_transporter_payment_net`
on `tp_no`. The web create route inserts into `sam_transporter_payment` but
never creates a matching row in `sam_transporter_payment_net`, so the report
returns zero data for payments created through the current UI.

**File:** `routes/web.php`

### 9a — Standardize terminal status to 'Settled'

Change line 3748-3754 from:
```php
    Route::put('/transporter-payments/{id}/paid', function ($id) {
        \DB::table('sam_transporter_payment')->where('id', $id)->update([
            'status' => 'Paid',
            'paid_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('transporter-payments.index')->with('success', 'Payment marked as paid');
    })->name('transporter-payments.paid');
```

To:
```php
    Route::put('/transporter-payments/{id}/paid', function ($id) {
        \DB::table('sam_transporter_payment')->where('id', $id)->update([
            'status'    => 'Settled',
            'paid_date' => now()->format('Y-m-d'),
        ]);
        return redirect()->route('transporter-payments.index')->with('success', 'Payment settled');
    })->name('transporter-payments.paid');
```

Update the stats query on line 3684 from:
```php
            'paid' => \DB::table('sam_transporter_payment')->where('status', 'Paid')->count(),
```
To:
```php
            'paid' => \DB::table('sam_transporter_payment')->where('status', 'Settled')->count(),
```

### 9b — Create a `payment_net` record on transporter payment creation

In the transporter payment store route (lines 3704-3729), add the following
**after** the `insert` call and **before** the `return redirect()`:

```php
        // Create corresponding payment_net record so the unpaid-transport report works
        $tpRecord = \DB::table('sam_transporter_payment')
            ->where('request_no', $request->request_no)
            ->latest('id')
            ->first();

        if ($tpRecord) {
            \DB::table('sam_transporter_payment_net')->insert([
                'tp_no'    => $tpRecord->request_no,   // matches tp.tp_no in the report JOIN
                'amount'   => $request->amount ?? 0,
                'remaning' => $request->amount ?? 0,   // initially full amount is unpaid
            ]);
        }
```

---
---

## Priority 10 — Customer Model: withholding Cast Mismatch

**Problem:** `Customer.php` line 30 casts `withholding` to `boolean`.
The database and the create route store it as the string `'Yes'` / `'No'`.
A boolean cast will turn `'No'` into `true` (non-empty string), which silently
breaks any withholding logic.

**File:** `app/Models/Customer.php`

Replace lines 29-31:
```php
    protected $casts = [
        'withholding' => 'boolean',
    ];
```

With:
```php
    protected $casts = [
        // withholding is stored as 'Yes' / 'No' string in the database — do not cast to boolean
    ];
```

(Remove the `$casts` array entirely, or leave it empty if other casts are added later.)

---
---

## Priority 11 — Collections: Decrement Sales Order `remaning`

**Problem:** When a collection is approved, the customer's outstanding balance
(`sam_sales_order.remaning`) is never decremented. The `uncollected-sales-orders`
report will keep showing the full amount forever.

**File:** `routes/web.php`

Replace lines 2713-2719 (collections approve):
```php
    Route::put('/collections/{id}/approve', function ($id) {
        \DB::table('sam_collection')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);
        return redirect()->route('collections.index')->with('success', 'Collection approved');
    })->name('collections.approve');
```

With:
```php
    Route::put('/collections/{id}/approve', function ($id) {
        $collection = \DB::table('sam_collection')->where('id', $id)->first();
        if (!$collection) {
            abort(404);
        }

        \DB::table('sam_collection')->where('id', $id)->update([
            'status'      => 'Approved',
            'approved_by' => auth()->user()->user_name,
        ]);

        // Decrement remaning on the linked Sales Order(s) for this customer
        if (!empty($collection->customer_id)) {
            $amount = (float)$collection->amount;

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

        return redirect()->route('collections.index')->with('success', 'Collection approved');
    })->name('collections.approve');
```

---
---

## Summary — What to Do First

| # | File | Lines affected | Effort |
|---|------|---------------|--------|
| 1 | `routes/web.php` | 408, 492, 588, 676 | Small — 4 route edits |
| 2 | `routes/web.php` | 550, 645 | Tiny — 1 line each |
| 3 | `routes/web.php` | 2775, 2777 | Tiny — 2 lines |
| 4 | `routes/web.php` | before 3662 | Medium — new route block + new Vue pages |
| 5 | `routes/web.php` | after 3644 | Medium — new route block + new Vue page |
| 6 | `routes/web.php` | after 684 | Small — 2 new route blocks |
| 7 | `routes/web.php` | 2302-2385 | Medium — new check endpoints + update existing |
| 8 | `routes/web.php` + `PaymentController` | 361, 2342, 133 | Small — add guards |
| 9 | `routes/web.php` | 3704, 3748, 3684 | Small — 3 edits |
| 10 | `app/Models/Customer.php` | 29-31 | Tiny — remove cast |
| 11 | `routes/web.php` | 2713-2719 | Small — expand approve route |
