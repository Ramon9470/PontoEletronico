<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Afastamentos</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        
        .header { 
            text-align: center; border: 1px solid #000; padding: 10px; 
            background-color: #f0f0f0; margin-bottom: 15px;
        }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }

        .filters-info { margin-bottom: 10px; font-size: 11px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 5px; text-align: left; }
        
        th { background-color: #d9d9d9; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        
        /* Larguras das Colunas */
        .col-nome { width: 25%; }
        .col-cargo { width: 15%; }
        .col-tipo { width: 10%; }
        .col-data { width: 8%; text-align: center; }
        .col-dias { width: 5%; text-align: center; }
        .col-obs  { width: 29%; }

        .badge { 
            padding: 2px 5px; border-radius: 3px; color: #fff; font-size: 8px; font-weight: bold; text-transform: uppercase;
        }
        /* Cores baseadas no tipo */
        .bg-atestado { background-color: #eab308; color: #000; } 
        .bg-maternidade { background-color: #ec4899; } 
        .bg-suspensao { background-color: #ef4444; } 
        .bg-ferias { background-color: #3b82f6; }
        .bg-outros { background-color: #6b7280; } 
        
    </style>
</head>
<body>

    <div class="header">
        <h1>Relatório de Afastamentos e Absenteísmo</h1>
        <p>LR PROJECT SOLUTIONS LTDA - CNPJ: 28.966.079/0001-29</p>
    </div>

    <div class="filters-info">
        <strong>Período:</strong> {{ $periodo }} &nbsp;|&nbsp;
        <strong>Gerado em:</strong> {{ \Carbon\Carbon::now('America/Sao_Paulo')->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-nome">Colaborador</th>
                <th class="col-cargo">Cargo/Função</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-data">Início</th>
                <th class="col-data">Fim</th>
                <th class="col-dias">Dias</th>
                <th class="col-obs">Motivo / Observação</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dados as $row)
            <tr>
                <td>
                    <strong>{{ strtoupper($row->user->name) }}</strong><br>
                    <span style="font-size: 9px; color: #555;">ID: {{ str_pad($row->user->id, 4, '0', STR_PAD_LEFT) }}</span>
                </td>
                <td>{{ strtoupper($row->user->role) }}</td>
                
                <td>
                    <span class="badge 
                        @if(stripos($row->tipo, 'atestado') !== false) bg-atestado
                        @elseif(stripos($row->tipo, 'maternidade') !== false) bg-maternidade
                        @elseif(stripos($row->tipo, 'suspensao') !== false) bg-suspensao
                        @elseif(stripos($row->tipo, 'férias') !== false) bg-ferias
                        @else bg-outros @endif">
                        {{ $row->tipo }}
                    </span>
                </td>

                <td class="col-data">{{ \Carbon\Carbon::parse($row->data_inicio)->format('d/m/y') }}</td>
                <td class="col-data">{{ \Carbon\Carbon::parse($row->data_fim)->format('d/m/y') }}</td>
                <td class="col-dias">
                    {{ \Carbon\Carbon::parse($row->data_inicio)->diffInDays(\Carbon\Carbon::parse($row->data_fim)) + 1 }}
                </td>
                
                <td style="font-size: 9px;">
                    {{ $row->motivo ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #777;">
                    Nenhum afastamento encontrado para este período.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; border-top: 1px solid #ccc; padding-top: 5px;">
        <strong>Total de Registros:</strong> {{ count($dados) }}
    </div>

</body>
</html>