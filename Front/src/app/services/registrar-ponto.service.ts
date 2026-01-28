import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class RegistrarPontoService {

  private http = inject(HttpClient);
  private readonly apiUrl = '/api'; 

  registrarPontoFacial(formData: FormData): Observable<any> {
    return this.http.post(`${this.apiUrl}/points/facial`, formData);
  }

  getPontosHoje(): Observable<any> {
    return this.http.get(`${this.apiUrl}/points/today`);
  }
}