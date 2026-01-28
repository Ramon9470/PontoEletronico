import { TestBed } from '@angular/core/testing';

import { EspelhoService } from './espelho.service';

describe('EspelhoService', () => {
  let service: EspelhoService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(EspelhoService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
