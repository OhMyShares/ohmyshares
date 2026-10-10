import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { ActionsModule } from '../actions/actions.module';
import { ChangePasswordComponent } from './change-password.component';
import {TuiButtonModule, TuiErrorModule} from '@taiga-ui/core';
import {TuiFieldErrorPipeModule, TuiInputPasswordModule, TuiTextAreaModule} from '@taiga-ui/kit';


@NgModule({
  declarations: [ChangePasswordComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: ChangePasswordComponent}
    ]),
    ReactiveFormsModule,
    ActionsModule,
    TuiButtonModule,
    TuiErrorModule,
    TuiTextAreaModule,
    TuiFieldErrorPipeModule,
    TuiInputPasswordModule,
  ]
})
export class ChangePasswordModule {
}
