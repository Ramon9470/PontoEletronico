import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EspelhoPonto } from './espelho-ponto.component';

describe('EspelhoPonto', () => {
  let component: EspelhoPonto;
  let fixture: ComponentFixture<EspelhoPonto>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EspelhoPonto]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EspelhoPonto);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
