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
        Schema::create('sam_transporter', function (Blueprint $table) {
            $table->id();
            $table->string('transporter_type', 20)->default('company'); // company, ind
            $table->text('company_name')->nullable();
            $table->string('firstname', 500)->nullable();
            $table->string('lastname', 500)->nullable();
            $table->string('tin_no', 20)->nullable();
            $table->string('withholding', 20)->nullable();
            $table->string('withhold', 20)->nullable();
            $table->string('phone_no', 20)->nullable();
            $table->string('email', 500)->nullable();
            $table->string('contact_person', 500)->nullable();
            $table->string('office_location', 500)->nullable();
            $table->string('status', 20)->default('Active');
            $table->string('registered_by', 500)->nullable();
            $table->timestamps();
            
            $table->index('transporter_type');
            $table->index('status');
            $table->index('tin_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sam_transporter');
    }
};
