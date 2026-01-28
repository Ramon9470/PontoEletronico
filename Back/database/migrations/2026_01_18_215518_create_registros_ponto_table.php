<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('registros_ponto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->date('data_registro');
            $table->time('hora_registro');
            $table->string('tipo_registro'); // Entrada, Saída, etc.
            $table->string('metodo')->nullable(); // Web, Facial, App
            $table->string('ip_origem')->nullable();
            $table->string('justificativa')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('registros_ponto');
    }
};