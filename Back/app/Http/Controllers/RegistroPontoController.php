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
	$user = auth()->user();

	$query = RegistroPonto::with('usuario')->where('data_registro', $hoje);
	
	if ($user->role !== 'admin' && $user->role !== 'gestor'){
		$query->where('usuario_id', $user->id);
	}
        
        // Retorna os últimos 10 registros do dia para feedback visual na tela de ponto
        $pontos = RegistroPonto::with('usuario')
            ->where('data_registro', $hoje)
            ->orderBy('hora_registro', 'desc')
            ->take(20)
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
        // Quem está logado no sistema?
        $usuarioLogado = auth()->user();

        // Se o logado for ADMIN ou RH, permitimos para testes ou registro manual supervisionado
        if ($usuarioLogado && $usuarioLogado->id != $userIdDaIA) {
             if ($usuarioLogado->role !== 'admin' && $usuarioLogado->role !== 'gestor') {
                 return response()->json([
                     'message' => "Atenção: A biometria reconheceu {$nomeUsuario}, mas o usuário logado é diferente."
                 ], 403);
             }
        }

        $user = User::with('escala')->find($userIdDaIA);
        if (!$user) return response()->json(['message' => 'Usuário reconhecido não encontrado no banco.'], 404);

        $agora = Carbon::now('America/Sao_Paulo');
        $hoje = $agora->toDateString();
        
        // 2. Correção da Regra de 5 Minutos
        $ultimoPonto = RegistroPonto::where('usuario_id', $userIdDaIA)
            ->where('data_registro', $hoje)
            ->orderBy('hora_registro', 'desc')
            ->first();

        if ($ultimoPonto) {
            // Cria um objeto Carbon confiável com a Data HOJE + Hora do Registro Anterior
            $dataHoraUltimo = Carbon::createFromFormat('Y-m-d H:i:s', $ultimoPonto->data_registro . ' ' . $ultimoPonto->hora_registro, 'America/Sao_Paulo');
            
            // Diferença absoluta em minutos
            $diffMinutos = $dataHoraUltimo->diffInMinutes($agora);

            if ($diffMinutos < 5) {
                $tempoRestante = 5 - $diffMinutos;
                return response()->json([
                    'message' => "Aguarde {$tempoRestante} minutos para registrar novamente."
                ], 429); // Código HTTP 429 = Too Many Requests
            }
        }

        // Limite de batidas da escala ou padrão 4
        $limitePontos = $user->escala ? $user->escala->limite_batidas : 4;
        
        $qtdHoje = RegistroPonto::where('usuario_id', $userIdDaIA)
            ->where('data_registro', $hoje)
            ->count();

        if ($qtdHoje >= $limitePontos) {
            return response()->json(['message' => "Sr(a). {$nomeUsuario}, limite diário atingido."], 400);
        }

        // Define Tipo de Registro Dinamicamente
        $tipos = ($limitePontos == 2) 
            ? ['Entrada', 'Saída'] 
            : ['Entrada', 'Saída Intervalo', 'Volta Intervalo', 'Saída'];
        
        $tipoRegistro = $tipos[$qtdHoje] ?? 'Extra';

        RegistroPonto::create([
            'usuario_id' => $userIdDaIA,
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
