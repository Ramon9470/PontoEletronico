<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RelatorioFeriasMail extends Mailable
{
    use Queueable, SerializesModels;

    public $arquivo;

    public function __construct($arquivo)
    {
        $this->arquivo = $arquivo;
    }

    public function build()
    {
        return $this->subject('Relatório de Controle de Férias')
                    ->view('emails.relatorio_geral')
                    ->attach($this->arquivo->getRealPath(), [
                        'as' => $this->arquivo->getClientOriginalName(),
                        'mime' => $this->arquivo->getMimeType(),
                    ]);
    }
}