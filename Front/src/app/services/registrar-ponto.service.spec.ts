import { TestBed } from '@angular/core/testing';

import { RegistrarPontoService } from './registrar-ponto.service';

describe('RegistrarPontoService', () => {
  let service: RegistrarPontoService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(RegistrarPontoService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
