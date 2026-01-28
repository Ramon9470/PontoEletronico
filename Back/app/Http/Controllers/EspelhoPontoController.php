<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RegistroPonto;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class EspelhoPontoController extends Controller
{
    public function getEspelho(Request $request)
    {
        $userId = $request->input('user_id');
        $mes = $request->input('mes', date('m'));
        $ano = $request->input('ano', date('Y'));

        if (!$userId) {
            $userId = $request->user()->id; 
        }

        $user = User::find($userId);
        if (!$user) return response()->json(['error' => 'Usuário não encontrado'], 404);

        $inicioMes = Carbon::createFromDate($ano, $mes, 1)->startOfMonth();
        $fimMes = $inicioMes->copy()->endOfMonth();

        $registros = RegistroPonto::where('usuario_id', $userId)
            ->whereBetween('data_registro', [$inicioMes->format('Y-m-d'), $fimMes->format('Y-m-d')])
            ->orderBy('data_registro')
            ->orderBy('hora_registro')
            ->get()
            ->groupBy('data_registro');

        $records = [];
        $periodo = CarbonPeriod::create($inicioMes, $fimMes);

        foreach ($periodo as $date) {
            $diaString = $date->format('Y-m-d');
            $pontosDoDia = isset($registros[$diaString]) ? $registros[$diaString] : collect([]);

            $horarios = $pontosDoDia->pluck('hora_registro')->map(function($hora) {
                return substr($hora, 0, 5); 
            })->toArray();
            
            $ent1 = $horarios[0] ?? null;
            $sai1 = $horarios[1] ?? null;
            $ent2 = $horarios[2] ?? null;
            $sai2 = $horarios[3] ?? null;

            $status = 'ok';
            if ($date->isWeekend()) {
                $status = 'folga';
            } elseif (empty($horarios) && $date->isPast()) {
                $status = 'falta';
            } elseif (count($horarios) % 2 != 0) {
                $status = 'atraso';
            }

            $records[] = [
                'date' => $date->format('d/m/Y'),
                'dia_semana' => $date->locale('pt-BR')->dayName,
                'status' => $status,
                'previsto' => '08:00',
                'ent1' => $ent1 ? $ent1 : '---',
                'sai1' => $sai1 ? $sai1 : '---',
                'ent2' => $ent2 ? $ent2 : '---',
                'sai2' => $sai2 ? $sai2 : '---',
                'saldo' => '00:00' 
            ];
        }

        $fotoUrl = null;
        if ($user->foto_url) {
            if (!str_starts_with($user->foto_url, 'http')) {
                $fotoUrl = asset('storage/' . $user->foto_url);
            } else {
                $fotoUrl = $user->foto_url;
            }
        }

        return response()->json([
            'collaborator' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role ?? $user->cargo ?? 'Colaborador',
                'photo' => $fotoUrl
            ],
            'summary' => [
                'workedHours' => '00:00',
                'targetHours' => '176:00',
                'extrasDay' => '00:00',
                'extrasNight' => '00:00'
            ],
            'records' => $records
        ]);
    }
}