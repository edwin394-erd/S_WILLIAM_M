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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre completo (ej: William Marin)
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // ROL: Determina si es Admin (Planificación) o Supervisor
            // Usamos string para legibilidad o integer para performance
            $table->string('role')->default('supervisor'); // 'admin', 'supervisor','technician','planificator'

            // PERTENENCIA: Si es supervisor, ¿de qué departamento es responsable?
            // Si es null, asumimos que es Admin de Planificación
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('discipline_id')->nullable()->constrained('disciplines');
            // Identificador de empleado (opcional, muy común en PDVSA)
            $table->string('indicator')->nullable()->unique(); // Ej: ABC1234
            
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        // 3. Insertar Usuarios
        DB::table('users')->insert([
            [
                'name' => 'Supervisor AIT',
                'email' => 'supervisor@test.com',
                'password' => Hash::make('admin123'),
                'role' => 'supervisor',
                'department_id' => 1,
                'discipline_id' => 1,
                'indicator' => 'SUP001', // Agregado ya que lo pusiste como unique
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'department_id' => null,
                'discipline_id' => null,
                'indicator' => 'ADM001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Planificador',
                'email' => 'planificador@test.com',
                'password' => Hash::make('admin123'),
                'role' => 'planificador',
                'department_id' => null,
                'discipline_id' => null,
                'indicator' => 'PLAN001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'name' => 'Tecnico',
                'email' => 'tecnico@test.com',
                'password' => Hash::make('admin123'),
                'role' => 'tecnico',
                'department_id' => 1,
                'discipline_id' => 1,
                'indicator' => 'TEC001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
