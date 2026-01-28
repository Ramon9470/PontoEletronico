import { Component, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RelatorioService } from '../../../services/relatorio.service';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-relatorio-afastamentos',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './relatorio-afastamentos.component.html',
  styleUrls: ['./relatorio-afastamentos.component.scss']
})
export class RelatorioAfastamentosComponent {
  
  private relatorioService = inject(RelatorioService);
  private cdr = inject(ChangeDetectorRef);
  
  dataInicio = new Date().toISOString().slice(0, 8) + '01';
  dataFim = new Date().toISOString().slice(0, 10);

  isGerando = false;
  isEnviando = false;
  
  arquivoSelecionado: File | null = null;
  emailDestino: string = '';

  gerarRelatorio() {
    this.isGerando = true;
    this.relatorioService.gerarAfastamentosHtml(this.dataInicio, this.dataFim)
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
    formData.append('tipo', 'afastamentos');

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