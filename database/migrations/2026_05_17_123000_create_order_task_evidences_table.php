<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_task_evidences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_task_id');
            $table->string('path');
            $table->timestamps();

            $table->foreign('order_task_id')
                ->references('id')
                ->on('order_tasks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_task_evidences');
    }
};
