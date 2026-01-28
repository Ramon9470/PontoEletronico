<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Cria a coluna turno_id que pode ser nula - usuário sem turno
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['turno_id']);
            $table->dropColumn('turno_id');
        });
    }
};