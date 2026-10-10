import { Component, ElementRef, HostBinding, OnInit, ViewChild } from '@angular/core';
import { UntypedFormControl, UntypedFormGroup, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import {SpinnerService} from '../../core/services/spinner.service';
import {environment} from '../../../environments/environment';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {
  // @HostBinding('class') classes = '';
  @ViewChild('password') password: ElementRef;

  isLoading$ = this.spinner$.isLoading$;
  isLoading = false;
  environment = environment;

  public loginForm = new UntypedFormGroup({
    email: new UntypedFormControl('', [
      Validators.required,
      Validators.email
    ]),
    password: new UntypedFormControl('', [
      Validators.required
    ])
  });

  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private spinner$: SpinnerService,
  ) {
  }

  ngOnInit() {
    // Check impersonate token
    const token = this.route.snapshot.queryParamMap.get('token');
    if (token) {
      this.impersonate(token);
    }

    // Prefill value if exists
    this.loginForm.get('email').setValue(localStorage.getItem('EMAIL_TYPED'));

    this.isLoading$.subscribe((value) => {
      this.isLoading = value;
    });
  }

  login() {
    if (this.loginForm.invalid || this.isLoading) {
      return;
    }
    const authParams = {
      email: this.loginForm.get('email').value,
      password: this.loginForm.get('password').value
    };
    this.authService.login(authParams, this);
  }

  impersonate(token: string) {
    this.authService.impersonate(token);
  }

  keepTypedEmail($event) {
    localStorage.setItem('EMAIL_TYPED', $event.target?.value);
  }
}
