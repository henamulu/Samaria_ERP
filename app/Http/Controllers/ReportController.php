<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Dashboard - Métricas principales
     */
    public function dashboard(): JsonResponse
    {
        $this->authorize('view dashboard');
        
        $metrics = $this->reportService->getDashboardMetrics();
        
        return response()->json($metrics);
    }

    /**
     * Reporte de entregas
     */
    public function deliveries(Request $request): JsonResponse
    {
        $this->authorize('view reports');
        
        $filters = $request->only(['date_from', 'date_to', 'customer_id']);
        
        $report = $this->reportService->getDeliveryReport($filters);
        
        return response()->json($report);
    }

    /**
     * Reporte financiero
     */
    public function financial(Request $request): JsonResponse
    {
        $this->authorize('view reports');
        
        $filters = $request->only(['date_from', 'date_to']);
        
        $report = $this->reportService->getFinancialReport($filters);
        
        return response()->json($report);
    }

    /**
     * Reporte de créditos pendientes
     */
    public function customerCredits(): JsonResponse
    {
        $this->authorize('view reports');
        
        $credits = $this->reportService->getCustomerCreditsReport();
        
        return response()->json($credits);
    }
}
