import { Component, HostBinding } from '@angular/core';
import { UntypedFormControl, UntypedFormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../core/services/auth.service';
import { CommonService } from '../../core/services/common.service';
import {SpinnerService} from "../../core/services/spinner.service";

@Component({
  selector: 'app-change-password',
  templateUrl: './change-password.component.html',
  styleUrls: ['./change-password.component.scss']
})
export class ChangePasswordComponent {
  // @HostBinding('class') classes = '';

  public changePasswordForm = new UntypedFormGroup({
    password: new UntypedFormControl('', [
      Validators.required
    ]),
    password_new: new UntypedFormControl('', [
      Validators.required
    ])
  });

  public isLoading = false;

  constructor(
    public commonService: CommonService,
    private authService: AuthService,
    private spinner$: SpinnerService,
  ) {
  }

  ngOnInit() {
    this.spinner$.isLoading$.subscribe((value) => {
      this.isLoading = value;
    });
  }

  changePassword() {
    if (this.changePasswordForm.invalid || this.isLoading) {
      return;
    }
    const authParams = {
      password: this.changePasswordForm.get('password').value,
      password_new: this.changePasswordForm.get('password_new').value
    };
    this.authService.changePassword(authParams);
  }

}
