import { ChangeDetectorRef, Component, OnDestroy, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms'; 
import { WebcamModule, WebcamImage } from 'ngx-webcam';
import { Subject, Observable } from 'rxjs';

import { RegistrarPontoService } from '../../services/registrar-ponto.service';
import { AuthService } from '../../services/auth.service';

interface PointRecord {
  type: string;
  time: string;
  method: string;
  icon: string;
  colorClass: string;
  userName?: string; // Adicionado para exibir quem bateu o ponto
}

@Component({
  selector: 'app-registrar-ponto',
  standalone: true,
  imports: [CommonModule, FormsModule, WebcamModule], 
  templateUrl: './registrar-ponto.component.html',
  styleUrls: ['./registrar-ponto.component.scss']
})
export class RegistrarPontoComponent implements OnInit, OnDestroy {

  pontoService = inject(RegistrarPontoService);
  authService = inject(AuthService);
  cdr = inject(ChangeDetectorRef);

  currentTime: Date = new Date();
  userIp: string = 'Local';
  
  isLoading: boolean = false;
  showSuccessMessage: boolean = false;
  showWarningMessage: boolean = false;
  warningText: string = '';
  successText: string = '';

  // Controle da Câmera
  isCameraOpen = false;
  private trigger = new Subject<void>();
  
  public videoOptions: MediaTrackConstraints = {
    width: {ideal: 640},
    height: {ideal: 480}
  };

  history: PointRecord[] = [];
  private clockInterval: any;
  private updateInterval: any;

  ngOnInit(): void {
    this.startClock();
    this.loadHistory();

    this.updateInterval = setInterval(() => {
       if (!this.isCameraOpen && !this.isLoading){
	 this.loadHistory();
       }
    }, 500);
  }

  ngOnDestroy(): void {
    if (this.clockInterval) clearInterval(this.clockInterval);
    if (this.updateInterval) clearInterval(this.updateInterval);
  }

  startClock() {
    this.clockInterval = setInterval(() => {
      this.currentTime = new Date();
      this.cdr.detectChanges(); 
    }, 1000);
  }

  // LÓGICA DA WEBCAM
  get triggerObservable(): Observable<void> {
    return this.trigger.asObservable();
  }

  openCamera() {
    this.showWarningMessage = false;
    this.showSuccessMessage = false;
    this.isCameraOpen = true;
  }

  closeCamera() {
    this.isCameraOpen = false;
  }

  triggerSnapshot() {
    this.trigger.next();
  }

  handleImage(webcamImage: WebcamImage) {
    this.enviarPontoFacial(webcamImage);
  }

  private dataURItoBlob(dataURI: string) {
    const byteString = window.atob(dataURI.split(',')[1]);
    const arrayBuffer = new ArrayBuffer(byteString.length);
    const int8Array = new Uint8Array(arrayBuffer);
    for (let i = 0; i < byteString.length; i++) {
      int8Array[i] = byteString.charCodeAt(i);
    }
    return new Blob([int8Array], { type: 'image/jpeg' });
  }

  // ENVIO PARA O BACKEND
  enviarPontoFacial(image: WebcamImage) {
    this.isLoading = true;
    
    const imageBlob = this.dataURItoBlob(image.imageAsDataUrl);
    const formData = new FormData();
    formData.append('foto', imageBlob, 'capture.jpg');

    this.pontoService.registrarPontoFacial(formData).subscribe({
      next: (res: any) => {
        this.isLoading = false;
        this.isCameraOpen = false;
        
        // Verifica se houve sucesso
        if (res.success || res.usuario) {
          const nome = res.usuario || 'Colaborador';
          const hora = res.horario || this.currentTime.toLocaleTimeString().substring(0,5);
          
          this.successText = `Ponto registrado: ${nome} às ${hora}`;
          this.showSuccessMessage = true;
          this.loadHistory(); 
          
          setTimeout(() => {
            this.showSuccessMessage = false;
            this.cdr.detectChanges();
          }, 5000);
        }
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.isLoading = false;
        console.error(err);
        this.warningText = err.error?.message || 'Rosto não reconhecido. Tente novamente.';
        this.showWarningMessage = true;
        this.cdr.detectChanges();
      }
    });
  }

  // CARREGAR HISTÓRICO
  loadHistory() {
    if (this.pontoService && this.pontoService.getPontosHoje) {
      this.pontoService.getPontosHoje().subscribe({
        next: (response: any) => {
          if (response.ip) this.userIp = response.ip;
          const lista = response.data || response;

          if (Array.isArray(lista)) {
            this.history = lista.map((registro: any) => {
              const visual = this.getVisualProps(registro.tipo_registro);
              return {
                type: registro.tipo_registro,
                time: String(registro.hora_registro).substring(0, 5),
                method: registro.metodo || 'Web',
                icon: visual.icon,
                colorClass: visual.color,
                userName: registro.nome_usuario // Mostra nome na lista
              };
            });
          }
          this.cdr.detectChanges();
        },
        error: (err) => console.error('Erro ao carregar histórico', err)
      });
    }
  }

  getVisualProps(type: string) {
    if (!type) return { icon: 'bi-circle', color: 'text-secondary border-secondary' };
    const lowerType = type.toLowerCase();
    
    if (lowerType.includes('entrada')) return { icon: 'bi-box-arrow-in-right', color: 'text-success border-success' };
    if (lowerType.includes('intervalo')) return { icon: 'bi-cup-hot', color: 'text-warning border-warning' };
    if (lowerType.includes('volta') || lowerType.includes('retorno')) return { icon: 'bi-briefcase', color: 'text-primary border-primary' };
    if (lowerType.includes('saída') || lowerType.includes('saida')) return { icon: 'bi-box-arrow-right', color: 'text-danger border-danger' };
    
    return { icon: 'bi-circle', color: 'text-secondary border-secondary' };
  }
}
