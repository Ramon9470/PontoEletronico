import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RelatorioBancoHoras } from './relatorio-banco-horas.component';

describe('RelatorioBancoHoras', () => {
  let component: RelatorioBancoHoras;
  let fixture: ComponentFixture<RelatorioBancoHoras>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RelatorioBancoHoras]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RelatorioBancoHoras);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
