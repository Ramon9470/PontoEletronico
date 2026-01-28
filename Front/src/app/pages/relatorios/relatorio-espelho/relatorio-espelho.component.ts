import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { UserService } from '../../../services/user.service';
import { RelatorioService } from '../../../services/relatorio.service';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-relatorio-espelho',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './relatorio-espelho.component.html',
  styleUrls: ['./relatorio-espelho.component.scss']
})
export class RelatorioEspelhoComponent implements OnInit {
  
  private userService = inject(UserService);
  private relatorioService = inject(RelatorioService);
  private cdr = inject(ChangeDetectorRef);
  private http = inject(HttpClient);

  arquivoSelecionado: File | null = null;
  emailDestino: string = '';
  isEnviando = false;
  isGerando = false;

  listaFuncionarios: any[] = []; 

  filtros = {
    funcionario: '',
    dataInicio: '2026-01-01',
    dataFim: '2026-01-31',
    formato: 'pdf'
  };

  ngOnInit() {
    this.carregarFuncionarios();
  }

  carregarFuncionarios() {
    this.userService.getAll().subscribe({
      next: (data) => {
        this.listaFuncionarios = data;
        this.cdr.detectChanges();
      },
      error: (err) => console.error('Erro ao carregar funcionários:', err)
    });
  }

  gerarRelatorio() {
    if (!this.filtros.funcionario) {
      alert('Por favor, selecione um colaborador da lista!');
      return;
    }
    this.isGerando = true;
    this.relatorioService.gerarEspelhoHtml(
        this.filtros.funcionario, 
        this.filtros.dataInicio, 
        this.filtros.dataFim
    ).subscribe({
      next: (htmlContent) => {
        const novaJanela = window.open('', '_blank');
        if (novaJanela) {
          novaJanela.document.write(htmlContent);
          novaJanela.document.close();
        } else {
          alert('O navegador bloqueou a nova aba. Por favor, permita pop-ups.');
        }
        this.isGerando = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Erro ao gerar relatório:', err);
        alert('Erro ao gerar relatório. Verifique se sua sessão não expirou.');
        this.isGerando = false;
      }
    });
  }

  aoSelecionarArquivo(event: any){
    if (event.target.files && event.target.files[0]){
      this.arquivoSelecionado = event.target.files[0];
    }
  }

  enviarEmail(){
    if (!this.arquivoSelecionado){
      alert('Selecione um arquivo PDF ou Excel para enviar.'); return;
    }
    if (!this.filtros.funcionario) {
      alert('Selecione o colaborador acima para identificarmos no e-mail.'); return;
    }
    if (!this.emailDestino){
      alert('Digite o e-mail de destino.'); return;
    }
    this.isEnviando = true;
    const formData = new FormData();
    formData.append('file', this.arquivoSelecionado);
    formData.append('employee_id', this.filtros.funcionario);
    formData.append('email_destino', this.emailDestino);

    this.relatorioService.enviarEmail(formData).subscribe({
      next: (res: any) => {
        alert('E-mail enviado com sucesso!');
        this.limparFormularioEmail();
        this.isEnviando = false;
      },
      error: (err) => {
        console.error(err);
        alert('Erro ao enviar e-mail.');
        this.isEnviando = false;
      }
    });
  }

  limparFormularioEmail() {
    this.arquivoSelecionado = null;
    this.emailDestino = '';
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
  }
}