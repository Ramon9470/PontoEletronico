<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Escalas</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        .header { text-align: center; border: 1px solid #000; padding: 10px; background-color: #f0f0f0; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }
        .filters-info { margin-bottom: 10px; font-size: 11px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 5px; text-align: left; vertical-align: middle; }
        th { background-color: #d9d9d9; font-weight: bold; text-transform: uppercase; font-size: 9px; text-align: center; }
        .badge { padding: 2px 5px; border-radius: 3px; color: #fff; font-size: 8px; font-weight: bold; }
        .bg-blue { background-color: #2563eb; }
        .bg-red { background-color: #dc2626; }
        .dia-box { display: inline-block; width: 15px; height: 15px; line-height: 15px; text-align: center; border-radius: 50%; font-size: 8px; margin: 0 2px; font-weight: bold; border: 1px solid #ccc; }
        .dia-on { background-color: #dcfce7; color: #166534; border-color: #16a34a; }
        .dia-off { background-color: #fee2e2; color: #991b1b; border-color: #ef4444; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Escalas e Turnos de Trabalho</h1>
        <p>LR PROJECT SOLUTIONS LTDA - CNPJ: 28.966.079/0001-29</p>
    </div>

    <div class="filters-info">
        <strong>Departamento:</strong> {{ $filtros['departamento'] }} &nbsp;|&nbsp;
        <strong>Modelo de Escala:</strong> {{ $filtros['modelo'] }} &nbsp;|&nbsp;
        <strong>Situação:</strong> Apenas Ativos
    </div>

    <table>
        <thead>
            <tr>
                <th width="25%">Colaborador</th>
                <th width="20%">Cargo / Departamento</th>
                <th width="10%">Modelo</th>
                <th width="25%">Horário de Trabalho</th>
                <th width="20%">Dias (Sáb / Dom)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dados as $row)
            <tr>
                <td>
                    <strong>{{ strtoupper($row->nome_completo) }}</strong><br>
                    <span style="font-size: 9px; color: #555;">Mat: {{ $row->matricula }}</span>
                </td>
                <td>
                    {{ strtoupper($row->cargo) }}<br>
                    <span style="font-size: 9px; color: #555;">{{ $row->departamento }}</span>
                </td>
                <td style="text-align: center;">
                    @if($row->jornada_nome)
                        <span class="badge bg-blue">{{ $row->escala_label }}</span>
                    @else
                        <span class="badge bg-red">SEM ESCALA</span>
                    @endif
                </td>
                <td style="text-align: center; font-size: 11px;">
                    @if($row->jornada_nome)
                        <strong>{{ $row->ent1 }} às {{ $row->sai1 }}</strong>
                        @if($row->ent2) e {{ $row->ent2 }} às {{ $row->sai2 }} @endif
                        <br><span style="font-size: 9px; color: #666;">({{ $row->jornada_nome }})</span>
                    @else --- @endif
                </td>
                <td style="text-align: center;">
                    @if($row->jornada_nome)
                        <span class="dia-box {{ $row->trabalha_sab ? 'dia-on' : 'dia-off' }}" title="Sábado">S</span>
                        <span class="dia-box {{ $row->trabalha_dom ? 'dia-on' : 'dia-off' }}" title="Domingo">D</span>
                        @if($row->regra_folga)
                            <div style="font-size: 8px; margin-top: 2px;">Folga: {{ $row->regra_folga }}</div>
                        @endif
                    @else - @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align: center; padding: 20px;">Nenhum registro encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; border-top: 1px solid #ccc; padding-top: 5px;">
        <strong>Total Listado:</strong> {{ count($dados) }} Colaboradores
        <span style="float: right;">Gerado em: {{ $data_geracao }}</span>
    </div>
</body>
</html>