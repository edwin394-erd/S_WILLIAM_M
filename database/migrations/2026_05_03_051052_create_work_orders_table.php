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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_sheet_id')->constrained();
            $table->string('odm_number')->unique(); // Código 600601...[cite: 1]
            $table->enum('type', ['CORRECTIVO', 'PREVENTIVO','PREDICTIVO','DETECTIVO']);
            $table->string('impacto')->default('0 Bis'); // Prioridad[cite: 1]
            $table->foreignId('installation_id')->constrained();
            $table->foreignId('equipment_id')->constrained();
            $table->text('accion_requerida');
            $table->boolean('is_high_risk')->default(false); 
            $table->boolean('is_extraplan')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
