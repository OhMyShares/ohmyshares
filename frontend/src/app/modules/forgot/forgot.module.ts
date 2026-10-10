import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { ForgotComponent } from './forgot.component';
import {TuiFieldErrorPipeModule, TuiInputModule} from '@taiga-ui/kit';
import {TuiButtonModule, TuiErrorModule} from '@taiga-ui/core';


@NgModule({
  declarations: [ForgotComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: ForgotComponent}
    ]),
    ReactiveFormsModule,
    CoreModule,
    RouterModule,
    ActionsModule,
    TuiInputModule,
    TuiButtonModule,
    TuiErrorModule,
    TuiFieldErrorPipeModule
  ]
})
export class ForgotModule {
}
