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
        Schema::create('order_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained();
            $table->foreignId('discipline_id')->nullable()->constrained();
            // Tiempos y Programación
            $table->date('date'); // Programado para el 23/04/26 8:00[cite: 1]
            $table->dateTime('time_start'); // Del 23/04/26 8:00[cite: 1]
            $table->dateTime('time_end');   // Al 23/04/26 9:59[cite: 1]
     
            
            // Estado y Evidencia
            $table->enum('status', ['PENDIENTE', 'COMPLETADO', 'NO COMPLETADO', 'POR REVISION'])->default('PENDIENTE');
            $table->text('observation')->nullable(); // Motivo de no cumplimiento
            $table->string('evidence_path')->nullable(); // Ruta de la foto
            $table->foreignId('user_report_id')->nullable()->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tasks');
    }
};
