<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bank Transfer Table
        if (!Schema::hasTable('sam_bank_transfer')) {
            Schema::create('sam_bank_transfer', function (Blueprint $table) {
                $table->id();
                $table->string('btr_no', 50);
                $table->date('transfer_date');
                $table->string('from_bank', 100)->nullable();
                $table->string('from_account', 50)->nullable();
                $table->string('to_bank', 100)->nullable();
                $table->string('to_account', 50)->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('transfer_type', 50)->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->string('cheque_no', 50)->nullable();
                $table->text('description')->nullable();
                $table->string('status', 20)->default('Pending');
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
                $table->string('checked_by', 50)->nullable();
                $table->string('approved_by', 50)->nullable();
            });
        }

        // Bank Reconciliation Table
        if (!Schema::hasTable('sam_bank_recon')) {
            Schema::create('sam_bank_recon', function (Blueprint $table) {
                $table->id();
                $table->string('br_no', 50);
                $table->string('bank_name', 100)->nullable();
                $table->integer('period_month')->nullable();
                $table->integer('period_year')->nullable();
                $table->decimal('bank_balance', 15, 2)->default(0);
                $table->decimal('book_balance', 15, 2)->default(0);
                $table->decimal('difference', 15, 2)->default(0);
                $table->decimal('outstanding_cheques', 15, 2)->default(0);
                $table->decimal('deposits_in_transit', 15, 2)->default(0);
                $table->decimal('bank_charges', 15, 2)->default(0);
                $table->text('remarks')->nullable();
                $table->string('status', 20)->default('Pending');
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
                $table->string('checked_by', 50)->nullable();
                $table->string('approved_by', 50)->nullable();
            });
        }

        // Collection Table
        if (!Schema::hasTable('sam_collection')) {
            Schema::create('sam_collection', function (Blueprint $table) {
                $table->id();
                $table->string('collection_no', 50);
                $table->date('collection_date');
                $table->string('type', 20)->nullable(); // sales, different
                $table->integer('customer_id')->nullable();
                $table->string('customer_name', 200)->nullable();
                $table->string('source', 200)->nullable();
                $table->string('collection_type', 50)->nullable(); // Cash, Cheque, Transfer
                $table->string('bank', 100)->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('cheque_no', 50)->nullable();
                $table->date('cheque_date')->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->date('deposit_date')->nullable();
                $table->text('description')->nullable();
                $table->string('status', 20)->default('Pending');
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
                $table->string('checked_by', 50)->nullable();
                $table->string('approved_by', 50)->nullable();
            });
        }

        // Goods In Transit Table
        if (!Schema::hasTable('sam_git')) {
            Schema::create('sam_git', function (Blueprint $table) {
                $table->id();
                $table->string('git_no', 50);
                $table->date('git_date');
                $table->integer('po_id')->nullable();
                $table->integer('supplier_id')->nullable();
                $table->string('supplier_name', 200)->nullable();
                $table->integer('customer_id')->nullable();
                $table->string('customer_name', 200)->nullable();
                $table->integer('item_id')->nullable();
                $table->string('item_name', 200)->nullable();
                $table->decimal('quantity', 15, 2)->default(0);
                $table->string('unit', 20)->nullable();
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->integer('transporter_id')->nullable();
                $table->string('transporter_name', 200)->nullable();
                $table->string('plate_no', 50)->nullable();
                $table->string('driver_name', 100)->nullable();
                $table->date('expected_arrival')->nullable();
                $table->string('origin', 200)->nullable();
                $table->string('destination', 200)->nullable();
                $table->text('remarks')->nullable();
                $table->string('status', 20)->default('In Transit');
                $table->date('delivery_date')->nullable();
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
            });
        }

        // Settlement Table
        if (!Schema::hasTable('sam_settlement')) {
            Schema::create('sam_settlement', function (Blueprint $table) {
                $table->id();
                $table->string('settlement_no', 50);
                $table->date('settlement_date');
                $table->string('type', 20)->nullable(); // POS, PCS, CRS, Advance, Transporter
                $table->integer('po_id')->nullable();
                $table->integer('recipient_id')->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->decimal('original_amount', 15, 2)->default(0);
                $table->decimal('settled_amount', 15, 2)->default(0);
                $table->decimal('balance', 15, 2)->default(0);
                $table->string('petty_cash_account', 50)->nullable();
                $table->string('bank', 100)->nullable();
                $table->text('remarks')->nullable();
                $table->string('status', 20)->default('Pending');
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
                $table->string('approved_by', 50)->nullable();
            });
        }

        // Credit Payment Table
        if (!Schema::hasTable('sam_credit_payment')) {
            Schema::create('sam_credit_payment', function (Blueprint $table) {
                $table->id();
                $table->string('request_no', 50);
                $table->date('request_date');
                $table->integer('supplier_id')->nullable();
                $table->string('supplier_name', 200)->nullable();
                $table->integer('po_id')->nullable();
                $table->string('po_no', 50)->nullable();
                $table->string('invoice_no', 50)->nullable();
                $table->date('invoice_date')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->decimal('vat_amount', 15, 2)->default(0);
                $table->decimal('withholding_tax', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2)->default(0);
                $table->date('due_date')->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->string('bank', 100)->nullable();
                $table->text('remarks')->nullable();
                $table->string('status', 20)->default('Pending');
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
                $table->string('checked_by', 50)->nullable();
                $table->string('approved_by', 50)->nullable();
                $table->date('paid_date')->nullable();
            });
        }

        // Goods Receive Table
        if (!Schema::hasTable('sam_goods_receive')) {
            Schema::create('sam_goods_receive', function (Blueprint $table) {
                $table->id();
                $table->string('grn_no', 50);
                $table->date('receive_date');
                $table->integer('supplier_id')->nullable();
                $table->string('supplier_name', 200)->nullable();
                $table->integer('po_id')->nullable();
                $table->string('po_no', 50)->nullable();
                $table->integer('item_id')->nullable();
                $table->string('item_name', 200)->nullable();
                $table->decimal('qty_received', 15, 2)->default(0);
                $table->decimal('qty_accepted', 15, 2)->default(0);
                $table->string('unit', 20)->nullable();
                $table->string('status', 20)->default('Pending');
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
                $table->string('inspected_by', 50)->nullable();
                $table->string('accepted_by', 50)->nullable();
            });
        }

        // Transporter Payment Table
        if (!Schema::hasTable('sam_transporter_payment')) {
            Schema::create('sam_transporter_payment', function (Blueprint $table) {
                $table->id();
                $table->string('request_no', 50);
                $table->date('request_date');
                $table->integer('transporter_id')->nullable();
                $table->string('transporter_name', 200)->nullable();
                $table->integer('delivery_id')->nullable();
                $table->string('delivery_no', 50)->nullable();
                $table->string('plate_no', 50)->nullable();
                $table->string('driver_name', 100)->nullable();
                $table->decimal('quantity', 15, 2)->default(0);
                $table->decimal('rate', 15, 2)->default(0);
                $table->decimal('amount', 15, 2)->default(0);
                $table->decimal('withholding_tax', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2)->default(0);
                $table->string('payment_method', 50)->nullable();
                $table->string('bank', 100)->nullable();
                $table->text('remarks')->nullable();
                $table->string('status', 20)->default('Pending');
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
                $table->string('checked_by', 50)->nullable();
                $table->string('approved_by', 50)->nullable();
                $table->date('paid_date')->nullable();
            });
        }

        // Payment Refund Table
        if (!Schema::hasTable('sam_payment_refund')) {
            Schema::create('sam_payment_refund', function (Blueprint $table) {
                $table->id();
                $table->string('refund_no', 50);
                $table->date('refund_date');
                $table->integer('payment_id')->nullable();
                $table->string('original_payment_no', 50)->nullable();
                $table->string('payee_name', 200)->nullable();
                $table->text('reason')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('status', 20)->default('Pending');
                $table->string('registered_by', 50)->nullable();
                $table->date('registered_date')->nullable();
                $table->string('approved_by', 50)->nullable();
                $table->date('processed_date')->nullable();
            });
        }

        // Bank Table (if not exists)
        if (!Schema::hasTable('sam_bank')) {
            Schema::create('sam_bank', function (Blueprint $table) {
                $table->id();
                $table->string('bank_name', 100);
                $table->string('account_no', 50)->nullable();
                $table->string('account_name', 200)->nullable();
                $table->string('branch', 200)->nullable();
                $table->string('status', 20)->default('Active');
            });

            // Insert some default banks
            \DB::table('sam_bank')->insert([
                ['bank_name' => 'Commercial Bank of Ethiopia', 'status' => 'Active'],
                ['bank_name' => 'Awash Bank', 'status' => 'Active'],
                ['bank_name' => 'Dashen Bank', 'status' => 'Active'],
                ['bank_name' => 'Bank of Abyssinia', 'status' => 'Active'],
                ['bank_name' => 'Oromia Bank', 'status' => 'Active'],
                ['bank_name' => 'Wegagen Bank', 'status' => 'Active'],
                ['bank_name' => 'United Bank', 'status' => 'Active'],
                ['bank_name' => 'Nib Bank', 'status' => 'Active'],
                ['bank_name' => 'Cooperative Bank of Oromia', 'status' => 'Active'],
                ['bank_name' => 'Zemen Bank', 'status' => 'Active'],
            ]);
        }

        // Item Table (if not exists)
        if (!Schema::hasTable('sam_item')) {
            Schema::create('sam_item', function (Blueprint $table) {
                $table->id();
                $table->string('item_name', 200);
                $table->string('item_code', 50)->nullable();
                $table->string('category', 100)->nullable();
                $table->string('unit', 20)->nullable();
                $table->string('status', 20)->default('Active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sam_bank_transfer');
        Schema::dropIfExists('sam_bank_recon');
        Schema::dropIfExists('sam_collection');
        Schema::dropIfExists('sam_git');
        Schema::dropIfExists('sam_settlement');
        Schema::dropIfExists('sam_credit_payment');
        Schema::dropIfExists('sam_goods_receive');
        Schema::dropIfExists('sam_transporter_payment');
        Schema::dropIfExists('sam_payment_refund');
        Schema::dropIfExists('sam_bank');
        Schema::dropIfExists('sam_item');
    }
};
