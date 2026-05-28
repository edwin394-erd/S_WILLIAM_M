<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discipline_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'discipline_id']);
        });

        $defaultUsers = DB::table('users')
            ->where('role', 'supervisor')
            ->whereNotNull('discipline_id')
            ->get();

        foreach ($defaultUsers as $user) {
            DB::table('discipline_user')->insert([
                'user_id' => $user->id,
                'discipline_id' => $user->discipline_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_user');
    }
};
