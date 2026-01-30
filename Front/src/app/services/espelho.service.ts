import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EspelhoPontoService {
  
  private http = inject(HttpClient);
  private apiUrl = '/api'; 

  constructor() { }

  /**
   * Busca os dados do espelho de ponto para um usuário específico em um mês/ano.
   */
  consultarEspelho(userId: number | null, mes: number, ano: number): Observable<any> {
    let params =  `?month=${mes}&year=${ano}`;

    if (userId){
	params += `$user_id=${userId}`;
    }
    return this.http.get<any>(`${this.apiUrl}/espelho-ponto${params}`);
  }
}
