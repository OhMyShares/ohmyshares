import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { LoginComponent } from './login.component';
import {TuiFieldErrorPipeModule, TuiInputModule, TuiInputPasswordModule} from '@taiga-ui/kit';
import {TuiButtonModule, TuiErrorModule, TuiPrimitiveTextfieldModule} from '@taiga-ui/core';


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
    TuiInputModule,
    TuiPrimitiveTextfieldModule,
    TuiButtonModule,
    TuiInputPasswordModule,
    TuiErrorModule,
    TuiFieldErrorPipeModule,
  ]
})
export class LoginModule {
}
