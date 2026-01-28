<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Banco de Horas</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { 
            text-align: center; border: 1px solid #000; padding: 10px; 
            background-color: #f0f0f0; margin-bottom: 15px;
        }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
        th { background-color: #d9d9d9; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-green { color: #166534; font-weight: bold; } /* Verde Escuro */
        .text-red { color: #dc2626; font-weight: bold; }   /* Vermelho */
        
        .footer { margin-top: 20px; font-size: 10px; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Extrato Consolidado de Banco de Horas</h1>
        <p>Período de Apuração: {{ $periodo }}</p>
    </div>

    <div style="margin-bottom: 10px;">
        <strong>Departamento:</strong> {{ $filtros['departamento'] == 'Todos os Departamentos' ? 'Geral' : $filtros['departamento'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="30%">Colaborador</th>
                <th width="20%">Departamento</th>
                <th width="15%">Cargo</th>
                <th width="10%" class="text-center">Presenças</th>
                <th width="10%" class="text-center">Faltas</th>
                <th width="15%" class="text-right">Saldo Acumulado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dados as $row)
            <tr>
                <td><strong>{{ strtoupper($row['nome']) }}</strong></td>
                <td>{{ $row['departamento'] }}</td>
                <td>{{ $row['cargo'] }}</td>
                <td class="text-center">{{ $row['dias_trabalhados'] }}</td>
                <td class="text-center">{{ $row['dias_faltas'] }}</td>
                <td class="text-right {{ $row['status_class'] }}">
                    {{ $row['saldo_formatado'] }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #777;">
                    Nenhum registro encontrado para os filtros selecionados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Gerado em: {{ $data_geracao }} (Horário de Brasília)
        <span style="float: right;">LR PROJECT SOLUTIONS LTDA</span>
    </div>

</body>
</html>