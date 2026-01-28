import { HttpInterceptorFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  // Pega o token salvo no login
  const token = localStorage.getItem('auth_token');

  // Se tiver token, clona a requisição e adiciona o cabeçalho Authorization
  if (token) {
    const cloned = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
    return next(cloned);
  }

  // Se não tiver token, manda como está
  return next(req);
};