<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_wo', function (Blueprint $table) {
            $table->date('report_date')
                ->nullable()
                ->after('wo_status');

            $table->integer('total_durasi')
                ->nullable()
                ->after('report_date');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_wo', function (Blueprint $table) {
            $table->dropColumn([
                'report_date',
                'total_durasi',
            ]);
        });
    }
};