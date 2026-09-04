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
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plta_id')->constrained('pltas')->cascadeOnDelete();
            $table->string('unit', 50);
            $table->string('system', 100);
            $table->string('equipment', 150);
            $table->string('kks', 100)->nullable();
            $table->string('assetnum', 50)->unique(); // identifier utama
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
