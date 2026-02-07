<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view customers');
        
        $query = Customer::query();
        
        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('tin_no', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->orderBy('company_name')->paginate($request->get('per_page', 15));
        
        return response()->json($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create customers');
        
        $validated = $request->validate([
            'customer_type' => 'required|in:company,ind',
            'company_name' => 'required_if:customer_type,company|string|max:500',
            'firstname' => 'required_if:customer_type,ind|string|max:500',
            'lastname' => 'required_if:customer_type,ind|string|max:500',
            'tin_no' => 'nullable|string|max:20',
            'withholding' => 'nullable|string|max:20',
            'phone_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:500',
            'contact_person' => 'nullable|string|max:500',
            'office_location' => 'nullable|string|max:500',
        ]);
        
        $validated['registered_by'] = auth()->user()->user_name ?? 'System';
        $validated['status'] = 'Active';
        
        $customer = Customer::create($validated);
        
        return response()->json($customer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('view customers');
        
        $customer->load(['deliveries', 'salesOrders']);
        
        return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $this->authorize('edit customers');
        
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:500',
            'firstname' => 'sometimes|string|max:500',
            'lastname' => 'sometimes|string|max:500',
            'tin_no' => 'nullable|string|max:20',
            'withholding' => 'nullable|string|max:20',
            'phone_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:500',
            'contact_person' => 'nullable|string|max:500',
            'office_location' => 'nullable|string|max:500',
            'status' => 'sometimes|in:Active,Deleted',
        ]);
        
        $customer->update($validated);
        
        return response()->json($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete customers');
        
        // Soft delete - cambiar status
        $customer->update(['status' => 'Deleted']);
        
        return response()->json(['message' => 'Customer deleted successfully']);
    }

    /**
     * Aprobar cliente
     */
    public function approve(Customer $customer): JsonResponse
    {
        $this->authorize('approve customers');
        
        $customer->update([
            'approved_by' => auth()->user()->user_name ?? 'System',
        ]);
        
        return response()->json($customer);
    }
}
