<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Férias</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        
        .header { 
            text-align: center; border: 1px solid #000; padding: 10px; 
            background-color: #f0f0f0; margin-bottom: 15px;
        }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 5px; text-align: left; vertical-align: middle; }
        th { background-color: #d9d9d9; font-weight: bold; text-transform: uppercase; font-size: 9px; }

        .badge { padding: 2px 5px; border-radius: 3px; color: #fff; font-size: 9px; font-weight: bold; }
        .bg-vencida { background-color: #dc2626; } /* Vermelho */
        .bg-programada { background-color: #2563eb; } /* Azul */
        .bg-concluida { background-color: #16a34a; } /* Verde */
        .bg-andamento { background-color: #d97706; } /* Laranja */

        .center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Relatório de Controle de Férias</h1>
        <p>LR PROJECT SOLUTIONS LTDA - CNPJ: 28.966.079/0001-29</p>
    </div>

    <div style="margin-bottom: 10px; font-size: 11px;">
        <strong>Filtro de Situação:</strong> {{ $filtros['status'] == 'Todos' ? 'Geral' : $filtros['status'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="25%">Colaborador</th>
                <th width="15%">Cargo / Depto</th>
                <th width="10%" class="center">Status</th>
                <th width="10%" class="center">Início Gozo</th>
                <th width="10%" class="center">Fim Gozo</th>
                <th width="5%" class="center">Dias</th>
                <th width="10%" class="center">Abono (1/3)</th>
                <th width="15%" class="center">Período Aquisitivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dados as $row)
            <tr>
                <td>
                    <strong>{{ strtoupper($row->nome_completo) }}</strong><br>
                    <span style="color: #555; font-size: 9px;">Mat: {{ $row->matricula }}</span>
                </td>
                <td>
                    {{ $row->cargo }}<br>
                    <span style="font-size: 9px; color: #555;">{{ $row->departamento }}</span>
                </td>
                
                <td class="center">
                    <span class="badge 
                        @if($row->status == 'vencida') bg-vencida
                        @elseif($row->status == 'concluida') bg-concluida
                        @elseif($row->status == 'em_andamento') bg-andamento
                        @else bg-programada @endif">
                        {{ strtoupper($row->status_label) }}
                    </span>
                </td>

                <td class="center">{{ $row->inicio_fmt }}</td>
                <td class="center">{{ $row->fim_fmt }}</td>
                <td class="center"><strong>{{ $row->dias_gozo }}</strong></td>
                
                <td class="center">
                    {{ $row->vender_um_terco ? 'SIM' : 'NÃO' }}
                </td>

                <td class="center">
                    {{ $row->aquisitivo }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="center" style="padding: 20px; color: #777;">
                    Nenhum registro de férias encontrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; border-top: 1px solid #ccc; padding-top: 5px;">
        <strong>Total de Registros:</strong> {{ count($dados) }}
        <span style="float: right;">Gerado em: {{ $data_geracao }}</span>
    </div>

</body>
</html>