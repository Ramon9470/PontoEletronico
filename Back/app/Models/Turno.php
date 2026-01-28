<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'entrada',
        'saida',
        'intervalo',
        'dias',
        'status'
    ];

    // Converte automaticamente o JSON do banco para Array no PHP e vice-versa
    protected $casts = [
        'dias' => 'array',
    ];

    // Um turno tem vários usuários
    public function users()
    {
        return $this->hasMany(User::class);
    }
}