import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SolicitarAjuste } from './solicitar-ajuste.component';

describe('SolicitarAjuste', () => {
  let component: SolicitarAjuste;
  let fixture: ComponentFixture<SolicitarAjuste>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SolicitarAjuste]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SolicitarAjuste);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
