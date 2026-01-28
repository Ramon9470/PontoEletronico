<?php

use App\Http\Controllers\AfastamentoController;
use App\Http\Controllers\EscalaController;
use App\Http\Controllers\FeriasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SolicitacaoAjusteController;
use App\Http\Controllers\EspelhoPontoController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\RelatorioController;

use App\Http\Controllers\RegistroPontoController;

// Rota pública de Login
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Rotas protegidas - só acessa se tiver logado
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ROTAS DE USUÁRIOS
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // ROTAS DE TURNOS
    Route::get('/turnos', [TurnoController::class, 'index']);
    Route::post('/turnos', [TurnoController::class, 'store']);
    Route::put('/turnos/{id}', [TurnoController::class, 'update']);
    Route::delete('/turnos/{id}', [TurnoController::class, 'destroy']);
    Route::patch('/turnos/{id}/status', [TurnoController::class, 'status']);
    Route::get('/turnos/{id}/colaboradores', [TurnoController::class, 'colaboradores']);

    // ROTAS DE SOLICITAÇÕES E ESPELHO
    Route::get('/solicitacoes', [SolicitacaoAjusteController::class, 'index']);
    Route::post('/solicitacoes', [SolicitacaoAjusteController::class, 'store']);
    
    // Listar todas as pendências para o Admin
    Route::get('/admin/solicitacoes', [SolicitacaoAjusteController::class, 'listarPendentes']);
    // Aprovar ou Recusar
    Route::patch('/solicitacoes/{id}/status', [SolicitacaoAjusteController::class, 'atualizarStatus']);

    Route::get('/espelho-ponto', [EspelhoPontoController::class, 'getEspelho']);
    Route::middleware('auth:sanctum')->get('/espelho-ponto', [EspelhoPontoController::class, 'getEspelho']);

    Route::get('/afastamentos', [AfastamentoController::class, 'index']);
    Route::post('/afastamentos', [AfastamentoController::class, 'store']);
    Route::delete('/afastamentos/{id}', [AfastamentoController::class, 'destroy']);

    Route::get('/escalas', [EscalaController::class, 'index']);
    Route::post('/escalas', [EscalaController::class, 'store']);
    Route::delete('/escalas/{id}', [EscalaController::class, 'destroy']);

    Route::get('/ferias', [FeriasController::class, 'index']);
    Route::post('/ferias', [FeriasController::class, 'store']);
    Route::delete('/ferias/{id}', [FeriasController::class, 'destroy']);

    // ROTAS DE RELATÓRIOS
    Route::get('/reports/mirror', [RelatorioController::class, 'gerarEspelho']); 
    Route::post('/reports/send-email', [RelatorioController::class, 'enviarEmail']);
    Route::get('/reports/leaves', [RelatorioController::class, 'gerarRelatorioAfastamentos']);
    Route::get('/reports/scales', [RelatorioController::class, 'gerarRelatorioEscalas']);
    Route::get('/reports/vacations', [RelatorioController::class, 'gerarRelatorioFerias']);
    Route::get('/reports/hours-bank', [RelatorioController::class, 'gerarRelatorioBancoHoras']);

    // ROTAS REGISTRO DE PONTO
    Route::get('/points/today', [RegistroPontoController::class, 'getPontosHoje']);
    Route::post('/points/facial', [RegistroPontoController::class, 'registrarFacial']);
});