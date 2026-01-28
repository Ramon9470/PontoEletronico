import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { AuthService } from '../../services/auth.service';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent implements OnInit {
  userName: string = 'Usuário';
  userRole: string = 'Visitante';
  userPhoto: string | null = null;
  isUserMenuOpen = false;

  @Output() toggleSidebar = new EventEmitter<void>();

  constructor(private authService: AuthService) {}

  ngOnInit(): void {
    this.authService.currentUser$.subscribe(user => {
      if (user) {
        this.userName = user.name;
        this.userRole = user.role;
        
        const u = user as any;
        this.userPhoto = u.foto_url || u.photoUrl || 'assets/default-user.png';
      }
    });
  }

  logout() {
    this.authService.logout();
  }

  toggleUserMenu(){
    this.isUserMenuOpen = !this.isUserMenuOpen;
  }

  closeMenu(){
    this.isUserMenuOpen = false;
  }
}