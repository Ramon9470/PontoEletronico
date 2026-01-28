import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RelatorioAfastamentos } from './relatorio-afastamentos.component';

describe('RelatorioAfastamentos', () => {
  let component: RelatorioAfastamentos;
  let fixture: ComponentFixture<RelatorioAfastamentos>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RelatorioAfastamentos]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RelatorioAfastamentos);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
