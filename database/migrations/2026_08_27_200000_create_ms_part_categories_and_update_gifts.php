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
        if (!Schema::hasTable('ms_part_categories')) {
            Schema::create('ms_part_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->default(1)->index();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->boolean('is_gift_eligible')->default(false);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('ms_parts_inventory')) {
            Schema::table('ms_parts_inventory', function (Blueprint $table) {
                if (!Schema::hasColumn('ms_parts_inventory', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->after('category')->index();
                }
                if (!Schema::hasColumn('ms_parts_inventory', 'is_gift_eligible')) {
                    $table->boolean('is_gift_eligible')->default(false)->after('min_stock_alert');
                }
            });
        }

        // Seed initial default categories if empty
        $defaultCategories = [
            ['name' => 'Display Screen / Folder', 'slug' => 'folder_display', 'is_gift_eligible' => false],
            ['name' => 'Front Glass', 'slug' => 'front_glass', 'is_gift_eligible' => false],
            ['name' => 'Charging Pin / Port', 'slug' => 'charging_port', 'is_gift_eligible' => false],
            ['name' => 'IC / Motherboard Chip', 'slug' => 'ic_chip', 'is_gift_eligible' => false],
            ['name' => 'Battery', 'slug' => 'battery', 'is_gift_eligible' => false],
            ['name' => 'Back Panel / Housing Glass', 'slug' => 'back_panel', 'is_gift_eligible' => false],
            ['name' => 'Back Cover & Cases', 'slug' => 'back_cover', 'is_gift_eligible' => true],
            ['name' => 'Tempered Glass', 'slug' => 'tempered_glass', 'is_gift_eligible' => true],
            ['name' => 'Chargers & Cables', 'slug' => 'charger_cable', 'is_gift_eligible' => true],
            ['name' => 'Earphones & Audio', 'slug' => 'earphones_audio', 'is_gift_eligible' => true],
            ['name' => 'Camera Module', 'slug' => 'camera_module', 'is_gift_eligible' => false],
            ['name' => 'General Accessories', 'slug' => 'general_accessory', 'is_gift_eligible' => true],
        ];

        foreach ($defaultCategories as $cat) {
            $exists = DB::table('ms_part_categories')->where('company_id', 1)->where('name', $cat['name'])->exists();
            if (!$exists) {
                DB::table('ms_part_categories')->insert([
                    'company_id' => 1,
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'is_gift_eligible' => $cat['is_gift_eligible'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Mark existing tempered glass and back panels as gift eligible in inventory
        if (Schema::hasTable('ms_parts_inventory')) {
            DB::table('ms_parts_inventory')
                ->whereIn('category', ['tempered_glass', 'back_panel', 'general_accessory'])
                ->update(['is_gift_eligible' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_part_categories');

        if (Schema::hasTable('ms_parts_inventory')) {
            Schema::table('ms_parts_inventory', function (Blueprint $table) {
                if (Schema::hasColumn('ms_parts_inventory', 'category_id')) {
                    $table->dropColumn('category_id');
                }
                if (Schema::hasColumn('ms_parts_inventory', 'is_gift_eligible')) {
                    $table->dropColumn('is_gift_eligible');
                }
            });
        }
    }
};
