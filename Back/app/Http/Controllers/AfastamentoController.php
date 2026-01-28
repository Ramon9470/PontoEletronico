<?php

namespace App\Http\Controllers;

use App\Models\Afastamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AfastamentoController extends Controller
{
    public function index()
    {
        // Traz os afastamentos com dados do usuário
        $afastamentos = Afastamento::with('user:id,name,role,foto_url')
            ->orderBy('data_inicio', 'desc')
            ->get();

        // Formata para o Frontend
        $formatados = $afastamentos->map(function ($item) {
            return [
                'id' => $item->id,
                'funcionario_nome' => $item->user->name,
                'cargo' => ucfirst($item->user->role),
                'foto_url' => $item->user->foto_url ? asset('storage/' . $item->user->foto_url) : null,
                'tipo_afastamento' => $item->tipo,
                'data_inicio' => $item->data_inicio,
                'data_fim' => $item->data_fim,
                'dias_duracao' => $item->dias_duracao,
                'motivo' => $item->motivo,
                'anexo_url' => $item->anexo_url ? asset('storage/' . $item->anexo_url) : null,
                'status_atual' => $item->status_formatado
            ];
        });

        return response()->json($formatados);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tipo' => 'required',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'anexo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120' // Max 5MB
        ]);

        $dados = $request->except('anexo');

        // Upload do Arquivo
        if ($request->hasFile('anexo')) {
            $path = $request->file('anexo')->store('comprovantes_afastamento', 'public');
            $dados['anexo_url'] = $path;
        }

        Afastamento::create($dados);

        return response()->json(['message' => 'Afastamento lançado com sucesso!'], 201);
    }

    public function destroy($id)
    {
        $afastamento = Afastamento::findOrFail($id);
        
        // Deleta o arquivo físico se existir
        if ($afastamento->anexo_url) {
            Storage::disk('public')->delete($afastamento->anexo_url);
        }

        $afastamento->delete();
        return response()->json(['message' => 'Excluído com sucesso']);
    }
}