<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'password', 'role', 'active', 'avatar_url', 'email',
        'cpf', 'rg', 'data_nascimento', 'genero', 'telefone', 'whatsapp',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf',
        'matricula', 'cargo', 'departamento', 'data_admissao', 'ctps',
        'foto_url', 'jornada_id', 'turno_id', 'perfil', 'escala_id', 'tipo_escala',
        'face_encoding', 'saldo_banco_horas'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean'
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute($value)
    {
        if ($value) {
            if (str_contains($value, 'http')) {
                return $value;
            }
            return asset('storage/' . $value);
        }
        return null;
    }

    public function escala()
    {
        return $this->belongsTo(Escala::class);
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }
}