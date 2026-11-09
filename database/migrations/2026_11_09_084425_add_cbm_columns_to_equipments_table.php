<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (! Schema::hasColumn('equipments', 'cbm_alias')) {
                $table->string('cbm_alias', 255)->nullable();
            }
            if (! Schema::hasColumn('equipments', 'cbm_parameters')) {
                $table->string('cbm_parameters', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            foreach (['cbm_alias', 'cbm_parameters'] as $column) {
                if (Schema::hasColumn('equipments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};