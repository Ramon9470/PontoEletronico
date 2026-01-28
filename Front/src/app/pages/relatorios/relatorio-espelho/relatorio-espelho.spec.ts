import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RelatorioEspelho } from './relatorio-espelho.component';

describe('RelatorioEspelho', () => {
  let component: RelatorioEspelho;
  let fixture: ComponentFixture<RelatorioEspelho>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RelatorioEspelho]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RelatorioEspelho);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
