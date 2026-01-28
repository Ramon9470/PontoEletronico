import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { BehaviorSubject, Observable, tap } from 'rxjs';

export interface LoginResponse {
  success: boolean;
  message: string;
  token: string;
  user: User;
}

export interface User {
  id: number;
  name: string;
  role: 'admin' | 'gestor' | 'funcionario';
  photo?: string;
  foto_url?: string;
  photoUrl?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = '/api/login';
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient, private router: Router) {
    // Ao iniciar, tenta recuperar o usuário salvo
    const savedUser = localStorage.getItem('user_data');
    if (savedUser) {
      this.currentUserSubject.next(JSON.parse(savedUser));
    }
  }

  // LOGIN
  login(credentials: { username: string; password: string }): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(this.apiUrl, credentials).pipe(
      tap(response => {
        if (response.success && response.token) {
          // Salva Token e Usuário
          localStorage.setItem('auth_token', response.token);
          localStorage.setItem('user_data', JSON.stringify(response.user));
          
          // Atualiza estado da aplicação
          this.currentUserSubject.next(response.user);
          
          // Redireciona
          this.router.navigate(['/home']);
        }
      })
    );
  }

  logout() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
    this.currentUserSubject.next(null);
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  getUserRole(): string | undefined {
    return this.currentUserSubject.value?.role;
  }
  
  // Helper para pegar usuário atual
  getUser(): User | null {
    return this.currentUserSubject.value;
  }
}