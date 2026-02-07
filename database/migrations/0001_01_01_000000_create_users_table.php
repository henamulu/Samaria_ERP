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
        Schema::create('sam_user', function (Blueprint $table) {
            $table->id();
            $table->string('department', 500)->nullable();
            $table->string('branch', 100)->nullable();
            $table->string('type', 30)->nullable();
            $table->string('firstname', 500)->nullable();
            $table->string('middlename', 500)->nullable();
            $table->string('lastname', 500)->nullable();
            $table->string('company_name', 200)->nullable();
            $table->string('user_name', 20)->unique();
            $table->string('user_email', 64)->unique();
            $table->string('phone_no', 20)->nullable();
            $table->string('user_password', 64);
            $table->string('user_status', 20)->default('Active'); // Active, Inactive, Deleted
            $table->string('role', 20)->nullable(); // Admin, User, cashier
            $table->string('filename', 255)->nullable();
            $table->string('size', 50)->nullable();
            $table->string('i_type', 50)->nullable();
            $table->string('status', 20)->default('Old'); // Old, New
            $table->string('user_for', 50)->nullable();
            $table->string('o_status', 30)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('user_type', 50)->nullable();
            $table->string('registered_by', 500)->nullable();
            $table->date('date_registered')->nullable();
            $table->string('reset_type', 20)->nullable();
            $table->string('temp_key', 500)->nullable();
            $table->string('profile_image', 500)->nullable();
            $table->string('dashboard', 20)->nullable(); // finance, sales, purchase
            $table->string('site', 20)->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('user_status');
            $table->index('role');
            $table->index('status');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sam_user');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
