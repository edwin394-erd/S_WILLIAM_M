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
        // Tabla de disciplinas (Sub-división de departamentos)
        Schema::create('disciplines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained();
            $table->string('name'); // Ej: SCADA, Instrumentación, Redes
            $table->timestamps();
        });

        $data = [
    // DEPARTAMENTO DE AIT (ID: 1)
    1 => [
        'REDES', 'SCADA XSPOC', 'AUTOMATIZACION', 'RESPALDO ELECTRICO', 
        'INFRAESTRUCTURA', 'TELEFONIA', 'SCADA WONDERWARE', 
        'TELECOM TELEMETRIA', 'TELECOM TRANSMISION', 'GEREMPRO ELECTRICO', 
        'GEREMPOR SCADA', 'GEREMPRO AUTOMATIZACION'
    ],
    // DEPARTAMENTO DE MECANICA (ID: 2)
    2 => [
        'TALLER', 'COMPRESORES', 'LUBRICACION', 'MOTORES LUKIVEN', 
        'MECANICA ESTACIONES', 'MECANICA POZOS', 'BOMBAS', 'FUERZA MOTRIZ',
        'MECANICA POZOS', 'MECANICA ESTACIONES' // Reincorporados según tu instrucción
    ],
    // DEPARTAMENTO DE CIVIL Y AMBIENTE (ID: 3)
    3 => [
        'ACONDICIONAMIENTO DE VIAS', 'ACONDICIONAMIENTO DE CERCAS', 
        'CIVIL ESTRUCTURAL MENSERCA', 'CIVIL ESTRUCTURAL LATICON', 
        'CIVIL LINEAS', 'CONSTRUCCION SANFOR', 'CONSTRUCCION SOLCIMECA', 
        'DESMALEZADO', 'FUMIGACION', 'MANTENIMIENTO MAYOR', 
        'MANTENIMIENTO MAYOR TANQUES', 'SOLDADURA', 'SANEAMIENTO', 
        'CAMION BRAZO CIVIL', 'CIVIL VALVULAS PSV', 'RIESGO BIOLOGICO'
    ],
    // DEPARTAMENTO DE INSTRUMENTACION (ID: 4)
    4 => [
        'POZO INSTRUMENTO', 'PLANTA Y ESTACIONES', 'PLD3 INSTRUMENTO', 
        'PLDZ9 INSTRUMENTO', 'MANTENIMIENTO NORTE', 'MANTENIMIENTO SUR', 
        'CUADRILLA NOCTURNA', 'MENSERCA INSTRUMENTO SUR', 
        'MENSERCA INSTRUMENTO NORTE', 'POZOS INYECTORES', 'GEREMPRO INSTRUMENTO'
    ],
    // DEPARTAMENTO DE SERVICIOS ELECTRICOS (ID: 5)
    5 => [
        'ELECTRICO POZO', 'ELECTRICO PLANTAS', 'ELECTRICO ESTACIONES', 
        'SUBESTACIONES ELECTRICAS', 'TERMOELECTRICA', '24KV', 'TRANSMISION', 
        'ALUMBRADO', 'ELECTRICO SANFO', 'UPS', 'PREVENTIVO NORTE', 
        'PREVENTIVO SUR', 'CORRECTIVO NORTE', 'CORRECTIVO SUR'
    ],
];

foreach ($data as $deptId => $disciplines) {
    foreach ($disciplines as $name) {
        DB::table('disciplines')->insert([
            'department_id' => $deptId,
            'name'          => $name,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplines');
    }
};
