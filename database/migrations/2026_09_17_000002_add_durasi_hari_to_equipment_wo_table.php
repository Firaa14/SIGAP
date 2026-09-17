<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('equipment_wo', 'durasi_hari')) {
            Schema::table('equipment_wo', function (Blueprint $table) {
                $table->unsignedInteger('durasi_hari')->nullable()->after('report_date');
            });
        }

        if (Schema::hasColumn('equipment_wo', 'total_durasi')) {
            DB::table('equipment_wo')
                ->whereNull('durasi_hari')
                ->whereNotNull('total_durasi')
                ->update(['durasi_hari' => DB::raw('total_durasi')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('equipment_wo', 'durasi_hari')) {
            Schema::table('equipment_wo', function (Blueprint $table) {
                $table->dropColumn('durasi_hari');
            });
        }
    }
};