<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escala extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'nome', 'tipo', 'inicio_ciclo', 'regra_folga', 'limite_batidas'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}