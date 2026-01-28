<?php

namespace App\Http\Controllers;

use App\Models\Escala;
use Illuminate\Http\Request;

class EscalaController extends Controller
{
    public function index()
    {
        // Traz as escalas com os dados do funcionário
        $escalas = Escala::with('user:id,name,role,foto_url')
            ->orderBy('id', 'desc')
            ->get();

        $formatados = $escalas->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'nome_completo' => $item->user->name,
                'cargo' => ucfirst($item->user->role),
                'foto_url' => $item->user->foto_url ? asset('storage/' . $item->user->foto_url) : null,
                'tipo_escala' => $item->tipo,
                'inicio_ciclo' => $item->inicio_ciclo,
                'regra_folga' => $item->regra_folga
            ];
        });

        return response()->json($formatados);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tipo' => 'required',
            'inicio_ciclo' => 'required|date',
            'regra_folga' => 'required'
        ]);

        // Garante apenas uma escala por usuário Atualiza ou Cria
        Escala::updateOrCreate(
            ['user_id' => $request->user_id], // Busca por user_id
            [
                'tipo' => $request->tipo,
                'inicio_ciclo' => $request->inicio_ciclo,
                'regra_folga' => $request->regra_folga
            ]
        );

        return response()->json(['message' => 'Escala definida com sucesso!']);
    }

    public function destroy($id)
    {
        Escala::destroy($id);
        return response()->json(['message' => 'Escala removida']);
    }
}