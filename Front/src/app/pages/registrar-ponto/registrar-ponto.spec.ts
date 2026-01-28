import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RegistrarPonto } from './registrar-ponto.component';

describe('RegistrarPonto', () => {
  let component: RegistrarPonto;
  let fixture: ComponentFixture<RegistrarPonto>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RegistrarPonto]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RegistrarPonto);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
