<?php

namespace App\Http\Controllers;

use App\Models\Ferias;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FeriasController extends Controller
{
    public function index()
    {
        $ferias = Ferias::with('user:id,name,role,foto_url')
            ->orderBy('data_inicio', 'desc')
            ->get();

        $formatados = $ferias->map(function ($item) {
            return [
                'id' => $item->id,
                'nome_completo' => $item->user->name,
                'cargo' => ucfirst($item->user->role),
                'foto_url' => $item->user->foto_url ? asset('storage/' . $item->user->foto_url) : null,
                'data_inicio' => $item->data_inicio,
                'data_fim' => $item->data_fim,
                'dias_gozo' => $item->dias_gozo,
                'vender_um_terco' => (bool)$item->vender_um_terco,
                'status_visual' => $item->status_visual
            ];
        });

        return response()->json($formatados);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'data_inicio' => 'required|date',
            'dias' => 'required|integer|min:5|max:30',
            'vender_um_terco' => 'boolean'
        ]);

        // Calcula a data fim baseada nos dias de gozo
        $inicio = Carbon::parse($request->data_inicio);
        $dias = (int)$request->dias;
        $fim = $inicio->copy()->addDays($dias - 1);

        Ferias::create([
            'user_id' => $request->user_id,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $fim->format('Y-m-d'),
            'dias_gozo' => $dias,
            'vender_um_terco' => $request->vender_um_terco ?? false
        ]);

        return response()->json(['message' => 'Férias programadas com sucesso!']);
    }

    public function destroy($id)
    {
        Ferias::destroy($id);
        return response()->json(['message' => 'Programação cancelada']);
    }
}