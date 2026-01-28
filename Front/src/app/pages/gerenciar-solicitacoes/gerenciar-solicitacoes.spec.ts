import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GerenciarSolicitacoes } from './gerenciar-solicitacoes.component';

describe('GerenciarSolicitacoes', () => {
  let component: GerenciarSolicitacoes;
  let fixture: ComponentFixture<GerenciarSolicitacoes>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GerenciarSolicitacoes]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GerenciarSolicitacoes);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
