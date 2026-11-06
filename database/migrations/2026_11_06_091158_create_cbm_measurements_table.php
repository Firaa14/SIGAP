<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbm_measurements', function (Blueprint $table) {
            $table->id();
            $table->string('assetnum', 50)->index();
            $table->string('parameter', 30);
            $table->string('status_code', 10);
            $table->date('measured_at');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->unique(['assetnum', 'parameter', 'measured_at'], 'cbm_unique_measurement');
            $table->index(['parameter', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbm_measurements');
    }
};