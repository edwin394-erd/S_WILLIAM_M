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
        Schema::table('order_tasks', function (Blueprint $table) {
            $table->enum('priority', ['Nivel 1', 'Nivel 2', 'Prioridad alta', 'Actividad critica'])
                ->default('Nivel 1')
                ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_tasks', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
