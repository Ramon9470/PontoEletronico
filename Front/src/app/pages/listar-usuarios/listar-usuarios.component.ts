import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { UserService } from '../../services/user.service';

@Component({
  selector: 'app-listar-usuarios',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './listar-usuarios.component.html',
  styleUrls: ['./listar-usuarios.component.scss']
})
export class ListarUsuariosComponent implements OnInit {

  employees: any[] = [];
  filteredList: any[] = [];
  isLoading = false;
  searchTerm: string = '';

  constructor(
    private userService: UserService,
    private router: Router,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit() {
    this.loadEmployees();
  }

  loadEmployees() {
    this.isLoading = true;
    this.userService.getAll().subscribe({
      next: (data) => {
        this.processData(data);
      },
      error: (err) => {
        console.warn('Erro ao carregar usuários:', err);
        this.isLoading = false;
      }
    });
  }

  processData(data: any[]) {
    this.employees = data.map((user: any) => {
        if (user.foto_url) {
             let url = user.foto_url;
             
             if (url.includes(':8000')) {
                 url = url.replace(':8000', '');
             }
             
             if (!url.startsWith('http')) {
                 url = `/storage/${url}`;
             }
             else if (url.startsWith('http:')) {
                 url = url.replace('http:', 'https:');
             }
             
             user.foto_url = url;
        }
        return user;
    });
    this.filteredList = this.employees;
    this.isLoading = false;
    this.cdr.detectChanges();
  }

  filterList() {
    if (!this.searchTerm) {
      this.filteredList = this.employees;
      return;
    }
    const term = this.searchTerm.toLowerCase();
    
    this.filteredList = this.employees.filter(emp => {
      const nome = emp.name?.toLowerCase() || '';
      const cargo = emp.cargo?.toLowerCase() || '';
      const cpf = emp.cpf || '';
      return nome.includes(term) || cargo.includes(term) || cpf.includes(term);
    });
  }

  navigateToAdd() {
    this.router.navigate(['/usuarios/novo']);
  }

  editEmployee(id: number) {
    this.router.navigate(['/usuarios/editar', id]);
  }

  deleteEmployee(id: number) {
    if (confirm('Tem certeza que deseja excluir este colaborador?')) {
      this.isLoading = true;
      this.userService.delete(id).subscribe({
        next: () => {
          alert('Colaborador excluído.');
          this.loadEmployees();
        },
        error: () => {
          alert('Erro ao excluir.');
          this.isLoading = false;
        }
      });
    }
  }

  toggleStatus(user: any) {
    if (confirm(`Alterar status de ${user.name}?`)) {
        const novoStatus = !user.active;
        this.userService.update(user.id, { active: novoStatus }).subscribe(() => {
            user.active = novoStatus;
        });
    }
  }
}
