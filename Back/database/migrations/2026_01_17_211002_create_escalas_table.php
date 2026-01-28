<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Cria a tabela de Escalas
        Schema::create('escalas', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Administrativo, Estagiário
            $table->integer('limite_batidas')->default(4); // 4 ou 2
            $table->timestamps();
        });

        // Insere as regras padrão automaticamente
        DB::table('escalas')->insert([
            ['nome' => 'Geral (8h)', 'limite_batidas' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Estagiário (6h)', 'limite_batidas' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Adiciona a coluna na tabela de usuários
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('escala_id')->nullable()->after('cargo')->constrained('escalas');
        });

        // Atualiza os usuários existentes
        DB::statement("UPDATE users SET escala_id = 2 WHERE cargo LIKE '%Estagiário%'");
        DB::statement("UPDATE users SET escala_id = 1 WHERE escala_id IS NULL");
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['escala_id']);
            $table->dropColumn('escala_id');
        });
        Schema::dropIfExists('escalas');
    }
};