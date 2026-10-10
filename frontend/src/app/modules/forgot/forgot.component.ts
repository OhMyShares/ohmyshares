import { Component, ElementRef, HostBinding, OnInit, ViewChild } from '@angular/core';
import { UntypedFormControl, UntypedFormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../core/services/auth.service';
import {SpinnerService} from "../../core/services/spinner.service";

@Component({
  selector: 'app-forgot',
  templateUrl: './forgot.component.html',
  styleUrls: [ './forgot.component.scss' ]
})
export class ForgotComponent implements OnInit {
  // @HostBinding('class') classes = '';
  @ViewChild('password') password: ElementRef;
  isLoading$ = this.spinner$.isLoading$;
  isLoading = false;

  public forgotForm = new UntypedFormGroup({
    email: new UntypedFormControl('', [
      Validators.required,
      Validators.email
    ])
  });

  constructor(
    private authService: AuthService,
    private spinner$: SpinnerService,
  ) {
  }

  ngOnInit() {
    // Prefill value if exists
    this.forgotForm.get('email').setValue(localStorage.getItem('EMAIL_TYPED'));
    this.isLoading$.subscribe((value) => {
      this.isLoading = value;
    });
  }

  forgot() {
    if (this.forgotForm.invalid || this.isLoading) {
      return;
    }
    const formParams = {
      email: this.forgotForm.get('email').value,
    };
    this.authService.forgot(formParams);
  }

    protected readonly history = history;
}
