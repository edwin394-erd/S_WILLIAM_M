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
        Schema::create('work_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable(); // Ej: PLD3-SEM16-2026
            $table->integer('week_number'); // Ej: Semana 16
            $table->date('start_date'); // jueves, 23 abril[cite: 1]
            $table->date('end_date');   // miércoles, 29 abril[cite: 1]
            $table->foreignId('department_id')->constrained();
            $table->integer('total_odm_scheduled')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_sheets');
    }
};
