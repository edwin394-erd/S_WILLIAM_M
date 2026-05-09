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
            $table->timestamps(); // Crea created_at y updated_at
        });

       
        DB::table('departments')->insertGetId([
            'name' => 'AIT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('departments')->insertGetId([
            'name' => 'MECANICA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('departments')->insertGetId([
            'name' => 'CIVIL Y AMBIENTE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('departments')->insertGetId([
            'name' => 'INSTRUMENTACION',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('departments')->insertGetId([
            'name' => 'SERVICIOS ELECTRICOS',
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
