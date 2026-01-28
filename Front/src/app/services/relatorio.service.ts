import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class RelatorioService {
  private http = inject(HttpClient);
  private apiUrl = 'http://localhost/api/reports'; 
  private usersUrl = 'https://localhost/api/users';
  
  // Baixa o HTML do relatório autenticado
  gerarEspelhoHtml(funcionarioId: string, inicio: string, fim: string): Observable<string> {
    const params = `?employee=${funcionarioId}&startDate=${inicio}&endDate=${fim}`;
    return this.http.get(`${this.apiUrl}/mirror${params}`, { responseType: 'text' });
  }

  getUsuarios(): Observable<any[]> {
    return this.http.get<any[]>(this.usersUrl);
  }

  // Enviar relatório por e-mail
  enviarEmail(formData: FormData): Observable<any> {
    return this.http.post(`${this.apiUrl}/send-email`, formData);
  }

  gerarAfastamentosHtml(inicio: string, fim: string): Observable<string> {
    const params = `?startDate=${inicio}&endDate=${fim}`;
    return this.http.get(`${this.apiUrl}/leaves${params}`, { responseType: 'text' });
  }

  gerarEscalasHtml(): Observable<string> {
    return this.http.get(`${this.apiUrl}/scales`, { responseType: 'text' });
  }

  gerarFeriasHtml(status: string): Observable<string> {
    return this.http.get(`${this.apiUrl}/vacations?status=${status}`, { responseType: 'text' });
  }

  gerarBancoHorasHtml(inicio: string, fim: string, departamento: string, employeeId: string): Observable<string> {
    const params = `?startDate=${inicio}&endDate=${fim}&departamento=${departamento}&employee=${employeeId}`;
    return this.http.get(`${this.apiUrl}/hours-bank${params}`, { responseType: 'text' });
  }

}