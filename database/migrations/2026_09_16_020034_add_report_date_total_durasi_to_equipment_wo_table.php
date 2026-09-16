<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_wo', function (Blueprint $table) {
            // Tanggal report dari kolom REPORTDATE di file Excel yang diupload
            $table->date('report_date')->nullable()->after('wo_status');

            // Selisih hari antara report_date dan waktu upload (uploaded_at)
            $table->unsignedInteger('durasi_hari')->nullable()->after('report_date');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_wo', function (Blueprint $table) {
            $table->dropColumn(['report_date', 'durasi_hari']);
        });
    }
};
