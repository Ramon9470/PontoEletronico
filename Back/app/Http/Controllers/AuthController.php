<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validação
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // Busca o usuário pelo USERNAME
        $user = User::where('username', $credentials['username'])->first();

        // Verifica se Usuário existe e se a Senha bate
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            Log::warning('Falha no login: Credenciais inválidas.', ['user' => $credentials['username']]);
            
            return response()->json([
                'success' => false,
                'message' => 'Usuário ou senha incorretos.'
            ], 401);
        }

        // Verifica se está ativo
        if (!$user->active) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário inativo. Contate o administrador.'
            ], 403);
        }

        // Gera o Token
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Retorna Sucesso
        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'role'  => $user->role,
                'photo' => $user->foto_url
            ]
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }
}