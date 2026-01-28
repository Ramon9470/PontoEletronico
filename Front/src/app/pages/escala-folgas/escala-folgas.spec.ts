import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EscalaFolgas } from './escala-folgas.component';

describe('EscalaFolgas', () => {
  let component: EscalaFolgas;
  let fixture: ComponentFixture<EscalaFolgas>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EscalaFolgas]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EscalaFolgas);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
