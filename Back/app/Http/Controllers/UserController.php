<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'active' => 'nullable',
            'cpf' => 'nullable|string',
            'cargo' => 'nullable|string',
            'departamento' => 'nullable|string',
            'ctps' => 'nullable|string',
            'genero' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'cep' => 'nullable|string',
            'logradouro' => 'nullable|string',
            'numero' => 'nullable|string',
            'complemento' => 'nullable|string',
            'bairro' => 'nullable|string',
            'cidade' => 'nullable|string',
            'uf' => 'nullable|string',
            'jornada_id' => 'nullable',
            'turno_id' => 'nullable',
            'escala_id' => 'nullable'
        ]);

        $data = $validated;
        $data['password'] = Hash::make($validated['password']);
        $data['active'] = filter_var($request->input('active', true), FILTER_VALIDATE_BOOLEAN);

        // Upload da Foto + GERAÇÃO DE BIOMETRIA
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            
            // Tenta gerar biometria ANTES de salvar qualquer coisa
            $resultadoBiometria = $this->gerarBiometriaNaIA($file);

            if (!$resultadoBiometria['sucesso']) {
                return response()->json([
                    'message' => 'Erro na Biometria: ' . $resultadoBiometria['erro']
                ], 422); // Retorna erro para o frontend
            }

            // Se deu certo, salva o arquivo e o código
            $path = $file->store('fotos_perfil', 'public');
            $data['foto_url'] = $path;
            $data['face_encoding'] = $resultadoBiometria['encoding'];
        }

        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|string',
            'active' => 'nullable',
            'cpf' => 'nullable|string',
            'cargo' => 'nullable|string',
            'departamento' => 'nullable|string',
            'ctps' => 'nullable|string',
            'genero' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'cep' => 'nullable|string',
            'logradouro' => 'nullable|string',
            'numero' => 'nullable|string',
            'complemento' => 'nullable|string',
            'bairro' => 'nullable|string',
            'cidade' => 'nullable|string',
            'uf' => 'nullable|string',
            'jornada_id' => 'nullable',
            'turno_id' => 'nullable',
            'escala_id' => 'nullable'
        ]);

        $data = collect($validated)->except(['password', 'foto'])->toArray();
        
        if ($request->has('active')) {
            $data['active'] = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Foto + GERAÇÃO DE BIOMETRIA
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');

            // Valida Biometria Primeiro
            $resultadoBiometria = $this->gerarBiometriaNaIA($file);

            if (!$resultadoBiometria['sucesso']) {
                return response()->json([
                    'message' => 'Foto Inválida: ' . $resultadoBiometria['erro']
                ], 422); 
            }

            // Apaga anterior e salva nova
            if ($user->foto_url && Storage::disk('public')->exists($user->foto_url)) {
                Storage::disk('public')->delete($user->foto_url);
            }
            
            $path = $file->store('fotos_perfil', 'public');
            $data['foto_url'] = $path;
            $data['face_encoding'] = $resultadoBiometria['encoding'];
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroy($id)
    {
        if ($id == 1) return response()->json(['error' => 'Admin não pode ser excluído'], 403);
        $user = User::findOrFail($id);
        if ($user->foto_url) Storage::disk('public')->delete($user->foto_url);
        $user->delete();
        return response()->json(null, 204);
    }

    private function gerarBiometriaNaIA($file)
    {
        try {
            // Timeout para 60s
            $response = Http::timeout(120)->attach(
                'imagem', file_get_contents($file), $file->getClientOriginalName()
            )->post('http://python:5000/gerar_biometria');

            if ($response->successful()) {
                $json = $response->json();
                
                if (isset($json['status']) && $json['status'] === 'sucesso') {
                    return [
                        'sucesso' => true,
                        'encoding' => $json['face_encoding']
                    ];
                } else {
                    // Retorna o erro específico da IA
                    return [
                        'sucesso' => false,
                        'erro' => $json['mensagem'] ?? 'Erro desconhecido na IA.'
                    ];
                }
            }
            
            Log::error('Erro HTTP IA: ' . $response->body());
            return ['sucesso' => false, 'erro' => 'Falha de comunicação com servidor de IA.'];

        } catch (\Exception $e) {
            Log::error('Exceção IA: ' . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro interno ao processar imagem.'];
        }
    }
}