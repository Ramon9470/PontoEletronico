import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { TurnoService } from '../../services/turno.service';
import { UserService } from '../../services/user.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-gestao-turnos-escala',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './gestao-turnos-escala.component.html',
  styleUrls: ['./gestao-turnos-escala.component.scss']
})
export class GestaoTurnosEscala implements OnInit {

  private turnoService = inject(TurnoService);
  private userService = inject(UserService);
  private authService = inject(AuthService);
  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);

  isLoading = false;
  isSaving = false;
  isModalOpen = false;
  isCollaboratorsModalOpen = false;
  isEditing = false;
  editingId: number | null = null;
  activeMenuId: number | null = null;

  todosTurnos: any[] = [];
  turnosFiltrados: any[] = [];
  listaFuncionarios: any[] = [];
  listaColaboradoresTurno: any[] = [];
  nomeTurnoAtual: string = '';
  termoBusca: string = '';
  
  stats = { ativos: 0, escalados: 0 };

  novoTurno: any = {
    nome: '', 
    entrada: '', 
    saida: '', 
    intervalo: '', 
    usuarioId: '',
    dias: { D: false, S: true, T: true, Q: true, QI: true, SX: true, SA: false }
  };

  ngOnInit() {
    this.verificarAcesso();
    this.carregarTurnos();
    this.carregarFuncionarios();
  }

  verificarAcesso() {
    if (!this.authService.isLoggedIn()) { 
        this.router.navigate(['/login']); 
    }
  }

  carregarTurnos() {
    this.isLoading = true;
    this.turnoService.listarTurnos().subscribe({
      next: (dados: any[]) => {
        this.todosTurnos = dados.map(turno => ({
          ...turno,
          diasArray: this.transformarDiasEmArray(turno.dias) 
        }));
        this.aplicarFiltros();
        this.calcularEstatisticas();
        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Erro ao carregar turnos', err);
        this.isLoading = false;
      }
    });
  }

  carregarFuncionarios() {
    this.userService.getAll().subscribe({
        next: (dados) => this.listaFuncionarios = dados,
        error: (err) => console.error('Erro ao buscar funcionários', err)
    });
  }

  transformarDiasEmArray(objDias: any): string[] {
    if (!objDias) return [];
    if (typeof objDias === 'string') {
      try { objDias = JSON.parse(objDias); } catch (e) { return []; }
    }
    const mapa = [
      { chaves: ['D', 'dom'], letra: 'D' },
      { chaves: ['S', 'seg'], letra: 'S' },
      { chaves: ['T', 'ter'], letra: 'T' },
      { chaves: ['Q', 'qua'], letra: 'Q' },
      { chaves: ['QI', 'qui'], letra: 'Q' },
      { chaves: ['SX', 'sex'], letra: 'S' },
      { chaves: ['SA', 'sab'], letra: 'S' }
    ];
    return mapa.map(item => {
      const isAtivo = item.chaves.some(chave => {
        const valor = objDias[chave];
        return valor === true || valor === 'true' || valor === 1 || valor === '1';
      });
      return isAtivo ? item.letra : '-';
    });
  }

  diaEstaAtivo(dia: string): boolean { return dia !== '-'; }

  calcularEstatisticas() {
    this.stats.ativos = this.todosTurnos.filter(t => t.status === 'ativo').length;
    this.stats.escalados = this.todosTurnos.reduce((acc, curr) => acc + (curr.qtd_colaboradores || 0), 0);
  }

  aplicarFiltros() {
    let temp = this.todosTurnos;
    if (this.termoBusca) {
        temp = temp.filter(t => t.nome.toLowerCase().includes(this.termoBusca.toLowerCase()));
    }
    this.turnosFiltrados = temp;
  }

  alternarMenu(id: number, event: Event) {
    event.stopPropagation();
    this.activeMenuId = this.activeMenuId === id ? null : id;
  }

  getClassBadge(status: string) { return status === 'ativo' ? 'status-active' : 'status-inactive'; }

  abrirModal() {
    this.isEditing = false;
    this.editingId = null;
    this.resetarFormulario();
    this.isModalOpen = true;
  }

  editarTurno(turno: any) {
    this.activeMenuId = null;
    this.isEditing = true;
    this.editingId = turno.id;
    let dias = turno.dias;
    if (typeof dias === 'string'){
      try { dias = JSON.parse(dias); } catch(e) {}
    }
    this.novoTurno = {
        nome: turno.nome,
        entrada: turno.entrada,
        saida: turno.saida,
        intervalo: turno.intervalo || '',
        usuarioId: '',
        dias: { 
           D: dias?.D || dias?.dom || false, 
           S: dias?.S || dias?.seg || false, 
           T: dias?.T || dias?.ter || false, 
           Q: dias?.Q || dias?.qua || false, 
           QI: dias?.QI || dias?.qui || false, 
           SX: dias?.SX || dias?.sex || false, 
           SA: dias?.SA || dias?.sab || false
        }
    };
    this.isModalOpen = true;
  }

  fecharModal() { this.isModalOpen = false; }

  resetarFormulario() {
    this.novoTurno = {
        nome: '', entrada: '', saida: '', intervalo: '', usuarioId: '',
        dias: { D: false, S: true, T: true, Q: true, QI: true, SX: true, SA: false }
    };
  }

  alternarDia(chave: string) { this.novoTurno.dias[chave] = !this.novoTurno.dias[chave]; }

  salvarTurno() {
    if(!this.novoTurno.nome || !this.novoTurno.entrada || !this.novoTurno.saida) {
        alert('Por favor, preencha Nome, Entrada e Saída.'); return;
    }
    this.isSaving = true;
    const payload = { ...this.novoTurno };
    const req$ = this.isEditing 
        ? this.turnoService.atualizarTurno(this.editingId!, payload)
        : this.turnoService.criarTurno(payload);
    
    req$.subscribe({
        next: () => {
            alert(this.isEditing ? 'Turno atualizado!' : 'Turno criado com sucesso!');
            this.fecharModal();
            this.carregarTurnos();
            this.isSaving = false;
        },
        error: (err) => { 
            console.error(err);
            alert('Erro ao salvar. Verifique os dados.'); 
            this.isSaving = false; 
        }
    });
  }

  alternarStatus(turno: any) {
    this.activeMenuId = null;
    this.turnoService.alternarStatus(turno.id).subscribe(() => this.carregarTurnos());
  }

  excluirTurno(turno: any) {
    this.activeMenuId = null;
    if(confirm(`ATENÇÃO: Deseja excluir o turno "${turno.nome}"?`)) {
        this.turnoService.excluirTurno(turno.id).subscribe({
            next: () => this.carregarTurnos(),
            error: (e) => alert('Erro ao excluir')
        });
    }
  }

  abrirModalColaboradores(turno: any) {
    this.nomeTurnoAtual = turno.nome;
    this.isCollaboratorsModalOpen = true;
    this.listaColaboradoresTurno = [];
    this.turnoService.listarColaboradoresDoTurno(turno.id).subscribe(data => {
        this.listaColaboradoresTurno = data;
        this.cdr.detectChanges();
    });
  }
  
  fecharModalColaboradores() { this.isCollaboratorsModalOpen = false; }
}