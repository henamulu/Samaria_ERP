<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransporterController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransporterPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas de autenticación (públicas)
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('user', [\App\Http\Controllers\AuthController::class, 'user'])->middleware('auth:sanctum');

// Rutas protegidas con autenticación
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Clientes
    Route::apiResource('customers', CustomerController::class);
    Route::post('customers/{customer}/approve', [CustomerController::class, 'approve']);
    
    // Proveedores
    Route::apiResource('suppliers', SupplierController::class);
    
    // Transportistas
    Route::apiResource('transporters', TransporterController::class);
    
    // Entregas
    Route::apiResource('deliveries', DeliveryController::class);
    Route::post('deliveries/{delivery}/approve', [DeliveryController::class, 'approve']);
    Route::post('deliveries/{delivery}/confirm', [DeliveryController::class, 'confirm']);
    Route::get('deliveries/pending/list', [DeliveryController::class, 'pending']);
    
    // Pagos
    Route::apiResource('payments', PaymentController::class);
    Route::post('payments/{payment}/approve', [PaymentController::class, 'approve']);
    Route::post('payments/{payment}/settle', [PaymentController::class, 'settle']);
    Route::get('payments/unpaid/transporters', [PaymentController::class, 'unpaidTransporters']);
    
    // Pagos a Transportistas
    Route::apiResource('transporter-payments', TransporterPaymentController::class);
    Route::post('transporter-payments/{transporterPayment}/approve', [TransporterPaymentController::class, 'approve']);
    Route::post('transporter-payments/{transporterPayment}/settle', [TransporterPaymentController::class, 'settle']);
    
    // Reportes
    Route::prefix('reports')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\ReportController::class, 'dashboard']);
        Route::get('deliveries', [\App\Http\Controllers\ReportController::class, 'deliveries']);
        Route::get('financial', [\App\Http\Controllers\ReportController::class, 'financial']);
        Route::get('customer-credits', [\App\Http\Controllers\ReportController::class, 'customerCredits']);
    });
    
});
