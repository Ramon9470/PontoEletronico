<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RelatorioPontoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $arquivo;
    public $nomeColaborador;

    // Recebe o arquivo e o nome do funcionário
    public function __construct($arquivo, $nomeColaborador)
    {
        $this->arquivo = $arquivo;
        $this->nomeColaborador = $nomeColaborador;
    }

    public function build()
    {
        // Define o Assunto e aponta para a View
        return $this->subject('Relatório de Ponto - ' . $this->nomeColaborador)
                    ->view('emails.relatorio')
                    ->attach($this->arquivo->getRealPath(), [
                        'as' => $this->arquivo->getClientOriginalName(),
                        'mime' => $this->arquivo->getMimeType(),
                    ]);
    }
}