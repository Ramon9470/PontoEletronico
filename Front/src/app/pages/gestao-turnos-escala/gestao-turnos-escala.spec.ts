import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GestaoTurnosEscala } from './gestao-turnos-escala.component';

describe('GestaoTurnosEscala', () => {
  let component: GestaoTurnosEscala;
  let fixture: ComponentFixture<GestaoTurnosEscala>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GestaoTurnosEscala]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GestaoTurnosEscala);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
