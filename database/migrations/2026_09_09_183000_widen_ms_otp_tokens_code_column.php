<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Widen otp_code from CHAR(6) to VARCHAR(255) to support bcrypt/secure hash storage
        Schema::table('ms_otp_tokens', function (Blueprint $table) {
            $table->string('otp_code', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_otp_tokens', function (Blueprint $table) {
            $table->char('otp_code', 6)->change();
        });
    }
};
