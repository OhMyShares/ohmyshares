import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { LoginComponent } from './login.component';
import { TuiButtonLoading, TuiPassword } from '@taiga-ui/kit';
import { TuiError, TuiButton, TuiIcon, TuiInput, TuiTextfield } from '@taiga-ui/core';


@NgModule({
  declarations: [LoginComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: LoginComponent}
    ]),
    ReactiveFormsModule,
    CoreModule,
    RouterModule,
    ActionsModule,
    ...TuiInput,
    ...TuiTextfield,
    TuiButton,
    TuiIcon, TuiPassword,
    ...TuiError, TuiButtonLoading]
})
export class LoginModule {
}
