<!DOCTYPE html>
<html>
<head>
    <title>Relatório de Ponto</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="background-color: #f4f4f4; padding: 20px;">
        <div style="background-color: #fff; padding: 20px; border-radius: 5px; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #5a67d8;">Olá!</h2>
            
            <p>Segue em anexo o <strong>Relatório de Espelho de Ponto</strong> referente ao colaborador <strong>{{ $nomeColaborador }}</strong>.</p>
            
            <p>Este documento foi gerado automaticamente pelo sistema <strong>Ponto Eletrônico</strong>.</p>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            
            <p style="font-size: 12px; color: #999;">
                Favor não responder a este e-mail.<br>
                Enviado em {{ date('d/m/Y H:i') }}
            </p>
        </div>
    </div>
</body>
</html>