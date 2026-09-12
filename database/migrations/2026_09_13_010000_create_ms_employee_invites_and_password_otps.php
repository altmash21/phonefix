<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employee Invites Table
        if (!Schema::hasTable('ms_employee_invites')) {
            Schema::create('ms_employee_invites', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->default(1)->index();
                $table->string('token', 32)->unique();
                $table->string('role_name', 50); // sales-staff, accessories-staff, cover-staff, repair-technician, store-admin
                $table->string('recipient_name', 100)->nullable();
                $table->unsignedBigInteger('created_by')->index();
                $table->unsignedBigInteger('used_by')->nullable()->index();
                $table->dateTime('used_at')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->string('status', 20)->default('active'); // active, used, revoked, expired
                $table->timestamps();

                $table->index(['company_id', 'status', 'token'], 'idx_invite_lookup');
            });
        }

        // 2. Password Reset OTPs Table
        if (!Schema::hasTable('ms_password_reset_otps')) {
            Schema::create('ms_password_reset_otps', function (Blueprint $table) {
                $table->id();
                $table->string('email', 191)->index();
                $table->string('otp_code', 6);
                $table->dateTime('expires_at')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['email', 'otp_code'], 'idx_pwd_otp_lookup');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_employee_invites');
        Schema::dropIfExists('ms_password_reset_otps');
    }
};
