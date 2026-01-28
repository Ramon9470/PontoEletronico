import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Afastamentos } from './afastamentos.component';

describe('Afastamentos', () => {
  let component: Afastamentos;
  let fixture: ComponentFixture<Afastamentos>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Afastamentos]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Afastamentos);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
