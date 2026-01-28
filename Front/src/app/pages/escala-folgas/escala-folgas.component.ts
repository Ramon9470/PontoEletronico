import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EscalaService } from '../../services/escala.service';
import { UserService } from '../../services/user.service';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-escala-folgas',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './escala-folgas.component.html',
  styleUrls: ['./escala-folgas.component.scss']
})
export class EscalaFolgasComponent implements OnInit {
  
  private escalaService = inject(EscalaService);
  private userService = inject(UserService);
  private authService = inject(AuthService);
  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);

  isModalOpen = false;
  isLoading = false;
  isSaving = false;
  
  listaFuncionarios: any[] = [];
  listaEscalas: any[] = [];
  escalasFiltradas: any[] = [];
  
  termoBusca: string = '';

  // Objeto do Formulário
  novaEscala = {
    usuarioId: '',
    tipo: '6x1',
    regraFolga: 'Domingo',
    inicioCiclo: ''
  };

  stats = { totalColaboradores: 0, escalasDefinidas: 0, pendentes: 0 };

  ngOnInit() {
    this.verificarAcesso();
    this.carregarDados();
  }

  verificarAcesso() {
    if (!this.authService.isLoggedIn()) { 
        this.router.navigate(['/login']); 
    }
  }

  carregarDados() {
    this.isLoading = true;
    
    // Carrega Funcionários
    this.userService.getAll().subscribe(data => {
      this.listaFuncionarios = data;
      this.stats.totalColaboradores = data.length;
      this.atualizarStats();
    });

    // Carrega Escalas
    this.escalaService.listar().subscribe({
      next: (data) => {
        this.listaEscalas = data;
        this.escalasFiltradas = data;
        this.stats.escalasDefinidas = data.length;
        this.atualizarStats();
        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error(err);
        this.isLoading = false;
      }
    });
  }

  atualizarStats() {
    this.stats.pendentes = this.stats.totalColaboradores - this.stats.escalasDefinidas;
  }

  filtrarEscalas() {
    if (!this.termoBusca) {
      this.escalasFiltradas = this.listaEscalas;
    } else {
      const termo = this.termoBusca.toLowerCase();
      this.escalasFiltradas = this.listaEscalas.filter(s => 
        s.nome_completo?.toLowerCase().includes(termo) || 
        s.cargo?.toLowerCase().includes(termo)
      );
    }
  }

  abrirModal() { 
    this.resetarFormulario();
    this.isModalOpen = true; 
  }
  
  fecharModal() { 
    this.isModalOpen = false; 
  }

  resetarFormulario() {
    this.novaEscala = { usuarioId: '', tipo: '6x1', regraFolga: 'Domingo', inicioCiclo: '' };
  }

  salvarEscala() {
    if(!this.novaEscala.usuarioId || !this.novaEscala.inicioCiclo) {
        alert('Preencha o colaborador e a data de início.');
        return;
    }
    
    this.isSaving = true;
    
    // Prepara objeto para o backend
    const payload = {
        user_id: this.novaEscala.usuarioId,
        tipo: this.novaEscala.tipo,
        inicio_ciclo: this.novaEscala.inicioCiclo,
        regra_folga: this.novaEscala.regraFolga
    };

    this.escalaService.salvar(payload).subscribe({
      next: () => {
        alert('Escala definida com sucesso!');
        this.fecharModal();
        this.carregarDados();
        this.isSaving = false;
      },
      error: (e) => {
        console.error(e);
        alert('Erro ao salvar.');
        this.isSaving = false;
      }
    });
  }

  excluirEscala(id: number) {
    if(confirm('Deseja remover a regra de escala deste funcionário?')) {
        this.escalaService.excluir(id).subscribe(() => this.carregarDados());
    }
  }

  // Helpers Visuais
  getLabelTipo(tipo: string): string {
    const mapa: any = { '6x1': 'Escala 6x1', '5x2': 'Escala 5x2', '12x36': 'Plantão 12x36' };
    return mapa[tipo] || tipo;
  }
}