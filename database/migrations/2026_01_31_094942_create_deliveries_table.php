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
        Schema::create('sam_delivery', function (Blueprint $table) {
            $table->id();
            $table->string('d_no', 20)->nullable();
            $table->string('source', 20)->nullable(); // git, siv, etc
            $table->string('used_for', 20)->nullable(); // customer, internal
            $table->unsignedBigInteger('project')->nullable(); // customer_id
            $table->string('item', 20)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('quantity', 20, 2)->default(0);
            $table->decimal('unit_price', 20, 2)->default(0);
            $table->decimal('total', 20, 2)->default(0);
            $table->text('remark')->nullable();
            $table->string('type_of_asset', 100)->nullable();
            $table->date('issue_date')->nullable();
            $table->string('voucher_type', 100)->nullable();
            $table->string('driver_name', 500)->nullable();
            $table->string('truck_plate_no', 200)->nullable();
            $table->string('delivered_to', 500)->nullable();
            $table->string('status', 20)->default('Pending'); // Done, Pending
            $table->string('registered_by', 100)->nullable();
            $table->date('registered_date')->nullable();
            $table->string('checked_by', 500)->nullable();
            $table->string('approved_by', 500)->nullable();
            $table->string('siv_id', 20)->nullable();
            $table->string('git_id', 20)->nullable();
            $table->unsignedBigInteger('t_id')->nullable(); // transporter_id
            $table->unsignedBigInteger('p_id')->nullable(); // payment_id
            $table->decimal('t_qty', 60, 2)->default(0);
            $table->unsignedBigInteger('tp_id')->nullable(); // transporter_payment_id
            $table->string('delivery_no', 20)->nullable();
            $table->string('pr_id', 20)->nullable();
            $table->decimal('supplier_qty', 60, 2)->default(0);
            $table->string('daily_cob', 20)->nullable();
            $table->string('daily_cob_approve', 20)->nullable();
            $table->string('ex_id', 20)->nullable();
            $table->string('category', 20)->nullable();
            $table->string('old_date', 20)->nullable();
            $table->string('d_name_val', 20)->nullable();
            $table->string('accepted_by', 500)->nullable();
            $table->string('signed_by_customer', 20)->nullable();
            $table->decimal('previous_qty', 60, 4)->default(0);
            $table->string('confirm_signed', 20)->nullable();
            $table->string('confirm_by', 300)->nullable();
            $table->date('confirm_date')->nullable();
            $table->timestamps();
            
            $table->index('project');
            $table->index('t_id');
            $table->index('p_id');
            $table->index('tp_id');
            $table->index('status');
            $table->index('issue_date');
            $table->index('truck_plate_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sam_delivery');
    }
};
