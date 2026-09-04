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
        Schema::create('pltas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_plta', 100);
            $table->string('kode_prefix', 10)->unique(); // 4-char prefix ASSETNUM, e.g. BSGR
            $table->string('slug', 100)->unique();       // URL slug, e.g. sengguruh
            $table->string('location', 150)->nullable();
            $table->string('capacity', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pltas');
    }
};
