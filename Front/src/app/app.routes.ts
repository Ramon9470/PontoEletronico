import { Routes } from '@angular/router';
import { LoginComponent } from './pages/login/login.component';
import { HomeComponent } from './pages/home/home.component';
import { ListarUsuariosComponent } from './pages/listar-usuarios/listar-usuarios.component';
import { authGuard } from './guards/auth-guard';
import { RegistrarPontoComponent } from './pages/registrar-ponto/registrar-ponto.component';
import { SolicitarAjuste } from './pages/solicitar-ajuste/solicitar-ajuste.component'; 
import { EspelhoPonto } from './pages/espelho-ponto/espelho-ponto.component'; 
import { GerenciarSolicitacoes } from './pages/gerenciar-solicitacoes/gerenciar-solicitacoes.component';
import { GestaoTurnosEscala } from './pages/gestao-turnos-escala/gestao-turnos-escala.component';
import { AfastamentosComponent } from './pages/afastamentos/afastamentos.component';
import { EscalaFolgasComponent } from './pages/escala-folgas/escala-folgas.component';
import { FeriasComponent } from './pages/ferias/ferias.component'; 
import { RelatorioEspelhoComponent } from './pages/relatorios/relatorio-espelho/relatorio-espelho.component';
import { RelatorioAfastamentosComponent } from './pages/relatorios/relatorio-afastamentos/relatorio-afastamentos.component';
import { RelatorioEscalasComponent } from './pages/relatorios/relatorio-escalas/relatorio-escalas.component';
import { RelatorioFeriasComponent } from './pages/relatorios/relatorio-ferias/relatorio-ferias.component';
import { RelatorioBancoHorasComponent } from './pages/relatorios/relatorio-banco-horas/relatorio-banco-horas.component';
import { TelaRegistroComponent } from './pages/tela-registro/tela-registro.component';

export const routes: Routes = [
    { path: '', redirectTo: 'login', pathMatch: 'full' },
    { path: 'login', component: LoginComponent },
    
    // Rotas protegidas
    { path: 'home', component: HomeComponent, canActivate: [authGuard] },  
    { path: 'registrar-ponto', component: RegistrarPontoComponent, canActivate: [authGuard] },  
    { path: 'solicitar-ajuste', component: SolicitarAjuste, canActivate: [authGuard] },    
    
    { path: 'colaboradores', component: ListarUsuariosComponent, canActivate: [authGuard] },    
    { path: 'listar-usuarios', redirectTo: 'colaboradores', pathMatch: 'full' },
    
    { path: 'usuarios/novo', component: TelaRegistroComponent, canActivate: [authGuard] },    
    { path: 'usuarios/editar/:id', component: TelaRegistroComponent, canActivate: [authGuard] },    
    
    { path: 'espelho-ponto', component: EspelhoPonto, canActivate: [authGuard] },    
    
    { path: 'gerenciar-solicitacoes', component: GerenciarSolicitacoes, canActivate: [authGuard] },
    { path: 'turnos', component: GestaoTurnosEscala, canActivate: [authGuard] },
    { path: 'afastamentos', component: AfastamentosComponent, canActivate: [authGuard] },
    { path: 'escala-folgas', component: EscalaFolgasComponent, canActivate: [authGuard] },
    { path: 'ferias', component: FeriasComponent, canActivate: [authGuard] },
    
    // Relatórios
    { path: 'relatorios/espelho', component: RelatorioEspelhoComponent, canActivate: [authGuard] },
    { path: 'relatorios/afastamentos', component: RelatorioAfastamentosComponent, canActivate: [authGuard] },
    { path: 'relatorios/escalas', component: RelatorioEscalasComponent, canActivate: [authGuard] },
    { path: 'relatorios/ferias', component: RelatorioFeriasComponent, canActivate: [authGuard]},
    { path: 'relatorios/banco-horas', component: RelatorioBancoHorasComponent, canActivate: [authGuard] }
];