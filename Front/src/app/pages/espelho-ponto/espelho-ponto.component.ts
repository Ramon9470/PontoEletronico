import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { UserService } from '../../services/user.service';
import { EspelhoPontoService } from '../../services/espelho.service';

@Component({
  selector: 'app-espelho-ponto',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './espelho-ponto.component.html',
  styleUrls: ['./espelho-ponto.component.scss']
})
export class EspelhoPonto implements OnInit {
  
  // Injeção de dependências
  private espelhoService = inject(EspelhoPontoService);
  private authService = inject(AuthService);
  private userService = inject(UserService);
  private cdr = inject(ChangeDetectorRef);

  currentDate = new Date();
  selectedMonth = this.currentDate.getMonth() + 1; 
  selectedYear = this.currentDate.getFullYear();
  periodoLabel = '';

  collaborator: any = { name: 'Carregando...', role: '', id: '', photo: null };
  summary: any = { workedHours: '00:00', targetHours: '00:00', extrasDay: '00:00' };
  pontoRecords: any[] = [];
  
  isLoading = false;
  canEdit: boolean = false;
  targetUserId: number | null = null;
  searchTerm: string = ''; 

  ngOnInit() {
    this.checkPermissions();
    
    // Se não for admin, pega o ID do usuário logado para carregar o próprio
    const currentUser = this.authService.getUser();
    if (currentUser) {
      this.targetUserId = currentUser.id;
    }
    
    this.atualizarRotulo();
    this.carregarEspelho();
  }

  checkPermissions() {
    const user = this.authService.getUser();
    const perfil = (user?.role || '').toLowerCase();
    this.canEdit = ['admin', 'rh', 'gestor'].includes(perfil);
  }

  buscarColaborador() {
    if (!this.canEdit) return; 
    if (!this.searchTerm.trim()) return;

    this.userService.getAll().subscribe({
      next: (users) => {
        const term = this.searchTerm.toLowerCase();
        // Busca flexível por ID ou Nome
        const found = users.find((u: any) => 
          (u.name && u.name.toLowerCase().includes(term)) || 
          (u.nome_completo && u.nome_completo.toLowerCase().includes(term)) ||
          String(u.id) === term
        );

        if (found) {
          this.targetUserId = found.id;
          this.searchTerm = ''; 
          this.carregarEspelho(); 
        } else {
          alert('Colaborador não encontrado.');
        }
      },
      error: (err) => console.error('Erro na busca de usuários:', err)
    });
  }

  carregarEspelho() {
    this.isLoading = true;

    // Chama o Service em vez de fazer HTTP direto
    this.espelhoService.consultarEspelho(this.targetUserId, this.selectedMonth, this.selectedYear)
      .subscribe({
        next: (data) => {
          if (data.collaborator) {
               this.collaborator = data.collaborator;
               
               if (this.collaborator.photo && !this.collaborator.photo.startsWith('http')) {
                   const baseUrl = window.location.origin;
                   this.collaborator.photo = `${baseUrl}/${this.collaborator.photo}`;
               }
          }
          
          if (data.summary) this.summary = data.summary;
          if (data.records) this.pontoRecords = data.records;

          this.isLoading = false;
          this.cdr.detectChanges();
        },
        error: (err) => {
            console.error('Erro ao carregar espelho:', err);
            this.collaborator.name = 'Não foi possível carregar';
            this.pontoRecords = [];
            this.isLoading = false;
            this.cdr.detectChanges();
        }
      });
  }

  mesAnterior() {
    if (this.selectedMonth === 1) { 
        this.selectedMonth = 12; 
        this.selectedYear--; 
    } else { 
        this.selectedMonth--; 
    }
    this.atualizarRotulo();
    this.carregarEspelho();
  }

  proximoMes() {
    if (this.selectedMonth === 12) { 
        this.selectedMonth = 1; 
        this.selectedYear++; 
    } else { 
        this.selectedMonth++; 
    }
    this.atualizarRotulo();
    this.carregarEspelho();
  }

  atualizarRotulo() {
    const date = new Date(this.selectedYear, this.selectedMonth - 1, 1);
    const nomeMes = date.toLocaleString('pt-BR', { month: 'long', year: 'numeric' });
    this.periodoLabel = nomeMes.charAt(0).toUpperCase() + nomeMes.slice(1);
  }

  imprimir() { window.print(); }
  
  exportarPDF() { 
      // Futura implementação real de PDF
      window.print(); 
  }

  salvarAcoes() { 
    alert('Edições salvas com sucesso!');
    this.carregarEspelho(); 
  }

  cancelarAcoes() { 
    this.carregarEspelho(); 
  }

  getStatusColor(status: string): string {
    switch(status) {
      case 'falta': return 'text-red';
      case 'atraso': return 'text-orange';
      case 'extra': return 'text-blue';
      case 'ok': return 'text-green';
      default: return 'text-gray';
    }
  }

  getIcon(status: string): string {
    switch(status) {
      case 'falta': return 'close';
      case 'atraso': return 'priority_high';
      case 'extra': return 'add';
      case 'ok': return 'check';
      case 'folga': return 'weekend';
      default: return 'remove';
    }
  }
}
