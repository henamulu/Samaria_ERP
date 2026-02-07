<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sam_payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('p_no', 20)->nullable();
            $table->string('p_type', 50)->nullable();
            $table->string('po_no', 50)->nullable();
            $table->string('balance_type', 20)->nullable();
            $table->string('payment_status', 20)->default('Pending');
            $table->date('pdc_date')->nullable();
            $table->date('pdc_update_date')->nullable();
            $table->string('pdc_update_by', 250)->nullable();
            $table->string('account_no', 50)->nullable();
            $table->decimal('net_amount', 64, 2)->default(0);
            $table->string('bank', 100)->nullable();
            $table->string('branch', 200)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_ref_no', 50)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('cheque_no', 100)->nullable();
            $table->text('payment_description')->nullable();
            $table->string('internal_payment_request', 50)->nullable();
            $table->string('pay_to', 100)->nullable();
            $table->string('payment_handover', 100)->nullable();
            $table->string('registered_by', 100)->nullable();
            $table->string('checked_by', 100)->nullable();
            $table->string('approved_by', 100)->nullable();
            $table->date('date_registered')->nullable();
            $table->string('status', 50)->default('Pending'); // Pending, Approved, Settled, Void
            $table->string('item_desc', 500)->nullable();
            $table->decimal('advance_balance', 60, 4)->default(0);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('settlement_id', 20)->nullable();
            $table->string('advance_status', 20)->nullable();
            $table->string('le_id', 20)->nullable();
            $table->string('outstand', 20)->nullable();
            $table->date('outstand_date')->nullable();
            $table->string('withhold', 20)->nullable();
            $table->string('cr_status', 20)->nullable();
            $table->string('recon', 20)->nullable();
            $table->decimal('not_paid', 60, 2)->default(0);
            $table->string('refund_by', 500)->nullable();
            $table->string('refund_status', 20)->nullable();
            $table->string('rejection_status', 20)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index('supplier_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('p_no');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sam_payment');
    }
};
