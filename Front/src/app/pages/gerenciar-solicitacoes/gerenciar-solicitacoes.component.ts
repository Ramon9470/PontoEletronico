import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SolicitacaoService } from '../../services/solicitacao.service';

@Component({
  selector: 'app-gerenciar-solicitacoes',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './gerenciar-solicitacoes.component.html',
  styleUrls: ['./gerenciar-solicitacoes.component.scss']
})
export class GerenciarSolicitacoes implements OnInit {

  private solicitacaoService = inject(SolicitacaoService);
  private cdr = inject(ChangeDetectorRef);

  isLoading: boolean = true;
  pendingList: any[] = [];

  ngOnInit() {
    this.carregarPendencias();
  }

  carregarPendencias() {
    this.isLoading = true;
    this.solicitacaoService.listarPendentes().subscribe({
      next: (data) => {
        this.pendingList = data;
        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        console.error('Erro ao buscar solicitações', err);
        this.isLoading = false;
        this.cdr.detectChanges();
      }
    });
  }

  confirmAction(item: any, acao: 'aprovado' | 'recusado') {
    if (!confirm(`Tem certeza que deseja marcar como ${acao.toUpperCase()}?`)) {
      return;
    }

    this.solicitacaoService.responderSolicitacao(item.id, acao).subscribe({
      next: () => {
        alert('Solicitação processada com sucesso!');
        this.pendingList = this.pendingList.filter(req => req.id !== item.id);
        this.cdr.detectChanges();
      },
      error: (err) => {
        alert('Erro ao processar solicitação.');
        console.error(err);
      }
    });
  }
}