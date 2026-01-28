import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RelatorioFerias } from './relatorio-ferias.component';

describe('RelatorioFerias', () => {
  let component: RelatorioFerias;
  let fixture: ComponentFixture<RelatorioFerias>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RelatorioFerias]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RelatorioFerias);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
