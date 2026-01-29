import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EscalaService {
  private http = inject(HttpClient);
  private apiUrl = '/api/escalas';

  listar(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl);
  }

  salvar(dados: any): Observable<any> {
    return this.http.post<any>(this.apiUrl, dados);
  }

  excluir(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}
