import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EspelhoPontoService {
  
  private http = inject(HttpClient);
  private apiUrl = 'https://localhost/api'; 

  constructor() { }

  /**
   * Busca os dados do espelho de ponto para um usuário específico em um mês/ano.
   */
  consultarEspelho(userId: number, mes: number, ano: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/espelho?user_id=${userId}&month=${mes}&year=${ano}`);
  }
}