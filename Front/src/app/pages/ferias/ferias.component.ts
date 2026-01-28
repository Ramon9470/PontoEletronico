import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { FeriasService } from '../../services/ferias.service';
import { UserService } from '../../services/user.service';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-ferias',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './ferias.component.html',
  styleUrls: ['./ferias.component.scss']
})
export class FeriasComponent implements OnInit {

  private feriasService = inject(FeriasService);
  private userService = inject(UserService);
  private authService = inject(AuthService);
  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);

  isModalOpen = false;
  isLoading = false;
  isSaving = false;
  
  listaFuncionarios: any[] = [];
  listaFerias: any[] = [];
  feriasFiltradas: any[] = [];
  
  termoBusca: string = '';

  // Objeto do Formulário
  novasFerias = {
    usuarioId: '',
    dataInicio: '',
    dias: 30,
    venderUmTerco: false
  };
  
  previsaoRetorno: string = '';
  stats = { emFerias: 0, programadas: 0, abonosVendidos: 0 };

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
    this.userService.getAll().subscribe(data => this.listaFuncionarios = data);
    
    // Carrega Férias
    this.feriasService.listar().subscribe({
      next: (data) => {
        this.listaFerias = data;
        this.feriasFiltradas = data;
        this.calcularEstatisticas();
        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: () => this.isLoading = false
    });
  }

  calcularEstatisticas() {
    this.stats.emFerias = this.listaFerias.filter(v => v.status_visual === 'em_andamento').length;
    this.stats.programadas = this.listaFerias.filter(v => v.status_visual === 'programada').length;
    this.stats.abonosVendidos = this.listaFerias.filter(v => v.vender_um_terco).length;
  }

  filtrar() {
    if(!this.termoBusca) {
        this.feriasFiltradas = this.listaFerias;
    } else {
        const termo = this.termoBusca.toLowerCase();
        this.feriasFiltradas = this.listaFerias.filter(v => 
            v.nome_completo?.toLowerCase().includes(termo) ||
            v.cargo?.toLowerCase().includes(termo)
        );
    }
  }

  abrirModal() { 
    this.isModalOpen = true; 
    this.novasFerias = { usuarioId: '', dataInicio: '', dias: 30, venderUmTerco: false };
    this.calcularRetorno();
  }
  
  fecharModal() { this.isModalOpen = false; }

  calcularRetorno() {
    if (this.novasFerias.dataInicio && this.novasFerias.dias) {
        const inicio = new Date(this.novasFerias.dataInicio);
        // Adiciona dias (lembrando que começa a contar do dia inicial, então dias - 1 para a data final)
        inicio.setDate(inicio.getDate() + this.novasFerias.dias - 1);
        this.previsaoRetorno = inicio.toLocaleDateString('pt-BR');
    } else {
        this.previsaoRetorno = '-';
    }
  }

  salvarFerias() {
    if(!this.novasFerias.usuarioId || !this.novasFerias.dataInicio) {
        alert('Preencha funcionário e data de início.'); return;
    }

    this.isSaving = true;
    this.feriasService.salvar(this.novasFerias).subscribe({
        next: () => {
            alert('Férias programadas com sucesso!');
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

  excluirFerias(id: number) {
    if(confirm('Cancelar estas férias?')) {
        this.feriasService.excluir(id).subscribe(() => this.carregarDados());
    }
  }

  getStatusClass(status: string): string {
    switch(status) {
        case 'programada': return 'status-blue';
        case 'em_andamento': return 'status-green';
        case 'concluido': return 'status-gray';
        default: return '';
    }
  }

  getStatusLabel(status: string): string {
    switch(status) {
        case 'programada': return 'Programada';
        case 'em_andamento': return 'Em Andamento';
        case 'concluido': return 'Concluído';
        default: return status;
    }
  }

  exportarDados() {
    if (this.feriasFiltradas.length === 0) { alert('Sem dados.'); return; }
    
    let csvContent = "Colaborador;Cargo;Inicio;Fim;Dias;Abono;Status\n";
    
    this.feriasFiltradas.forEach(row => {
      const line = [
        row.nome_completo, 
        row.cargo, 
        this.formatarDataBr(row.data_inicio),
        this.formatarDataBr(row.data_fim),
        row.dias_gozo,
        row.vender_um_terco ? 'SIM' : 'NÃO',
        this.getStatusLabel(row.status_visual)
      ].join(";");
      csvContent += line + "\n";
    });

    const blob = new Blob(["\ufeff" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `ferias_${new Date().getTime()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  private formatarDataBr(dateStr: string): string {
    if (!dateStr) return '';
    const parts = dateStr.split('-'); 
    return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
  }
}