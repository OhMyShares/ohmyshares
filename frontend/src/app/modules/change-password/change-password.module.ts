import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { ActionsModule } from '../actions/actions.module';
import { ChangePasswordComponent } from './change-password.component';
import { TuiError, TuiButton, TuiIcon, TuiInput } from '@taiga-ui/core';
import { TuiButtonLoading, TuiPassword, TuiTextarea } from '@taiga-ui/kit';


@NgModule({
  declarations: [ChangePasswordComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: ChangePasswordComponent}
    ]),
    ReactiveFormsModule,
    ActionsModule,
    TuiButton,
    ...TuiError,
    ...TuiTextarea,
    ...TuiError,
    TuiIcon, TuiPassword, ...TuiInput, TuiButtonLoading]
})
export class ChangePasswordModule {
}
