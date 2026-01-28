import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

export const authGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  
  // Verifica se o token existe
  const token = localStorage.getItem('auth_token');

  if (token) {
    return true;
  } else {
    // Não tem token? Volta pro login
    router.navigate(['/login']);
    return false;
  }
};