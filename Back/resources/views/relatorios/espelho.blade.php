<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Folha de Ponto</title>
    <style>
        /* --- CONFIGURAÇÃO DO PAPEL (OFÍCIO / LEGAL) --- */
        @page { 
            size: 216mm 356mm; 
            margin: 0; 
        }

        body { 
            font-family: sans-serif; 
            font-size: 10px; 
            margin: 0; 
            padding: 20px;
            background-color: #525659;
            display: flex;
            justify-content: center;
        }

        /* A FOLHA DE PAPEL */
        .page-container {
            width: 216mm;
            height: 356mm;
            background-color: white;
            padding: 10mm;
            box-sizing: border-box;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            position: relative;
        }
        
        /* --- ESTILOS --- */
        table { width: 100%; border-collapse: collapse; margin-bottom: 3px; table-layout: fixed; /* Importante para fixar larguras */ }
        th, td { border: 1px solid #000; padding: 3px 2px; text-align: center; word-wrap: break-word; }
        
        .header-gray { 
            background-color: #d9d9d9; text-align: center; font-weight: bold; 
            font-size: 14px; padding: 4px; border: 1px solid #000;
        }

        .no-border-table td { border: none; padding: 2px; text-align: left; }
        .info-box { border: 1px solid #000; padding: 4px; margin-bottom: 4px; }
        .label { font-weight: bold; margin-right: 5px; }

        /* --- TABELA PRINCIPAL --- */
        .main-table th { 
            background-color: #d9d9d9; text-transform: uppercase; 
            font-size: 9px; text-align: center; font-weight: bold;
            padding: 5px 0;
        }
        .main-table td { 
            font-size: 10px; height: 16px; 
            vertical-align: middle;
        }
        
        .text-red { color: red; font-weight: bold; }
        .text-small { font-size: 9px; color: #444; }

        /* Rodapé */
        .footer-layout { width: 100%; border: 1px solid #000; margin-top: 0; }
        .footer-layout td { border: none; vertical-align: top; padding: 5px; }
        .border-right { border-right: 1px solid #000 !important; }
        
        .linha-campo {
            border-bottom: 1px solid #000 !important;
            width: 80px; 
            text-align: center;
            height: 14px;
        }
        
        .sig-box { text-align: center; margin-top: 25px; }
        .sig-line { border-top: 1px solid #000; width: 90%; margin: 0 auto; padding-top: 2px; }

        .schedule-table { width: 100%; font-size: 8px; border: none; }
        .schedule-table td { border: none; padding: 1px 0; vertical-align: top; text-align: left; }
        
        .day-col { width: 65px; text-align: right; font-weight: bold; white-space: nowrap; }
        .sep-col { width: 10px; text-align: center; }

        .system-footer {
            position: absolute;
            bottom: 10mm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        @media print {
            body { background: none; padding: 0; display: block; }
            .page-container { 
                width: 100%; height: auto; box-shadow: none; margin: 0; border: none; padding: 0;
            }
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="page-container">

        <div class="header-gray">FOLHA DE PONTO ( {{ $periodo_extenso }} )</div>

        <div class="info-box" style="border-top: none;">
            <table class="no-border-table">
                <tr>
                    <td><span class="label">EMPRESA:</span> LR PROJECT SOLUTIONS LTDA</td>
                    <td align="right">130</td>
                </tr>
                <tr><td colspan="2"><span class="label">CNPJ:</span> 28.966.079/0001-29 - RUA PARQUE ESPERANÇA, CABEDELO</td></tr>
            </table>
        </div>

        <div class="info-box" style="margin-top: -5px; border-top: none;">
            <table class="no-border-table" style="width: 100%;">
                <tr>
                    <td style="width: 55%; vertical-align: top;">
                        <div style="margin-bottom: 3px;">
                            <span class="label">FUNCIONÁRIO:</span> {{ strtoupper($user->name) }} 
                            <span style="margin-left: 10px;">{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div style="margin-bottom: 3px;">
                            <span class="label">ADMISSÃO:</span> {{ date('d/m/Y', strtotime($user->created_at)) }} &nbsp;&nbsp;&nbsp;
                            <span class="label">FUNÇÃO:</span> {{ strtoupper($user->role) }}
                        </div>
                        <div>
                            <span class="label">CTPS:</span> {{ $user->ctps ?? '_____' }}
                        </div>
                    </td>

                    <td style="width: 45%; vertical-align: top; border-left: 1px solid #ccc; padding-left: 5px;">
                        @php
                            $e1 = substr($user->entrada_1 ?? '08:00', 0, 5);
                            $s1 = substr($user->saida_1 ?? '12:00', 0, 5);
                            $e2 = substr($user->entrada_2 ?? '14:00', 0, 5);
                            $s2 = substr($user->saida_2 ?? '18:00', 0, 5);
                            $horario_padrao = "ENT.: $e1 - INT.: $s1 a $e2 - SAÍ.: $s2";
                        @endphp

                        <div style="font-weight: bold; margin-bottom: 2px;">HORÁRIO :</div>
                        
                        <table class="schedule-table">
                            <tr>
                                <td class="day-col">Segunda-Feira</td>
                                <td class="sep-col">:</td>
                                <td>{{ $horario_padrao }}</td>
                            </tr>
                            <tr>
                                <td class="day-col">Terça-Feira</td>
                                <td class="sep-col">:</td>
                                <td>{{ $horario_padrao }}</td>
                            </tr>
                            <tr>
                                <td class="day-col">Quarta-Feira</td>
                                <td class="sep-col">:</td>
                                <td>{{ $horario_padrao }}</td>
                            </tr>
                            <tr>
                                <td class="day-col">Quinta-Feira</td>
                                <td class="sep-col">:</td>
                                <td>{{ $horario_padrao }}</td>
                            </tr>
                            <tr>
                                <td class="day-col">Sexta-Feira</td>
                                <td class="sep-col">:</td>
                                <td>{{ $horario_padrao }}</td>
                            </tr>
                            <tr>
                                <td class="day-col">Sábado</td>
                                <td class="sep-col">:</td>
                                <td>
                                    @if($user->trabalha_sab ?? false)
                                        ENT.: {{ $e1 }} - INT.: {{ $s1 }} a : - SAÍ.:
                                    @else
                                        DSR / Folga
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 10%;">DIA</th>
                    <th style="width: 14%;">ENTRADA</th>
                    <th style="width: 28%;">INTERVALO REFEIÇÃO</th>
                    <th style="width: 14%;">SAÍDA</th>
                    <th style="width: 14%;">HORAS<br>EXTRAS</th>
                    <th style="width: 20%;">HORAS NEGATIVAS / POSITIVAS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($linhas as $linha)
                <tr style="background-color: {{ $linha['bg'] }}">
                    <td align="left">{{ $linha['dia'] }} {{ $linha['semana'] }}</td>
                    <td>{{ $linha['entrada'] }}</td>
                    <td>{{ $linha['intervalo'] }}</td>
                    <td>{{ $linha['saida'] }}</td>
                    <td>{{ $linha['extras'] }}</td>
                    <td class="{{ !empty($linha['negativas']) ? 'text-red' : '' }}" style="text-align: center;">
                        @if($linha['observacao'])
                            <span class="text-small">{{ $linha['observacao'] }}</span>
                        @else
                            {{ $linha['negativas'] }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="header-gray" style="border-bottom: none; font-size: 11px;">RESUMO DE FREQUÊNCIA</div>
        
        <table class="footer-layout">
            <tr>
                <td width="65%" class="border-right">
                    <table class="no-border-table" style="font-size: 9px;">
                        <tr>
                            <td width="90">Nº de faltas justificadas</td>
                            <td width="5">:</td>
                            <td style="border-bottom: 1px solid #000;"></td>
                            <td width="2"></td>
                            <td width="80">Nº de Cesta Básica</td>
                            <td width="5">:</td>
                            <td style="border-bottom: 1px solid #000;"></td>
                            <td width="5"></td>
                        </tr>
                        <tr>
                            <td>Nº de faltas não justificadas</td>
                            <td>:</td>
                            <td class="linha-campo">{{ isset($resumo['faltas']) && $resumo['faltas'] > 0 ? $resumo['faltas'] : '' }}</td>
                            <td></td>
                            <td>Nº de Vale Transporte</td>
                            <td>:</td>
                            <td class="linha-campo"></td>
                        </tr>
                        <tr>
                            <td>Atrasos</td>
                            <td>:</td>
                            <td class="linha-campo">{{ isset($resumo['atrasos']) && $resumo['atrasos'] > 0 ? $resumo['atrasos'] : '' }}</td>
                            <td></td>
                            <td>Nº de Horas Extras</td>
                            <td>:</td>
                            <td class="linha-campo">{{ isset($resumo['extras']) && $resumo['extras'] != '00:00' ? $resumo['extras'] : '' }}</td>
                        </tr>
                        <tr>
                            <td>Atrasos não justificados</td>
                            <td>:</td>
                            <td class="linha-campo"></td>
                            <td></td>
                            <td>Nº de Horas Extras 100%</td>
                            <td>:</td>
                            <td class="linha-campo"></td>
                        </tr>
                        <tr>
                            <td>Nº de Vale Refeição</td>
                            <td>:</td>
                            <td class="linha-campo"></td>
                            <td></td>
                            <td>Nº de Adicional Noturno</td>
                            <td>:</td>
                            <td class="linha-campo"></td>
                        </tr>
                    </table>
                </td>

                <td width="35%" style="padding-left: 5px;">
                    <div style="margin-bottom: 5px; text-align: left; margin-top: 5px;">
                        Data : ________ / ________ / ________
                    </div>

                    <div class="sig-box">
                        <div class="sig-line"></div>
                        Assinatura do Funcionário
                    </div>

                    <div class="sig-box" style="margin-top: 25px;">
                        <div class="sig-line"></div>
                        Assinatura da Chefia
                    </div>
                </td>
            </tr>
        </table>
        
        <div class="system-footer">
            Sistema de Ponto Eletrônico - Gerado em {{ date('d/m/Y H:i') }}
        </div>

    </div>

    <button class="no-print" onclick="window.print()" 
        style="position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; background: #333; color: white; border: none; cursor: pointer; border-radius: 5px;">
        Imprimir / Salvar PDF
    </button>

</body>
</html>