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
        Schema::create('upload_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('filename', 255);
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);  // berhasil diproses
            $table->unsignedInteger('new_rows')->default(0);       // insert baru
            $table->unsignedInteger('updated_rows')->default(0);   // update existing
            $table->unsignedInteger('skipped_rows')->default(0);   // dilewati
            $table->unsignedInteger('error_rows')->default(0);     // gagal validasi
            $table->enum('status', ['processing', 'done', 'failed'])->default('processing');
            /** @var array<string, int> distribusi jumlah WO per nama PLTA */
            $table->json('plta_distribution')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_histories');
    }
};
