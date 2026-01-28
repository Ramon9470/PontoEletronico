import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AfastamentoService {
  private http = inject(HttpClient);
  private apiUrl = 'https://localhost/api/afastamentos';

  listar(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl);
  }

  criar(dados: any, arquivo: File | null): Observable<any> {
    const formData = new FormData();
    formData.append('user_id', dados.usuarioId);
    formData.append('tipo', dados.tipo);
    formData.append('data_inicio', dados.dataInicio);
    formData.append('data_fim', dados.dataFim);
    formData.append('motivo', dados.motivo || '');
    
    if (arquivo) {
      formData.append('anexo', arquivo);
    }

    return this.http.post<any>(this.apiUrl, formData);
  }

  excluir(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}