<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->time('entrada');
            $table->time('saida');
            $table->string('intervalo')->nullable();
            $table->json('dias');
            $table->string('status')->default('ativo');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('turnos');
    }
};