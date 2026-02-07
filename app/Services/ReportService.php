<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Reporte de entregas por período
     */
    public function getDeliveryReport(array $filters = [])
    {
        $query = Delivery::with(['customer', 'transporter'])
            ->where('status', 'Done');
        
        if (isset($filters['date_from'])) {
            $query->where('issue_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('issue_date', '<=', $filters['date_to']);
        }
        
        if (isset($filters['customer_id'])) {
            $query->where('project', $filters['customer_id']);
        }
        
        return [
            'total_deliveries' => $query->count(),
            'total_quantity' => $query->sum('quantity'),
            'total_amount' => $query->sum('total'),
            'deliveries' => $query->orderBy('issue_date', 'desc')->get(),
        ];
    }

    /**
     * Reporte financiero por período
     */
    public function getFinancialReport(array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth();
        
        $sales = SalesOrder::whereBetween('e_date', [$dateFrom, $dateTo])
            ->where('status', 'Approved')
            ->selectRaw('SUM(total) as total_sales, SUM(remaning) as total_pending')
            ->first();
        
        $payments = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->where('status', 'Settled')
            ->selectRaw('SUM(net_amount) as total_payments')
            ->first();
        
        return [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'sales' => [
                'total' => $sales->total_sales ?? 0,
                'pending' => $sales->total_pending ?? 0,
                'collected' => ($sales->total_sales ?? 0) - ($sales->total_pending ?? 0),
            ],
            'payments' => [
                'total' => $payments->total_payments ?? 0,
            ],
        ];
    }

    /**
     * Dashboard - Métricas principales
     */
    public function getDashboardMetrics()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            'today' => [
                'deliveries' => Delivery::whereDate('issue_date', $today)
                    ->where('status', 'Done')
                    ->count(),
                'pending_deliveries' => Delivery::where('status', 'Pending')->count(),
            ],
            'this_month' => [
                'sales' => SalesOrder::where('e_date', '>=', $thisMonth)
                    ->where('status', 'Approved')
                    ->sum('total'),
                'deliveries' => Delivery::where('issue_date', '>=', $thisMonth)
                    ->where('status', 'Done')
                    ->count(),
                'payments' => Payment::where('payment_date', '>=', $thisMonth)
                    ->where('status', 'Settled')
                    ->sum('net_amount'),
            ],
            'pending' => [
                'sales_orders' => SalesOrder::where('status', 'Pending')->count(),
                'payments' => Payment::where('status', 'Pending')->count(),
            ],
        ];
    }

    /**
     * Reporte de créditos pendientes por cliente
     */
    public function getCustomerCreditsReport()
    {
        return SalesOrder::where('remaning', '>', 0)
            ->where('status', 'Approved')
            ->with('customer')
            ->selectRaw('
                customer,
                SUM(remaning) as total_credit,
                COUNT(*) as total_orders
            ')
            ->groupBy('customer')
            ->get()
            ->map(function ($item) {
                return [
                    'customer' => $item->customer,
                    'customer_name' => $item->customer ? $item->customer->company_name : 'N/A',
                    'total_credit' => $item->total_credit,
                    'total_orders' => $item->total_orders,
                ];
            });
    }
}
