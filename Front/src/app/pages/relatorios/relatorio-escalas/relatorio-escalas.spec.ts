import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RelatorioEscalas } from './relatorio-escalas.component';

describe('RelatorioEscalas', () => {
  let component: RelatorioEscalas;
  let fixture: ComponentFixture<RelatorioEscalas>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RelatorioEscalas]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RelatorioEscalas);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
