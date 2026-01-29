import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class TurnoService {
  
  private http = inject(HttpClient);
  private apiUrl = '/api/turnos'; 

  constructor() { }

  listarTurnos(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl);
  }

  criarTurno(dados: any): Observable<any> {
    return this.http.post<any>(this.apiUrl, dados);
  }

  atualizarTurno(id: number, dados: any): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}/${id}`, dados);
  }

  alternarStatus(id: number): Observable<any> {
    return this.http.patch<any>(`${this.apiUrl}/${id}/status`, {});
  }

  excluirTurno(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/${id}`);
  }

  listarColaboradoresDoTurno(turnoId: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/${turnoId}/colaboradores`);
  }
}
