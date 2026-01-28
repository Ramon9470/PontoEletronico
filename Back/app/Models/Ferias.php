<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ferias extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'data_inicio', 'data_fim', 'dias_gozo', 'vender_um_terco'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Calcula o status visual dinamicamente
    public function getStatusVisualAttribute()
    {
        $hoje = Carbon::today();
        $inicio = Carbon::parse($this->data_inicio);
        $fim = Carbon::parse($this->data_fim);

        if ($fim->isPast()) return 'concluido';
        if ($hoje->between($inicio, $fim)) return 'em_andamento';
        return 'programada';
    }
}