import { Component, HostBinding, OnDestroy, OnInit } from '@angular/core';
import { UntypedFormControl, UntypedFormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { takeWhile } from 'rxjs/operators';
import { AuthService } from '../../core/services/auth.service';
import { CommonService } from '../../core/services/common.service';
import {SpinnerService} from "../../core/services/spinner.service";
import {environment} from "../../../environments/environment";

@Component({
  selector: 'app-signup',
  templateUrl: './signup.component.html',
  styleUrls: [ './signup.component.scss' ]
})
export class SignupComponent implements OnInit, OnDestroy {
  // @HostBinding('class') classes = '';

  isLoading$ = this.spinner$.isLoading$;
  isLoading = false;

  public signUpForm = new UntypedFormGroup({
    name: new UntypedFormControl('', [
      Validators.required
    ]),
    email: new UntypedFormControl('', [
      Validators.required,
      Validators.email
    ]),
    rules: new UntypedFormControl(false, (environment.PRIVACY_POLICY_URL || environment.USER_AGREEMENT_URL) ? [
      Validators.requiredTrue
    ] : null),
    captcha: new UntypedFormControl('', environment.H_CAPTCHA_SITE_KEY ? [
      Validators.required
    ] : null),
  });
  private isSubscribed = true;

  constructor(
    private authService: AuthService,
    public commonService: CommonService,
    private spinner$: SpinnerService,
    private router: Router,
  ) {
    this.commonService.validationError$
      .pipe(
        takeWhile(() => this.isSubscribed)
      )
      .subscribe((error) =>
        Object.keys(error).forEach((key) => {
          this.signUpForm.get(key).setErrors({ validationError: error[key][0] });
        })
      );
  }

  ngOnInit() {
    if (!environment.SIGNUP_ENABLED) {
      this.router.navigate([ '/login' ]);
      return;
    }

    // Prefill value if exists
    this.signUpForm.get('email').setValue(localStorage.getItem('EMAIL_TYPED'));
    this.isLoading$.subscribe((value) => {
      this.isLoading = value;
    });
  }

  ngOnDestroy() {
    this.isSubscribed = false;
  }

  signUp() {
    if (this.signUpForm.invalid || this.isLoading) {
      return;
    }
    this.authService.signUp({
      name: this.signUpForm.get('name').value,
      email: this.signUpForm.get('email').value,
      captcha: environment.H_CAPTCHA_SITE_KEY ? this.signUpForm.get('captcha').value : 'inactive',
    });
  }

  protected readonly environment = environment;
}
