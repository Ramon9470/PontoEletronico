<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroPonto extends Model
{
    protected $table = 'registros_ponto';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'data_registro',
        'hora_registro',
        'tipo_registro',
        'metodo',
        'ip_origem',
        'justificativa'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}