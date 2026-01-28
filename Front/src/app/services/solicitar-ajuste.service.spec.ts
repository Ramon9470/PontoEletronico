import { TestBed } from '@angular/core/testing';

import { SolicitarAjusteService } from './solicitar-ajuste.service';

describe('SolicitarAjusteService', () => {
  let service: SolicitarAjusteService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(SolicitarAjusteService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
