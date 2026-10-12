import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { SignupComponent } from './signup.component';
import { TuiButtonLoading, TuiPassword } from '@taiga-ui/kit';
import { TuiError, TuiGroup, TuiLabel, TuiButton, TuiCheckbox, TuiIcon, TuiInput } from '@taiga-ui/core';
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
    ...TuiInput,
    TuiButton,
    TuiIcon, TuiPassword,
    TuiGroup,
    TuiLabel,
    ...TuiError,
    NgHcaptchaModule.forRoot({
      siteKey: environment.H_CAPTCHA_SITE_KEY,
    }), TuiButtonLoading, TuiCheckbox]
})
export class SignupModule {
}
