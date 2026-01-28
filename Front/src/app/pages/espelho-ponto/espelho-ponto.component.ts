import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../services/auth.service';
import { UserService } from '../../services/user.service';

@Component({
  selector: 'app-espelho-ponto',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './espelho-ponto.component.html',
  styleUrls: ['./espelho-ponto.component.scss']
})
export class EspelhoPonto implements OnInit {
  
  private http = inject(HttpClient);
  private authService = inject(AuthService);
  private userService = inject(UserService);
  private cdr = inject(ChangeDetectorRef);

  // URL correta (HTTPS padrão)
  private apiUrl = 'https://localhost/api/espelho-ponto';

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
    this.canEdit = ['admin', 'rh'].includes(perfil);
  }

  buscarColaborador() {
    if (!this.searchTerm.trim()) return;

    this.userService.getAll().subscribe({
      next: (users) => {
        const term = this.searchTerm.toLowerCase();
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
      error: (err) => console.error('Erro na busca:', err)
    });
  }

  carregarEspelho() {
    if (!this.targetUserId) return;
    this.isLoading = true;

    const url = `${this.apiUrl}?user_id=${this.targetUserId}&mes=${this.selectedMonth}&ano=${this.selectedYear}`;

    this.http.get<any>(url).subscribe({
      next: (data) => {
        if (data.collaborator) {
             this.collaborator = data.collaborator;
             
             // --- LÓGICA DE FOTO CORRIGIDA ---
             // Removemos o código que forçava a porta 8000.
             // Agora confiamos no link que vem do backend (APP_URL=https://localhost)
             if (this.collaborator.photo) {
                 // Se por acaso vier :8000 antigo, removemos
                 if (this.collaborator.photo.includes(':8000')) {
                     this.collaborator.photo = this.collaborator.photo.replace(':8000', '');
                 }
                 // Se vier http inseguro, forçamos https
                 if (this.collaborator.photo.startsWith('http:')) {
                    this.collaborator.photo = this.collaborator.photo.replace('http:', 'https:');
                 }
                 // Se vier caminho relativo (ex: fotos_perfil/foto.jpg), montamos a URL segura
                 else if (!this.collaborator.photo.startsWith('http')) {
                     this.collaborator.photo = `https://localhost/storage/${this.collaborator.photo}`;
                 }
             }
        }
        if (data.summary) this.summary = data.summary;
        if (data.records) this.pontoRecords = data.records;

        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
          console.error('Erro detalhado:', err);
          // Mostra mensagem amigável ao usuário
          this.collaborator.name = 'Erro ao carregar dados';
          this.pontoRecords = []; // Limpa tabela para não confundir
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
  exportarPDF() { window.print(); }
  salvarAcoes() { alert('Salvo!'); }
  cancelarAcoes() { this.carregarEspelho(); }

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