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
        Schema::table('users', function (Blueprint $table) {
            // clt (220h), estagiario (120h), 12x36 (180h)
            $table->string('tipo_escala')->default('clt')->after('role'); 
            $table->decimal('saldo_banco_horas', 8, 2)->default(0)->after('tipo_escala');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
