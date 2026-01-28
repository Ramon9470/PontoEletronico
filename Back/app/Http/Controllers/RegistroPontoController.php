<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RegistroPonto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RegistroPontoController extends Controller
{
    /**
     * Lista os pontos batidos hoje pelo usuário logado.
     */
    public function getPontosHoje(Request $request)
    {        
        $hoje = Carbon::now('America/Sao_Paulo')->toDateString();
        
        // Retorna os últimos 10 registros do dia para feedback visual na tela de ponto
        $pontos = RegistroPonto::with('usuario')
            ->where('data_registro', $hoje)
            ->orderBy('hora_registro', 'desc')
            ->take(10)
            ->get();
            
        // Formata para o frontend
        $formatted = $pontos->map(function($p) {
             return [
                 'tipo_registro' => $p->tipo_registro,
                 'hora_registro' => $p->hora_registro,
                 'metodo' => $p->metodo,
                 'nome_usuario' => $p->usuario->name ?? 'Desconhecido'
             ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'ip' => $request->ip()
        ]);
    }

    /**
     * Recebe a foto da webcam, envia para a IA e registra o ponto.
     */
    public function registrarFacial(Request $request)
    {
        if (!$request->hasFile('foto')) {
            return response()->json(['message' => 'Nenhuma imagem enviada.'], 400);
        }

        try {
            $foto = $request->file('foto');
            
            // Envia para o container Python
            $urlPython = 'http://python:5000/reconhecer'; 
            
            $response = Http::timeout(120)
                ->attach('imagem', file_get_contents($foto), 'capture.jpg')
                ->post($urlPython);

            if ($response->failed()) {
                Log::error('Falha IA: ' . $response->body());
                return response()->json(['message' => 'Serviço de reconhecimento indisponível.'], 500);
            }

            $resultado = $response->json();

            // Verifica se a IA reconheceu
            if (!isset($resultado['status']) || $resultado['status'] !== 'sucesso') {
                return response()->json(['message' => 'Rosto não reconhecido. Tente novamente.'], 404);
            }

            // Salva o Ponto
            $userId = $resultado['usuario_id'];
            $nomeUsuario = $resultado['nome'];
            $confianca = $resultado['confianca'] ?? 'High';

            return $this->salvarPonto($userId, $nomeUsuario, "Facial ($confianca)");

        } catch (\Exception $e) {
            Log::error("Erro Facial Controller: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao processar biometria.'], 500);
        }
    }

    /**
     * Lógica centralizada de validação e persistência do ponto.
     */
    private function salvarPonto($userId, $nomeUsuario, $metodo)
    {
        $user = User::with('escala')->find($userId);
        if (!$user) return response()->json(['message' => 'Usuário não encontrado.'], 404);

        $agora = Carbon::now('America/Sao_Paulo');
        $hoje = $agora->toDateString();
        
        // Limite de batidas da escala ou padrão 4
        $limitePontos = $user->escala ? $user->escala->limite_batidas : 4;
        
        $qtdHoje = RegistroPonto::where('usuario_id', $userId)
            ->where('data_registro', $hoje)
            ->count();

        if ($qtdHoje >= $limitePontos) {
            return response()->json(['message' => "Sr(a). {$nomeUsuario}, limite diário atingido."], 400);
        }

        // Regra de 5 Minutos
        $ultimoPonto = RegistroPonto::where('usuario_id', $userId)
            ->where('data_registro', $hoje)
            ->orderBy('hora_registro', 'desc')
            ->first();

        if ($ultimoPonto) {
            $ultimaDataHora = Carbon::parse($ultimoPonto->data_registro . ' ' . $ultimoPonto->hora_registro);
            if ($ultimaDataHora->diffInMinutes($agora) < 5) {
                return response()->json(['message' => "Sr(a). {$nomeUsuario}, aguarde 5 minutos entre registros."], 429);
            }
        }

        // Define Tipo de Registro Dinamicamente
        $tipos = ($limitePontos == 2) 
            ? ['Entrada', 'Saída'] 
            : ['Entrada', 'Saída Intervalo', 'Volta Intervalo', 'Saída'];
        
        $tipoRegistro = $tipos[$qtdHoje] ?? 'Extra';

        RegistroPonto::create([
            'usuario_id' => $userId,
            'data_registro' => $hoje,
            'hora_registro' => $agora->toTimeString(),
            'tipo_registro' => $tipoRegistro,
            'metodo' => $metodo
        ]);

        return response()->json([
            'success' => true,
            'usuario' => $nomeUsuario,
            'horario' => $agora->format('H:i'),
            'mensagem' => "{$tipoRegistro} registrada com sucesso!",
        ]);
    }
}