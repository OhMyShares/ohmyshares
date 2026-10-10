import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { SignupComponent } from './signup.component';
import {TuiCheckboxLabeledModule, TuiFieldErrorPipeModule, TuiInputModule, TuiInputPasswordModule} from '@taiga-ui/kit';
import {TuiButtonModule, TuiErrorModule, TuiGroupModule} from '@taiga-ui/core';
import {NgHcaptchaModule} from 'ng-hcaptcha';
import {environment} from '../../../environments/environment';


@NgModule({
  declarations: [SignupComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: SignupComponent}
    ]),
    ReactiveFormsModule,
    CoreModule,
    ActionsModule,
    TuiInputModule,
    TuiButtonModule,
    TuiInputPasswordModule,
    TuiGroupModule,
    TuiCheckboxLabeledModule,
    TuiErrorModule,
    TuiFieldErrorPipeModule,
    NgHcaptchaModule.forRoot({
      siteKey: environment.H_CAPTCHA_SITE_KEY,
    })
  ]
})
export class SignupModule {
}
