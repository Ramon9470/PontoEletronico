<?php

namespace App\Http\Controllers;

use App\Models\SolicitacaoAjuste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SolicitacaoAjusteController extends Controller
{
    public function index()
    {
        return SolicitacaoAjuste::where('usuario_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_ajuste' => 'required',
            'data_ocorrido' => 'required|date',
            'justificativa' => 'required'
        ]);

        $data = $request->all();
        $data['usuario_id'] = Auth::id();

        if ($request->hasFile('anexo')) {
            $path = $request->file('anexo')->store('anexos', 'public');
            $data['anexo_url'] = $path;
        }

        SolicitacaoAjuste::create($data);

        return response()->json(['message' => 'Solicitação enviada com sucesso!']);
    }

    public function listarPendentes()
    {
        // Pega todas que não foram respondidas ainda, com os dados do usuário
        $pendentes = SolicitacaoAjuste::with('usuario')
                        ->where('status', 'pendente')
                        ->orderBy('created_at', 'desc')
                        ->get();

        // Formata para o Frontend
        return $pendentes->map(function ($item) {

            $usuario = $item->usuario;

            return [
                'id' => $item->id,
                'nome_completo' => $usuario ? $usuario->name : 'Usuário desconhecido (ID: '.$item->usuario_id.')',
                'cargo' => $usuario ? ($usuario->role ?? 'Colaborador') : '---',
                'foto_url' => $usuario ? $usuario->profile_photo_url : null,
                'data_ocorrido' => $item->data_ocorrido,
                'tipo_ajuste' => $item->tipo_ajuste,
                'horario' => $item->horario,
                'justificativa' => $item->justificativa,
                'anexo_url' => $item->anexo_url,
                'created_at' => $item->created_at,
            ];
        })->values();
    }

    public function atualizarStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aprovado,recusado'
        ]);

        $solicitacao = SolicitacaoAjuste::findOrFail($id);
        $solicitacao->status = $request->status;
        $solicitacao->save();

        return response()->json(['message' => 'Status atualizado com sucesso!']);
    }
}