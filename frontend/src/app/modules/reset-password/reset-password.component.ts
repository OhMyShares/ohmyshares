import { Component, ElementRef, HostBinding, OnInit, ViewChild } from '@angular/core';
import { UntypedFormControl, UntypedFormGroup, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import {SpinnerService} from "../../core/services/spinner.service";

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss']
})
export class ResetPasswordComponent implements OnInit {
  // @HostBinding('class') classes = '';
  @ViewChild('password') password: ElementRef;
  @ViewChild('password_confirmation') passwordConfirmation: ElementRef;

  isLoading$ = this.spinner$.isLoading$;
  isLoading = false;

  public resetPasswordForm = new UntypedFormGroup({
    email: new UntypedFormControl('', [
      Validators.required,
      Validators.email
    ]),
    password: new UntypedFormControl('', [
      Validators.required
    ]),
    password_confirmation: new UntypedFormControl('', [
      Validators.required
    ])
  });

  private token: string;

  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private spinner$: SpinnerService,
  ) {
  }

  ngOnInit() {
    this.token = this.route.snapshot.queryParamMap.get('token');
    this.isLoading$.subscribe((value) => {
      this.isLoading = value;
    });
  }

  submit() {
    if (this.resetPasswordForm.invalid || this.isLoading) {
      return;
    }
    const resetParams = {
      token: this.token,
      email: this.resetPasswordForm.get('email').value,
      password: this.resetPasswordForm.get('password').value,
      password_confirmation: this.resetPasswordForm.get('password_confirmation').value,
    };
    this.authService.resetPassword(resetParams, this);
  }
}
