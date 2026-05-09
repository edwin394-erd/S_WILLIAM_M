<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Añadido unique para evitar duplicados
            $table->timestamps();
        });

        // Lista de equipos a insertar
        $equipos = [
            'BOMBA', 'VDF', 'RTU', 'PLC', 'MOTOR', 'BALANCIN', 'APLICACIÓN', 
            'ANTENA', 'TANQUE', 'UPS', 'GENERADOR', 'CALDERA', 'TRAMO', 
            'SUB MULTIPLE', 'OLEODUCTO', 'CERCA', 'CARRETERA', 'OTROS', 
            'INTERRUPTOR', 'TABLERO', 'PROTECCION', 'TRANSFORMADOR', 
            'ACOMETIDA', 'SENSOR', 'TRANSMISOR', 'VALVULA', 'ACTUADOR', 
            'COMPRESOR', 'TURBINA', 'TUBERIA'
        ];

        // Preparamos los datos para el insert masivo
        $data = array_map(function($nombre) {
            return [
                'name' => $nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, array_unique($equipos)); // array_unique por si hay repetidos en la lista

        DB::table('equipment')->insert($data);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ojo: el nombre de la tabla en tu up es 'equipment', no 'equipments'
        Schema::dropIfExists('equipment');
    }
};