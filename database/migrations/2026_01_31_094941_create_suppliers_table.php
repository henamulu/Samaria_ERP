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
        Schema::create('sam_supplier', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name', 500);
            $table->string('supplier_tin', 20)->nullable();
            $table->string('supplier_category', 100)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('status', 50)->default('Active');
            $table->string('address', 500)->nullable();
            $table->string('registered_by', 500)->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('supplier_tin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sam_supplier');
    }
};
