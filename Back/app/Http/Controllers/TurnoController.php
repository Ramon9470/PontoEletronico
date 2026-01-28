<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\User;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    // Listar todos os turnos
    public function index()
    {
        // Traz os turnos e conta quantos usuários tem em cada um
        $turnos = Turno::withCount('users')->get();
        
        $turnosFormatados = $turnos->map(function($turno) {
            $turno->qtd_colaboradores = $turno->users_count;
            return $turno;
        });

        return response()->json($turnosFormatados);
    }

    // Criar Turno
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required',
            'entrada' => 'required',
            'saida' => 'required',
            'dias' => 'required'
        ]);

        $turno = Turno::create($request->all());

        // Se veio um usuarioId, vincula ele a este turno agora
        if ($request->has('usuarioId') && !empty($request->usuarioId)) {
            $user = User::find($request->usuarioId);
            if ($user) {
                $user->turno_id = $turno->id;
                $user->save();
            }
        }

        return response()->json($turno, 201);
    }

    // Atualizar Turno
    public function update(Request $request, $id)
    {
        $turno = Turno::findOrFail($id);
        $turno->update($request->all());
        return response()->json($turno);
    }

    // Alternar Status Ativo/Inativo
    public function status($id)
    {
        $turno = Turno::findOrFail($id);
        $turno->status = $turno->status === 'ativo' ? 'inativo' : 'ativo';
        $turno->save();
        return response()->json($turno);
    }

    // Excluir
    public function destroy($id)
    {
        Turno::destroy($id);
        return response()->json(['message' => 'Turno excluído']);
    }

    // Listar Colaboradores de um Turno específico
    public function colaboradores($id)
    {
        $turno = Turno::with('users')->findOrFail($id);
        return response()->json($turno->users);
    }
}