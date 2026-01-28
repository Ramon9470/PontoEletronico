<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Afastamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'tipo', 'data_inicio', 'data_fim', 'motivo', 'anexo_url', 'status'
    ];

    // Relacionamento com Usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Acessor para calcular dias de duração automaticamente
    public function getDiasDuracaoAttribute()
    {
        $inicio = Carbon::parse($this->data_inicio);
        $fim = Carbon::parse($this->data_fim);
        return $inicio->diffInDays($fim) + 1; // +1 para contar o dia inicial
    }

    // Acessor para Status formatado
    public function getStatusFormatadoAttribute()
    {
        $hoje = Carbon::today();
        $fim = Carbon::parse($this->data_fim);

        if ($fim->isPast()) return 'Concluído';
        if ($hoje->between($this->data_inicio, $this->data_fim)) return 'Em andamento';
        return 'Programado';
    }
}