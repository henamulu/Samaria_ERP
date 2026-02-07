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
        Schema::create('sam_sales_order', function (Blueprint $table) {
            $table->id();
            $table->string('so_no', 20)->nullable();
            $table->string('payment_type', 20)->nullable(); // Credit, Cash
            $table->string('from_dep', 500)->nullable();
            $table->string('to_dep', 500)->nullable();
            $table->unsignedBigInteger('customer')->nullable();
            $table->string('type', 20)->nullable();
            $table->string('item_id', 20)->nullable();
            $table->string('item', 20)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('quantity', 20, 2)->default(0);
            $table->decimal('unit_price', 60, 2)->default(0);
            $table->string('tax_p', 20)->nullable();
            $table->decimal('total', 60, 2)->default(0);
            $table->decimal('remaning', 60, 2)->default(0);
            $table->text('remark')->nullable();
            $table->string('location', 20)->nullable();
            $table->date('e_date')->nullable();
            $table->string('status', 20)->default('Pending'); // Pending, Checked, Approved, Done
            $table->string('registered_by', 20)->nullable();
            $table->date('registered_date')->nullable();
            $table->string('p_id', 20)->nullable(); // payment_id
            $table->string('checked_by', 500)->nullable();
            $table->string('approved_by', 500)->nullable();
            $table->string('p_doc', 20)->nullable();
            $table->string('p_doc_by', 500)->nullable();
            $table->date('p_doc_date')->nullable();
            $table->string('i_id', 20)->nullable();
            $table->string('pr_id', 20)->nullable();
            $table->string('pi_id', 20)->nullable();
            $table->decimal('t_km', 60, 2)->default(0);
            $table->string('t_tax_p', 20)->nullable();
            $table->string('transport_unit', 20)->nullable();
            $table->decimal('t_unit_price', 60, 2)->default(0);
            $table->string('transport', 20)->nullable();
            $table->string('invoice_type', 50)->nullable();
            $table->string('agg_id', 20)->nullable();
            $table->decimal('previous_price', 60, 2)->default(0);
            $table->string('d_status', 20)->nullable();
            $table->decimal('requested_price', 60, 2)->default(0);
            $table->string('r_by', 500)->nullable();
            $table->string('r_approved_by', 500)->nullable();
            $table->timestamps();
            
            $table->index('customer');
            $table->index('status');
            $table->index('so_no');
            $table->index('e_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sam_sales_order');
    }
};
