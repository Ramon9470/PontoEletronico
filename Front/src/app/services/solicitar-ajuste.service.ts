import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class SolicitarAjusteService {
  private http = inject(HttpClient);
  private apiUrl = 'https://localhost/api/solicitacoes'; 

  criarSolicitacao(data: FormData): Observable<any> {
    return this.http.post(this.apiUrl, data);
  }

  listarMinhasSolicitacoes(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl);
  }
}