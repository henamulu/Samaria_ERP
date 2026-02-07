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
        Schema::create('sam_transporter_payment', function (Blueprint $table) {
            $table->id();
            $table->string('t_id', 20)->nullable(); // transporter_id
            $table->string('d_id', 20)->nullable(); // delivery_id
            $table->string('ta_id', 20)->nullable(); // transporter_agg_id
            $table->string('transporter_name', 500)->nullable();
            $table->decimal('unit_price', 60, 2)->default(0);
            $table->decimal('total', 60, 2)->default(0);
            $table->string('status', 20)->default('Pending'); // Pending, Approved, Settled
            $table->string('tp_no', 20)->nullable();
            $table->string('registered_by', 250)->nullable();
            $table->date('registered_date')->nullable();
            $table->decimal('trans_lose', 60, 2)->default(0);
            $table->string('approved_by', 20)->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('owner', 20)->nullable();
            $table->decimal('u_price', 60, 2)->default(0);
            $table->string('lose_repay', 20)->nullable();
            $table->decimal('trans_excess', 60, 2)->default(0);
            $table->decimal('trans_t_lose', 60, 2)->default(0);
            $table->decimal('trans_t_excess', 60, 2)->default(0);
            $table->timestamps();
            
            $table->index('t_id');
            $table->index('d_id');
            $table->index('status');
            $table->index('tp_no');
            $table->index('from_date');
            $table->index('to_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sam_transporter_payment');
    }
};
