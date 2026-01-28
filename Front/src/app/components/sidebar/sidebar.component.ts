import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss']
})
export class SidebarComponent implements OnInit {
  // Variáveis que o HTML estava procurando
  isCollapsed = false;
  isSubmenuOpen = false;
  isAdmin = false;
  
  constructor(private authService: AuthService) {}

  ngOnInit(): void {
    this.authService.currentUser$.subscribe(user => {
      // Define se é admin baseada na role do usuário
      this.isAdmin = user?.role === 'admin' || user?.role === 'gestor';
    });
  }

  toggleSidebar() {
    this.isCollapsed = !this.isCollapsed;
  }

  toggleSubmenu() {
    if (!this.isCollapsed) {
      this.isSubmenuOpen = !this.isSubmenuOpen;
    }
  }

  logout() {
    this.authService.logout();
  }
}