<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Campos Padrão de Acesso
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('funcionario');
            $table->boolean('active')->default(true);
            
            // Dados Pessoais
            $table->string('cpf', 20)->nullable()->unique();
            $table->string('rg', 20)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('genero', 20)->default('Masculino');
            $table->string('telefone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            
            // Endereço
            $table->string('cep', 20)->nullable();
            $table->string('logradouro', 150)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('uf', 5)->nullable();

            // Dados Corporativos
            $table->string('matricula', 20)->nullable()->unique();
            $table->string('cargo', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->date('data_admissao')->nullable();
            $table->string('ctps', 50)->nullable();
            $table->string('foto_url', 255)->nullable();
            
            // Legado/Outros
            $table->integer('jornada_id')->nullable();
            $table->string('perfil', 50)->default('funcionario');
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};