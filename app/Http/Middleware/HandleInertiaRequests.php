<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\DB;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        // Calculate notification counts
        $notifications = [
            'pendingApprovals' => 0,
            'overdueCredits' => 0,
            'lowStock' => 0,
        ];
        
        if ($user) {
            // Count pending approvals (deliveries, payments, sales orders, customers)
            $notifications['pendingApprovals'] = 
                DB::table('sam_delivery')->where('status', 'Pending')->count() +
                DB::table('sam_payment')->where('status', 'Pending')->count() +
                DB::table('sam_sales_order')->where('status', 'Pending')->count() +
                DB::table('sam_customer')->where('status', 'Pending')->count();
            
            // Count overdue credits (SOs with remaning > 0 older than 30 days)
            $notifications['overdueCredits'] = DB::table('sam_sales_order')
                ->where('remaning', '>', 0)
                ->where('status', 'Approved')
                ->where('registered_date', '<', now()->subDays(30))
                ->count();
            
            // Count low stock items (stock_balance < threshold, threshold = 100 for now)
            // Note: sam_stock_balance uses stock_balance column, not balance
            $notifications['lowStock'] = DB::table('sam_stock_balance')
                ->where('status', 'Done')
                ->whereRaw('CAST(stock_balance AS DECIMAL(10,2)) < 100')
                ->count();
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'role' => $user?->role,
            ],
            'notifications' => $notifications,
        ];
    }
}
