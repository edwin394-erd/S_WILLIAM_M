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
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); // Crea el ID incremental (PK)
            $table->string('name'); // Esta es la columna que te faltaba
            $table->string('grupo_telegram_id')->nullable(); // ID del grupo de Telegram, puede ser nulo
            $table->timestamps(); // Crea created_at y updated_at
        });

       
        DB::table('departments')->insertGetId([
            'name' => 'AIT',
            'grupo_telegram_id' => '-1003979777993',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('departments')->insertGetId([
            'name' => 'MECANICA',
            'grupo_telegram_id' => '-1003989354311',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('departments')->insertGetId([
            'name' => 'CIVIL Y AMBIENTE',
            'grupo_telegram_id' => '-1003719556146',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('departments')->insertGetId([
            'name' => 'INSTRUMENTACION',
            'grupo_telegram_id' => '-1003941773654',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('departments')->insertGetId([
            'name' => 'SERVICIOS ELECTRICOS',
            'grupo_telegram_id' => '-1003910840521',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
