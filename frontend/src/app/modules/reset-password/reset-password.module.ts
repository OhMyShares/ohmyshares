import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { ResetPasswordComponent } from './reset-password.component';
import {TuiButtonModule} from '@taiga-ui/core';
import {TuiInputModule, TuiInputPasswordModule} from '@taiga-ui/kit';

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
    TuiButtonModule,
    TuiInputModule,
    TuiInputPasswordModule
  ]
})
export class ResetPasswordModule {
}
