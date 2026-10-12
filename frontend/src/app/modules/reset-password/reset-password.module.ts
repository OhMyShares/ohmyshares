import { TuiButtonLoading, TuiPassword } from "@taiga-ui/kit";
import { TuiButton, TuiIcon, TuiInput } from "@taiga-ui/core";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { ResetPasswordComponent } from './reset-password.component';

@NgModule({
  declarations: [ResetPasswordComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: ResetPasswordComponent}
    ]),
    ReactiveFormsModule,
    CoreModule,
    RouterModule,
    ActionsModule,
    TuiButton,
    ...TuiInput,
    TuiIcon, TuiPassword,
      TuiButtonLoading
]
})
export class ResetPasswordModule {
}
