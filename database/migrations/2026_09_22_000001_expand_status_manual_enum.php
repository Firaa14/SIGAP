<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('equipment_wo', 'status_manual')) {
            DB::statement("ALTER TABLE equipment_wo MODIFY status_manual ENUM('normal', 'abnormal', 'not_ready') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('equipment_wo', 'status_manual')) {
            DB::statement("UPDATE equipment_wo SET status_manual = 'not_ready' WHERE status_manual IN ('normal', 'abnormal')");
            DB::statement("ALTER TABLE equipment_wo MODIFY status_manual ENUM('not_ready') NULL");
        }
    }
};