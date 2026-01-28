import { Component, inject, ChangeDetectorRef, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RelatorioService } from '../../../services/relatorio.service';

@Component({
  selector: 'app-relatorio-banco-horas',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './relatorio-banco-horas.component.html',
  styleUrls: ['./relatorio-banco-horas.component.scss']
})
export class RelatorioBancoHorasComponent implements OnInit {
  
  private relatorioService = inject(RelatorioService);
  private cdr = inject(ChangeDetectorRef);
  
  dataInicio = new Date().toISOString().slice(0, 8) + '01';
  dataFim = new Date().toISOString().slice(0, 10);
  departamento = 'Todos';
  
  funcionarioId = 'Todos';
  colaboradores: any[] = [];

  isGerando = false;
  isEnviando = false;
  arquivoSelecionado: File | null = null;
  emailDestino: string = '';

  ngOnInit() {
    this.carregarColaboradores();
  }

  carregarColaboradores() {
    this.relatorioService.getUsuarios().subscribe({
      next: (data: any) => {
        this.colaboradores = Array.isArray(data) ? data : (data.data || []);
      },
      error: (err) => console.error('Erro ao carregar colaboradores', err)
    });
  }

  gerarRelatorio() {
    this.isGerando = true;
    this.relatorioService.gerarBancoHorasHtml(this.dataInicio, this.dataFim, this.departamento, this.funcionarioId)
      .subscribe({
        next: (html) => {
          const win = window.open('', '_blank');
          if (win) { 
            win.document.write(html); 
            win.document.close(); 
          }
          this.isGerando = false;
          this.cdr.detectChanges();
        },
        error: () => { 
          alert('Erro ao gerar relatório.'); 
          this.isGerando = false; 
          this.cdr.detectChanges();
        }
    });
  }

  onFileSelected(event: any) {
    if (event.target.files?.[0]) this.arquivoSelecionado = event.target.files[0];
  }

  enviarEmail() {
    if (!this.arquivoSelecionado || !this.emailDestino) {
      alert('Preencha os dados.'); return;
    }
    this.isEnviando = true;
    const formData = new FormData();
    formData.append('file', this.arquivoSelecionado);
    formData.append('email_destino', this.emailDestino);
    formData.append('tipo', 'banco_horas'); 

    this.relatorioService.enviarEmail(formData).subscribe({
      next: () => { 
        alert('Sucesso!'); 
        this.isEnviando = false;
        this.cdr.detectChanges(); 
      },
      error: () => { 
        alert('Erro.'); 
        this.isEnviando = false;
        this.cdr.detectChanges(); 
      }
    });
  }
}