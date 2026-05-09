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
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('impact'); // Cambiado a integer para cálculos numéricos de barriles
            $table->timestamps();
        });

        $installations = [];

        // 1. Pozos BN-0001 al BN-1200 (Impacto 70 a 1450 Bls)
        for ($i = 1; $i <= 1200; $i++) {
            $installations[] = [
                'name' => 'BN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'impact' => rand(70, 1450),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 2. Estaciones de Flujo EF-1 a EF-20 (Impacto 1400 a 30000 Bls)
        for ($i = 1; $i <= 20; $i++) {
            $installations[] = [
                'name' => 'EF-' . $i,
                'impact' => rand(1400, 30000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 3. Estaciones específicas EF-A
        foreach (['EF-A3', 'EF-A4', 'EF-A5'] as $ef) {
            $installations[] = [
                'name' => $ef,
                'impact' => rand(1400, 30000), // Asumimos rango de EF
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 4. Ubicaciones Especiales (Z, Subestaciones, etc.)
        $especiales = [
            'Z81', 'Z82', 'Z83', 'Z83A', 'Z10', 'Z92', 'Z9', 
            'PLD3', 'PLDZ9', 'SUBESTACION ELECTRICA Z9', 
            'SUBESTACION ELECTRICA Z10', 'RECONECTADOR'
        ];

        foreach ($especiales as $nombre) {
            $installations[] = [
                'name' => $nombre,
                'impact' => 0, // Valor base o por definir
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar en bloques (chunks) para evitar errores de memoria si la lista crece más
        foreach (array_chunk($installations, 200) as $chunk) {
            DB::table('installations')->insert($chunk);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};