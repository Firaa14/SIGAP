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
        Schema::create('equipment_wo', function (Blueprint $table) {
            $table->id();
            // UNIQUE → satu equipment hanya boleh punya satu WO aktif
            $table->foreignId('equipment_id')
                ->unique()
                ->constrained('equipments')
                ->cascadeOnDelete();
            $table->string('no_wo', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('worktype', 10)->nullable();  // CM / EJ / EV / PAM
            $table->string('wo_status', 20)->nullable(); // APPR / INPRG / CLOSE / COMP / dll
            // Status dari Excel: null = belum ada data WO
            $table->enum('status_otomatis', ['normal', 'abnormal'])->nullable();
            // Status manual dari user: hanya not_ready; null = tidak sedang not_ready
            $table->enum('status_manual', ['not_ready'])->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_wo');
    }
};
