<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment_wo', function (Blueprint $table) {
            // 0. Foreign key equipment_id "menempel" pada index unique lama,
            //    jadi foreign key-nya harus dilepas dulu sebelum index bisa di-drop.
            $table->dropForeign(['equipment_id']);
        });

        Schema::table('equipment_wo', function (Blueprint $table) {
            // 1. Baru sekarang unique constraint lama bisa di-drop
            //    (yang membuat satu equipment cuma boleh punya 1 WO)
            $table->dropUnique(['equipment_id']);

            // 2. Pasang lagi foreign key-nya, tapi kali ini tanpa unique,
            //    supaya equipment_id tetap tervalidasi ke tabel equipments
            //    namun boleh muncul berkali-kali di equipment_wo.
            $table->foreign('equipment_id')
                ->references('id')->on('equipments')
                ->cascadeOnDelete();
        });

        Schema::table('equipment_wo', function (Blueprint $table) {
            // 3. Composite unique baru:
            //    equipment yang sama boleh punya banyak WO,
            //    tapi kombinasi equipment_id + no_wo + worktype + description
            //    yang identik dianggap record yang sama (untuk keperluan re-upload/update).
            //
            // Catatan: kolom `description` bertipe TEXT. MySQL tidak bisa membuat
            // unique index langsung di kolom TEXT tanpa panjang (length) tertentu,
            // jadi kita batasi 191 karakter pertama untuk index-nya.
            $table->unique(
                ['equipment_id', 'no_wo', 'worktype', DB::raw('description(191)')],
                'equipment_wo_unique_combo'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_wo', function (Blueprint $table) {
            $table->dropUnique('equipment_wo_unique_combo');
            $table->dropForeign(['equipment_id']);
        });

        Schema::table('equipment_wo', function (Blueprint $table) {
            $table->unique('equipment_id');
            $table->foreign('equipment_id')
                ->references('id')->on('equipments')
                ->cascadeOnDelete();
        });
    }
};