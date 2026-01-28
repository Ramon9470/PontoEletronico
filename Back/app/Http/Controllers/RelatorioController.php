<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Afastamento;
use App\Models\Turno;
use App\Models\Ferias;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\RelatorioPontoMail;
use App\Mail\RelatorioAfastamentoMail;
use App\Mail\RelatorioEscalaMail;
use App\Mail\RelatorioFeriasMail;
use App\Mail\RelatorioBancoHorasMail;

class RelatorioController extends Controller
{
    // RELATÓRIO DE ESPELHO DE PONTO
    public function gerarEspelho(Request $request)
    {
        $request->validate([
            'employee' => 'required|exists:users,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date'
        ]);

        $user = User::findOrFail($request->employee);
        $inicio = Carbon::parse($request->startDate);
        $fim = Carbon::parse($request->endDate);

        $periodo_extenso = $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');

        $afastamentos = Afastamento::where('user_id', $user->id)
            ->whereBetween('data_inicio', [$inicio, $fim])
            ->get();

        $linhas = [];
        $atual = $inicio->copy();
        
        $resumo = ['faltas' => 0, 'atrasos' => 0, 'extras' => '00:00'];
        
        while ($atual <= $fim) {
            $isFimDeSemana = $atual->isWeekend();
            
            $temAfastamento = $afastamentos->first(function($a) use ($atual) {
                return $atual->between($a->data_inicio, $a->data_fim);
            });

            $bg = '#ffffff'; 
            $observacao = '';
            
            if ($isFimDeSemana) {
                $bg = '#f0f0f0'; $observacao = 'DSR / Folga';
            }

            if ($temAfastamento) {
                $bg = '#fff0f0'; $observacao = $temAfastamento->tipo; 
            }

            $linhas[] = [
                'dia' => $atual->format('d/m'),
                'semana' => $this->getDiaSemanaAbreviado($atual),
                'bg' => $bg,
                'entrada' => ($isFimDeSemana || $temAfastamento) ? '' : '08:00',
                'intervalo' => ($isFimDeSemana || $temAfastamento) ? '' : '12:00 a 13:00',
                'saida' => ($isFimDeSemana || $temAfastamento) ? '' : '17:00',
                'extras' => '', 'negativas' => '', 'observacao' => $observacao
            ];

            $atual->addDay();
        }

        return view('relatorios.espelho', compact('user', 'linhas', 'periodo_extenso', 'resumo'));
    }

    // RELATÓRIO DE AFASTAMENTOS
    public function gerarRelatorioAfastamentos(Request $request)
    {
        $request->validate(['startDate' => 'required|date', 'endDate' => 'required|date']);

        $inicio = Carbon::parse($request->startDate);
        $fim = Carbon::parse($request->endDate);

        $dados = Afastamento::with('user')
            ->whereBetween('data_inicio', [$inicio, $fim])
            ->orWhereBetween('data_fim', [$inicio, $fim])
            ->orderBy('data_inicio')->get();

        $periodo = $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');

        return view('relatorios.afastamentos', compact('dados', 'periodo'));
    }

    // RELATÓRIO DE ESCALAS
    public function gerarRelatorioEscalas(Request $request)
    {
        $users = User::with(['escala', 'turno'])->get();

        $dados = $users->map(function ($u) {
            
            $trabalhaSab = true; 
            $trabalhaDom = true;
            $regraFolga = '';
            $escalaLabel = 'PENDENTE';
            $jornadaNome = null; 
            
            $ent1 = ''; $sai1 = ''; $ent2 = ''; $sai2 = '';

            if ($u->escala) {
                $escalaLabel = $u->escala->tipo;
                $regraFolga = $u->escala->regra_folga;

                if (stripos($u->escala->tipo, '5x2') !== false) {
                     $trabalhaSab = false; $trabalhaDom = false;
                }
                if (stripos($regraFolga, 'Sábado') !== false) $trabalhaSab = false;
                if (stripos($regraFolga, 'Domingo') !== false) $trabalhaDom = false;
            } else {
                $trabalhaSab = false; $trabalhaDom = false;
            }

            if ($u->turno) {
                $jornadaNome = $u->turno->nome;
                $ent1 = substr($u->turno->entrada_1, 0, 5);
                $sai1 = substr($u->turno->saida_1, 0, 5);
                $ent2 = $u->turno->entrada_2 ? substr($u->turno->entrada_2, 0, 5) : '';
                $sai2 = $u->turno->saida_2 ? substr($u->turno->saida_2, 0, 5) : '';
            }

            return (object) [
                'nome_completo' => $u->name,
                'matricula' => str_pad($u->id, 4, '0', STR_PAD_LEFT),
                'cargo' => $u->role,
                'departamento' => $u->departamento ?? 'Geral',
                'jornada_nome' => $jornadaNome,
                'escala_label' => $escalaLabel,
                'ent1' => $ent1, 'sai1' => $sai1,
                'ent2' => $ent2, 'sai2' => $sai2,
                'trabalha_sab' => $trabalhaSab,
                'trabalha_dom' => $trabalhaDom,
                'regra_folga' => $regraFolga
            ];
        });

        $filtros = ['departamento' => 'Todos', 'modelo' => 'Todos'];
        $data_geracao = Carbon::now('America/Sao_Paulo')->format('d/m/Y H:i');

        return view('relatorios.escalas', compact('dados', 'filtros', 'data_geracao'));
    }

    // RELATÓRIO DE FÉRIAS
    public function gerarRelatorioFerias(Request $request)
    {
        // Busca na tabela ferias trazendo o usuário junto
        $query = Ferias::with('user'); 

        $registros = $query->get();

        $dados = $registros->map(function ($f) {
            $inicio = Carbon::parse($f->data_inicio);
            $fim = Carbon::parse($f->data_fim);
            $hoje = Carbon::now();

            // Lógica de Status
            $status = 'programada';
            $statusLabel = 'Programada';

            if ($fim->isPast()) {
                $status = 'concluida';
                $statusLabel = 'Concluída';
            } elseif ($hoje->between($inicio, $fim)) {
                $status = 'em_andamento';
                $statusLabel = 'Em Andamento';
            }

            return (object) [
                'nome_completo' => $f->user->name ?? '---',
                'matricula' => str_pad($f->user->id, 4, '0', STR_PAD_LEFT),
                'cargo' => $f->user->role ?? '-',
                'departamento' => $f->user->departamento ?? 'Geral',
                'status' => $status,
                'status_label' => $statusLabel,
                'inicio_fmt' => $inicio->format('d/m/Y'),
                'fim_fmt' => $fim->format('d/m/Y'),
                'dias_gozo' => $f->dias_gozo,
                'vender_um_terco' => $f->vender_um_terco,
                'aquisitivo' => $f->periodo_aquisitivo ?? ( $inicio->subYear()->format('Y') . '/' . $inicio->format('Y') )
            ];
        });

        if ($request->status && $request->status !== 'Todos') {
            $dados = $dados->where('status', $request->status);
        }

        $filtros = ['status' => $request->status ?? 'Todos'];
        $data_geracao = Carbon::now('America/Sao_Paulo')->format('d/m/Y H:i');

        return view('relatorios.ferias', compact('dados', 'filtros', 'data_geracao'));
    }

    public function gerarRelatorioBancoHoras(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date'
        ]);

        $inicio = Carbon::parse($request->startDate);
        $fim = Carbon::parse($request->endDate);

        $query = User::with(['escala', 'turno']);

        // Filtro por Departamento
        if ($request->departamento && $request->departamento !== 'Todos') {
            $query->where('departamento', $request->departamento);
        }

        // Filtro por Colaborador Específico
        if ($request->employee && $request->employee !== 'Todos') {
            $query->where('id', $request->employee);
        }

        $users = $query->get();

        // Busca afastamentos
        $afastamentos = Afastamento::whereBetween('data_inicio', [$inicio, $fim])->get();

        $dados = $users->map(function ($u) use ($inicio, $fim, $afastamentos) {
            $diasUteis = $inicio->diffInWeekdays($fim) + 1;
            $afastamentosUser = $afastamentos->where('user_id', $u->id)->count();
            
            $presencas = $diasUteis - $afastamentosUser;
            $faltas = $afastamentosUser;
            
            $saldoFormatado = '00:00';
            $statusClass = 'text-green';

            return [
                'nome' => $u->name,
                'departamento' => $u->departamento ?? 'Geral',
                'cargo' => $u->role,
                'dias_trabalhados' => max(0, $presencas),
                'dias_faltas' => $faltas,
                'saldo_formatado' => $saldoFormatado,
                'status_class' => $statusClass
            ];
        });

        // Atualiza a descrição dos filtros para aparecer no PDF
        $filtros = [
            'departamento' => $request->departamento ?? 'Todos',
            // Se tiver selecionado um funcionário, mostra o nome dele (buscando na coleção) ou 'Todos'
            'colaborador' => ($request->employee && $request->employee !== 'Todos') 
                             ? ($users->first()->name ?? 'Todos') 
                             : 'Todos'
        ];
        
        $periodo = $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');
        $data_geracao = Carbon::now('America/Sao_Paulo')->format('d/m/Y H:i');

        return view('relatorios.banco_horas', compact('dados', 'filtros', 'periodo', 'data_geracao'));
    }

    // ENVIO DE E-MAIL
    public function enviarEmail(Request $request)
    {
        $request->validate([
            'email_destino' => 'required|email',
            'file' => 'required|file',
            'tipo' => 'nullable|string' 
        ]);

        try {
            $mailable = null;

            if ($request->tipo === 'afastamentos') {
                $mailable = new RelatorioAfastamentoMail($request->file('file'));
            } 
            elseif ($request->tipo === 'escalas') {
                $mailable = new RelatorioEscalaMail($request->file('file'));
            } 
            elseif ($request->tipo === 'ferias') {
                $mailable = new RelatorioFeriasMail($request->file('file'));
            } 
            elseif ($request->tipo === 'banco_horas') {
                $mailable = new RelatorioBancoHorasMail($request->file('file'));
            } 
            else {
                // Espelho de Ponto
                $nome = $request->employee_name ?? 'Colaborador';
                $mailable = new RelatorioPontoMail($request->file('file'), $nome);
            }

            Mail::to($request->email_destino)->send($mailable);
            
            return response()->json(['message' => 'E-mail enviado com sucesso!']);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    // Helper
    private function getDiaSemanaAbreviado($data) {
        $map = ['Sunday' => 'Dom', 'Monday' => 'Seg', 'Tuesday' => 'Ter', 'Wednesday' => 'Qua', 'Thursday' => 'Qui', 'Friday' => 'Sex', 'Saturday' => 'Sáb'];
        return $map[$data->format('l')] ?? '';
    }
}