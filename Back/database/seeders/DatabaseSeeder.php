<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar Escalas
        $escalaGeralId = DB::table('escalas')->insertGetId([
            'nome' => 'Geral (8h)',
            'limite_batidas' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $escalaEstagioId = DB::table('escalas')->insertGetId([
            'nome' => 'Estagiário (6h)',
            'limite_batidas' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Criar Jornadas
        $jornadaComercialId = DB::table('jornadas')->insertGetId([
            'descricao' => 'Comercial 08h-18h',
            'tipo' => 'normal',
            'entrada_1' => '08:00:00',
            'saida_1' => '12:00:00',
            'entrada_2' => '13:00:00',
            'saida_2' => '18:00:00',
            'total_horas_diarias' => 8,
            'ativo' => true
        ]);

        // Criar Turnos
        $turnoMatutinoId = DB::table('turnos')->insertGetId([
            'nome' => 'Turno Comercial',
            'entrada' => '08:00:00',
            'saida' => '18:00:00',
            'intervalo' => '1h',
            'dias' => json_encode(['dom'=>false, 'seg'=>true, 'ter'=>true, 'qua'=>true, 'qui'=>true, 'sex'=>true, 'sab'=>false]),
            'status' => 'ativo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Criar Usuários
        
        // USUÁRIO ADMIN
        User::create([
            'name' => 'Administrador do Sistema',
            'username' => 'admin',
            'email' => 'admin@empresa.com',
            'password' => '123456',
            'role' => 'admin',
            'perfil' => 'Administrador',
            'active' => true,
            'cpf' => '000.000.000-01',
            'matricula' => 'ADM001',
            'cargo' => 'Diretor de TI',
            'departamento' => 'Tecnologia',
            'escala_id' => $escalaGeralId,
            'jornada_id' => $jornadaComercialId,
            'turno_id' => $turnoMatutinoId,
            'tipo_escala' => 'clt',
            'saldo_banco_horas' => 0.00
        ]);

        // USUÁRIO GESTOR
        User::create([
            'name' => 'Gerente de RH',
            'username' => 'gestor',
            'email' => 'rh@empresa.com',
            'password' => '123456',
            'role' => 'gestor',
            'perfil' => 'Gestor',
            'active' => true,
            'cpf' => '000.000.000-02',
            'matricula' => 'RH001',
            'cargo' => 'Gerente de RH',
            'departamento' => 'Recursos Humanos',
            'escala_id' => $escalaGeralId,
            'jornada_id' => $jornadaComercialId,
            'turno_id' => $turnoMatutinoId,
            'tipo_escala' => 'clt'
        ]);

        // USUÁRIO FUNCIONÁRIO
        User::create([
            'name' => 'João Estagiário',
            'username' => 'funcionario',
            'email' => 'joao@empresa.com',
            'password' => '123456',
            'role' => 'funcionario',
            'perfil' => 'Colaborador',
            'active' => true,
            'cpf' => '000.000.000-03',
            'matricula' => 'EST001',
            'cargo' => 'Estagiário Dev',
            'departamento' => 'Desenvolvimento',
            'escala_id' => $escalaEstagioId,
            'jornada_id' => $jornadaComercialId,
            'turno_id' => $turnoMatutinoId,
            'tipo_escala' => 'estagiario'
        ]);

        $this->command->info('Banco de dados populado com sucesso! Usuário: admin / Senha: 123456');
    }
}