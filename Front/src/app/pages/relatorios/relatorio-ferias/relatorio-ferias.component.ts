import { Component, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RelatorioService } from '../../../services/relatorio.service';

@Component({
  selector: 'app-relatorio-ferias',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './relatorio-ferias.component.html',
  styleUrls: ['./relatorio-ferias.component.scss']
})
export class RelatorioFeriasComponent {
  
  private relatorioService = inject(RelatorioService);
  private cdr = inject(ChangeDetectorRef);
  
  statusFiltro = 'Todos'; 
  isGerando = false;
  isEnviando = false;
  
  arquivoSelecionado: File | null = null;
  emailDestino: string = '';

  gerarRelatorio() {
    this.isGerando = true;
    this.relatorioService.gerarFeriasHtml(this.statusFiltro)
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
        }
      });
  }

  onFileSelected(event: any) {
    if (event.target.files?.[0]) {
      this.arquivoSelecionado = event.target.files[0];
    }
  }

  enviarEmail() {
    if (!this.arquivoSelecionado || !this.emailDestino) {
      alert('Preencha o e-mail e anexe o arquivo.'); return;
    }
    this.isEnviando = true;
    const formData = new FormData();
    formData.append('file', this.arquivoSelecionado);
    formData.append('email_destino', this.emailDestino);
    formData.append('tipo', 'ferias');

    this.relatorioService.enviarEmail(formData).subscribe({
      next: () => {
        alert('E-mail enviado!');
        this.isEnviando = false;
        this.arquivoSelecionado = null;
        this.emailDestino = '';
      },
      error: () => {
        alert('Erro ao enviar.');
        this.isEnviando = false;
      }
    });
  }
}