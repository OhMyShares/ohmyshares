import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { ForgotComponent } from './forgot.component';
import { TuiButtonLoading } from '@taiga-ui/kit';
import { TuiError, TuiButton, TuiInput } from '@taiga-ui/core';


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
    ...TuiInput,
    TuiButton,
    ...TuiError, TuiButtonLoading]
})
export class ForgotModule {
}
