import { Component, OnInit, inject, NgZone, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute, RouterModule } from '@angular/router';
import { UserService } from '../../services/user.service'; 
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-tela-registro',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './tela-registro.component.html',
  styleUrls: ['./tela-registro.component.scss']
})
export class TelaRegistroComponent implements OnInit {

  tituloPagina: string = 'Novo Colaborador';
  isLoading = false;
  isEditing = false;
  userId: number | null = null;
  
  fotoPreview: string | null = null;
  selectedFile: File | null = null;

  funcionario: any = {
    status: 'Ativo',
    usuario: '',     
    senha: '',       
    perfil: 'funcionario', 
    nome: '',        
    email: '',
    cargo: '',
    departamento: '',
    cpf: '',
    ctps: '',
    genero: 'Masculino',
    whatsapp: '',
    cep: '',
    endereco: '',    
    numero: '',
    complemento: '',
    bairro: '',
    cidade: '',
    uf: ''
  };

  constructor(
    private userService: UserService,
    private router: Router,
    private route: ActivatedRoute,
    private zone: NgZone,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit() {
    this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      if (id) {
        this.isEditing = true;
        this.tituloPagina = 'Editar Colaborador';
        this.userId = +id;
        this.carregarDados(this.userId);
      }
    });
  }

  carregarDados(id: number) {
    this.isLoading = true;
    this.userService.getById(id).subscribe({
      next: (data) => {
        this.funcionario = {
            status: data.active ? 'Ativo' : 'Inativo',
            usuario: data.username,
            senha: '', 
            perfil: data.role || 'funcionario',
            nome: data.name,        
            email: data.email,
            cargo: data.cargo,
            departamento: data.departamento,
            cpf: data.cpf,
            ctps: data.ctps,
            genero: data.genero || 'Masculino',
            whatsapp: data.whatsapp,
            cep: data.cep,
            endereco: data.logradouro,
            numero: data.numero,
            complemento: data.complemento,
            bairro: data.bairro,
            cidade: data.cidade,
            uf: data.uf
        };
        
        // URL DA FOTO HTTPS
        if (data.foto_url) {
             let url = data.foto_url;
             
             if (url.includes(':8000')) url = url.replace(':8000', '');
             
             if (url.startsWith('http:')) {
                 url = url.replace('http:', 'https:');
             } else if (!url.startsWith('http')) {
                 url = `https://localhost/storage/${url}`;
             }

             this.fotoPreview = url;
        }
        
        this.isLoading = false;
      },
      error: () => {
        alert('Erro ao carregar dados do usuário.');
        this.cancelar();
      }
    });
  }

  salvar() {
    this.isLoading = true;
    const formData = new FormData();
    
    // Campos Obrigatórios
    formData.append('name', this.funcionario.nome);
    formData.append('username', this.funcionario.usuario);
    formData.append('email', this.funcionario.email);
    formData.append('role', this.funcionario.perfil);
    formData.append('active', this.funcionario.status === 'Ativo' ? '1' : '0');
    
    if (this.funcionario.senha) formData.append('password', this.funcionario.senha);
    
    const camposOpcionais = [
        'cargo', 'departamento', 'cpf', 'ctps', 'genero', 'whatsapp',
        'cep', 'numero', 'complemento', 'bairro', 'cidade', 'uf'
    ];

    camposOpcionais.forEach(campo => {
        if (this.funcionario[campo]) {
            formData.append(campo, this.funcionario[campo]);
        }
    });

    if (this.funcionario.endereco) {
        formData.append('logradouro', this.funcionario.endereco);
    }

    if (this.selectedFile) {
        formData.append('foto', this.selectedFile);
    }

    const request$ = (this.isEditing && this.userId)
        ? (() => { 
            formData.append('_method', 'PUT'); 
            return this.userService.postFormData(this.userId, formData); 
          })()
        : this.userService.create(formData);

    request$.pipe(finalize(() => this.isLoading = false)).subscribe({
        next: () => {
            alert('Salvo com sucesso! Biometria processada.');
            this.cancelar();
        },
        error: (err) => {
            console.error(err);
            const mensagemErro = err.error?.message || 'Erro ou Timeout (A IA pode estar lenta na 1ª vez).';
            alert('ATENÇÃO: ' + mensagemErro);
        }
    });
  }

  onFileSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      this.selectedFile = file;
      const reader = new FileReader();
      reader.onload = (e) => {
          this.fotoPreview = e.target?.result as string;
          this.cdr.detectChanges();
      };
      reader.readAsDataURL(file);
    }
  }

  usarImagemPadrao(event: any) {
    this.fotoPreview = null;
  }

  cancelar() {
    this.router.navigate(['/colaboradores']);
  }
  
  buscarCep() {
    const cep = this.funcionario.cep?.replace(/\D/g, '');
    if (cep && cep.length === 8) {
      fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(res => res.json())
        .then(data => {
          if (!data.erro) {
            this.zone.run(() => {
                this.funcionario.endereco = data.logradouro;
                this.funcionario.bairro = data.bairro;
                this.funcionario.cidade = data.localidade;
                this.funcionario.uf = data.uf;
                this.cdr.detectChanges();
            });
          }
        });
    }
  }
}