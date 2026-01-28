<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('afastamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Vínculo com usuário
            $table->string('tipo'); // atestado, maternidade, inss, outros
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->text('motivo')->nullable();
            $table->string('anexo_url')->nullable(); // Caminho do arquivo
            $table->string('status')->default('analise'); // analise, aprovado, rejeitado
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('afastamentos');
    }
};