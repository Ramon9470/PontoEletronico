import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Ferias } from './ferias.component';

describe('Ferias', () => {
  let component: Ferias;
  let fixture: ComponentFixture<Ferias>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Ferias]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Ferias);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
