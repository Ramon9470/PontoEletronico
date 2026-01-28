import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AfastamentoService } from '../../services/afastamento.service';
import { UserService } from '../../services/user.service';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-afastamentos',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './afastamentos.component.html',
  styleUrls: ['./afastamentos.component.scss']
})
export class AfastamentosComponent implements OnInit {

  private afastamentoService = inject(AfastamentoService);
  private userService = inject(UserService);
  private authService = inject(AuthService);
  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);

  // Estados
  isModalOpen = false;
  isLoading = false;
  isSaving = false;
  
  // Dados
  listaAfastamentos: any[] = [];
  afastamentosFiltrados: any[] = [];
  listaFuncionarios: any[] = [];
  
  termoBusca: string = '';
  
  novoAfastamento: any = {
    usuarioId: '', 
    tipo: 'atestado', 
    dataInicio: '', 
    dataFim: '', 
    motivo: '', 
    anexo: null
  };
  nomeArquivoSelecionado: string = '';

  stats = { ausentesHoje: 0, retornandoHoje: 0, inss: 0 };

  ngOnInit() {
    this.verificarAcesso();
    this.carregarFuncionarios();
    this.carregarAfastamentos();
  }

  verificarAcesso() {
    if (!this.authService.isLoggedIn()) { 
        this.router.navigate(['/login']); 
    }
  }

  carregarFuncionarios() {
    this.userService.getAll().subscribe(data => this.listaFuncionarios = data);
  }

  carregarAfastamentos() {
    this.isLoading = true;
    this.afastamentoService.listar().subscribe({
        next: (data) => {
            this.listaAfastamentos = data;
            this.afastamentosFiltrados = data;
            this.calcularEstatisticas();
            this.isLoading = false;
            this.cdr.detectChanges();
        },
        error: (err) => {
            console.error(err);
            this.isLoading = false;
        }
    });
  }

  calcularEstatisticas() {
    const hoje = new Date();
    hoje.setHours(0,0,0,0);
    
    let ausentes = 0, retornando = 0, inss = 0;

    if(this.listaAfastamentos) {
        this.listaAfastamentos.forEach(a => {
            const inicio = new Date(a.data_inicio + 'T00:00:00');
            const fim = new Date(a.data_fim + 'T00:00:00');
            
            if (hoje >= inicio && hoje <= fim) ausentes++;
            if (fim.getTime() === hoje.getTime()) retornando++;
            if (['inss', 'suspensao'].includes(a.tipo_afastamento)) inss++;
        });
    }
    this.stats = { ausentesHoje: ausentes, retornandoHoje: retornando, inss: inss };
  }

  aplicarFiltro() {
    if (!this.termoBusca) {
        this.afastamentosFiltrados = this.listaAfastamentos;
    } else {
        const termo = this.termoBusca.toLowerCase();
        this.afastamentosFiltrados = this.listaAfastamentos.filter(a => 
            a.funcionario_nome?.toLowerCase().includes(termo) || 
            a.tipo_afastamento?.toLowerCase().includes(termo)
        );
    }
  }
  
  aoSelecionarArquivo(event: any) {
    if (event.target.files.length > 0) {
        this.novoAfastamento.anexo = event.target.files[0];
        this.nomeArquivoSelecionado = event.target.files[0].name;
    }
  }

  abrirModal() { 
    this.resetarFormulario();
    this.isModalOpen = true; 
  }
  
  fecharModal() { this.isModalOpen = false; }
  
  resetarFormulario() {
    this.novoAfastamento = { usuarioId: '', tipo: 'atestado', dataInicio: '', dataFim: '', motivo: '', anexo: null };
    this.nomeArquivoSelecionado = '';
  }

  salvarAfastamento() {
    if(!this.novoAfastamento.usuarioId || !this.novoAfastamento.dataInicio || !this.novoAfastamento.dataFim) {
      alert('Por favor, preencha o colaborador e as datas.'); return;
    }

    this.isSaving = true;
    
    this.afastamentoService.criar(this.novoAfastamento, this.novoAfastamento.anexo).subscribe({
      next: () => {
        alert('Lançado com sucesso!');
        this.fecharModal();
        this.carregarAfastamentos();
        this.isSaving = false;
      },
      error: (e) => { 
        console.error(e);
        alert('Erro ao salvar.'); 
        this.isSaving = false; 
      }
    });
  }

  excluirAfastamento(id: number) {
    if(confirm('Tem certeza que deseja excluir este registro?')) {
      this.afastamentoService.excluir(id).subscribe(() => this.carregarAfastamentos());
    }
  }

  getClassBadge(status: string) {
    switch(status) {
        case 'Em andamento': return 'badge-warning';
        case 'Concluído': return 'badge-gray';
        case 'Programado': return 'badge-blue';
        default: return 'badge-gray';
    }
  }

  exportarDados() {
    if (this.afastamentosFiltrados.length === 0) { alert('Sem dados para exportar.'); return; }
    
    let csvContent = "Colaborador;Cargo;Tipo;Inicio;Fim;Duracao;Status;Motivo\n";
    
    this.afastamentosFiltrados.forEach(row => {
      const line = [
        row.funcionario_nome, 
        row.cargo, 
        row.tipo_afastamento ? row.tipo_afastamento.toUpperCase() : '',
        this.formatarDataBr(row.data_inicio), 
        this.formatarDataBr(row.data_fim),    
        row.dias_duracao, 
        row.status_atual, 
        `"${row.motivo || ''}"`
      ].join(";");
      csvContent += line + "\n";
    });

    const blob = new Blob(["\ufeff" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `afastamentos_${new Date().getTime()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  private formatarDataBr(dateStr: string): string {
    if (!dateStr) return '';
    try {
        const parts = dateStr.split('-');
        if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
        return dateStr;
    } catch (e) { return dateStr; }
  }
}