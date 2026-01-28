<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoAjuste extends Model
{
    use HasFactory;

    protected $table = 'solicitacao_ajustes';

    protected $fillable = [
        'usuario_id', 
        'data_ocorrido', 
        'tipo_ajuste', 
        'horario', 
        'justificativa', 
        'anexo_url', 
        'status'
    ];

    // Relacionamento inverso
    public function usuario() {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getAnexoUrlAttribute($value)
    {
        if ($value) {
            if (str_contains($value, 'http')) return $value;
            // Força a porta 8000
            return 'http://localhost:8000/storage/' . $value;
        }
        return null;
    }
}