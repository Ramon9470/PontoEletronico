import { TestBed } from '@angular/core/testing';

import { AfastamentoService } from './afastamento.service';

describe('AfastamentoService', () => {
  let service: AfastamentoService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AfastamentoService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
