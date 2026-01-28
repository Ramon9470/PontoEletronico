import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { SolicitarAjusteService } from '../../services/solicitar-ajuste.service';

@Component({
  selector: 'app-solicitar-ajuste',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './solicitar-ajuste.component.html',
  styleUrls: ['./solicitar-ajuste.component.scss']
})
export class SolicitarAjuste implements OnInit {
  
  private service = inject(SolicitarAjusteService);
  private cdr = inject(ChangeDetectorRef);

  // Campos do Formulário
  dataOcorrido: string = '';
  tipoAjuste: string = 'inclusao';
  horario: string = '';
  justificativa: string = '';
  selectedFile: File | null = null;
  
  isSubmitting = false;
  historyList: any[] = [];

  ngOnInit() {
    this.carregarHistorico();
  }

  onFileSelected(event: any) {
    this.selectedFile = event.target.files[0];
  }

  onClear() {
    this.dataOcorrido = '';
    this.tipoAjuste = 'inclusao';
    this.horario = '';
    this.justificativa = '';
    this.selectedFile = null;
    const fileInput = document.getElementById('fileInput') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
  }

  onSubmit() {
    if (!this.dataOcorrido || !this.justificativa) {
        alert('Preencha a data e a justificativa!');
        return;
    }

    this.isSubmitting = true;

    // Montando o FormData
    const formData = new FormData();
    formData.append('data_ocorrido', this.dataOcorrido);
    formData.append('tipo_ajuste', this.tipoAjuste);
    formData.append('justificativa', this.justificativa);
    
    if (this.horario) formData.append('horario', this.horario);
    if (this.selectedFile) formData.append('anexo', this.selectedFile);

    this.service.criarSolicitacao(formData).subscribe({
        next: () => {
            alert('Solicitação enviada com sucesso!');
            this.isSubmitting = false;
            this.onClear();
            this.carregarHistorico();
            this.cdr.detectChanges();
        },
        error: (err) => {
            console.error(err);
            this.isSubmitting = false;
            this.cdr.detectChanges();
            
            if (err.status === 500) {
                alert('Erro interno no servidor. Tente rodar as migrations ou contate o suporte.');
            } else {
                alert('Erro ao enviar solicitação.');
            }
        }
    });
  }

  carregarHistorico() {
    this.service.listarMinhasSolicitacoes().subscribe(data => {
        // Adiciona controle de detalhes
        this.historyList = data.map(item => ({ ...item, showDetails: false }));
        this.cdr.detectChanges();
    });
  }

  toggleDetails(item: any) {
    item.showDetails = !item.showDetails;
  }

  getStatusClass(status: string) {
    switch(status) {
        case 'aprovado': return 'bg-success text-white';
        case 'recusado': return 'bg-danger text-white';
        default: return 'bg-warning text-dark';
    }
  }

  getTipoLabel(tipo: string) {
    const map: any = {
        'inclusao': 'Inclusão de Ponto',
        'abono': 'Abono / Atestado',
        'desconsiderar': 'Desconsiderar'
    };
    return map[tipo] || tipo;
  }
}