<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. OTP tokens for gating destructive actions
        Schema::create('ms_otp_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('requested_by')->index();
            $table->string('action', 50); // delete_stock, void_sale, void_accessory_sale, delete_category
            $table->string('item_reference', 100); // e.g. part:42, sale:185
            $table->char('otp_code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'action', 'item_reference', 'otp_code'], 'idx_otp_lookup');
        });

        // 2. Login session tracking
        Schema::create('ms_login_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(1);
            $table->unsignedBigInteger('user_id')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_label', 150)->nullable();
            $table->string('session_token', 100)->nullable()->index();
            $table->timestamp('logged_in_at')->useCurrent();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('logged_out_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->index(['user_id', 'is_active']);
        });

        // 3. Enhance EMI providers with processing fees and tenure
        if (Schema::hasTable('ms_emi_providers')) {
            Schema::table('ms_emi_providers', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_emi_providers', 'processing_fee_flat')) {
                    $table->decimal('processing_fee_flat', 10, 2)->default(0.00)->after('advance_balance');
                }
                if (!Schema::hasColumn('ms_emi_providers', 'processing_fee_pct')) {
                    $table->decimal('processing_fee_pct', 5, 2)->default(0.00)->after('processing_fee_flat');
                }
                if (!Schema::hasColumn('ms_emi_providers', 'default_tenure_months')) {
                    $table->integer('default_tenure_months')->default(12)->after('processing_fee_pct');
                }
                if (!Schema::hasColumn('ms_emi_providers', 'interest_rate_pct')) {
                    $table->decimal('interest_rate_pct', 5, 2)->default(0.00)->after('default_tenure_months');
                }
                if (!Schema::hasColumn('ms_emi_providers', 'notes')) {
                    $table->text('notes')->nullable()->after('interest_rate_pct');
                }
            });
        }

        // 4. Add processing fee column to mobile sales
        if (Schema::hasTable('ms_mobile_sales')) {
            Schema::table('ms_mobile_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_mobile_sales', 'emi_processing_fee')) {
                    $table->decimal('emi_processing_fee', 10, 2)->default(0.00)->after('emi_financed_amount');
                }
            });
        }

        // 5. Add description to parts inventory
        if (Schema::hasTable('ms_parts_inventory')) {
            Schema::table('ms_parts_inventory', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_parts_inventory', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_otp_tokens');
        Schema::dropIfExists('ms_login_sessions');

        if (Schema::hasTable('ms_emi_providers')) {
            Schema::table('ms_emi_providers', function (Blueprint $table) {
                $cols = ['processing_fee_flat', 'processing_fee_pct', 'default_tenure_months', 'interest_rate_pct', 'notes'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('ms_emi_providers', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('ms_mobile_sales')) {
            Schema::table('ms_mobile_sales', function (Blueprint $table) {
                if (Schema::hasColumn('ms_mobile_sales', 'emi_processing_fee')) {
                    $table->dropColumn('emi_processing_fee');
                }
            });
        }

        if (Schema::hasTable('ms_parts_inventory')) {
            Schema::table('ms_parts_inventory', function (Blueprint $table) {
                if (Schema::hasColumn('ms_parts_inventory', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};
