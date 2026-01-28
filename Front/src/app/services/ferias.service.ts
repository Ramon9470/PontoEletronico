import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class FeriasService {
  private http = inject(HttpClient);
  private apiUrl = 'https://localhost/api/ferias';

  listar(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl);
  }

  salvar(dados: any): Observable<any> {
    // Adapta o objeto para o formato que o Backend espera
    const payload = {
        user_id: dados.usuarioId,
        data_inicio: dados.dataInicio,
        dias: dados.dias,
        vender_um_terco: dados.venderUmTerco
    };
    return this.http.post<any>(this.apiUrl, payload);
  }

  excluir(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}