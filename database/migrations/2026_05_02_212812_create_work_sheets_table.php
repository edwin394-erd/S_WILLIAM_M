<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

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
            $table->string('enviado')->default("POR ENVIAR"); // Indica si la hoja de trabajo ha sido enviada a SAP
            $table->timestamps();
        });

        // $now = Carbon::now('America/Caracas');

        // // Calcular inicio de semana (jueves) y fin (miércoles) con zona Venezuela
        // $weekday = (int) $now->dayOfWeekIso; // 1=Mon .. 7=Sun
        // if ($weekday >= 4) {
        //     $currentStart = Carbon::parse('thursday this week', 'America/Caracas')->toDateString();
        // } else {
        //     $currentStart = Carbon::parse('thursday last week', 'America/Caracas')->toDateString();
        // }
        // $currentEnd = Carbon::parse($currentStart, 'America/Caracas')->addDays(6)->toDateString();

        // // Calcular número de semana usando primer jueves del año como ancla
        // $year = (int) $now->format('Y');
        // $firstThursday = Carbon::parse("first thursday of january $year", 'America/Caracas')->toDateString();
        // $weekNumber = (int) floor((strtotime($currentStart) - strtotime($firstThursday)) / (7 * 24 * 3600)) + 1;
        // $departmentId = DB::table('departments')
        //     ->where('name', 'ait')
        //     ->value('id');

        // if ($departmentId) {
        //     DB::table('work_sheets')->insert([
        //         'codigo' => "PLD3-SEM{$weekNumber}-{$now->year}",
        //         'week_number' => $weekNumber,
        //         'start_date' => $currentStart,
        //         'end_date' => $currentEnd,
        //         'department_id' => $departmentId,
        //         'total_odm_scheduled' => 0,
        //         'enviado' => 'POR ENVIAR',
        //         'created_at' => $now,
        //         'updated_at' => $now,
        //     ]);
        // }
    }

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_sheets');
    }
};
