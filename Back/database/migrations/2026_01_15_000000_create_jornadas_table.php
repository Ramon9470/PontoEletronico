<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jornadas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 100);
            $table->enum('tipo', ['normal', 'estagio', '12x36'])->default('normal');
            $table->time('entrada_1');
            $table->time('saida_1');
            $table->time('entrada_2')->nullable();
            $table->time('saida_2')->nullable();
            $table->time('saida_sabado')->nullable();
            $table->integer('tolerancia_minutos')->default(10);
            $table->integer('total_horas_diarias')->default(8);
            
            // Dias de trabalho
            $table->boolean('trabalha_seg')->default(true);
            $table->boolean('trabalha_ter')->default(true);
            $table->boolean('trabalha_qua')->default(true);
            $table->boolean('trabalha_qui')->default(true);
            $table->boolean('trabalha_sex')->default(true);
            $table->boolean('trabalha_sab')->default(false);
            $table->boolean('trabalha_dom')->default(false);
            
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jornadas');
    }
};