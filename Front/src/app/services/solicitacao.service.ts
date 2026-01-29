import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SolicitacaoService {
  
  private http = inject(HttpClient);
  private apiUrl = '/api'; 

  constructor() { }

  listarPendentes(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/admin/solicitacoes`);
  }

  responderSolicitacao(id: number, status: 'aprovado' | 'recusado'): Observable<any> {
    return this.http.patch(`${this.apiUrl}/solicitacoes/${id}/status`, { status });
  }
}
