import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ConfirmDialogComponent } from './confirm-dialog.component';
import {TuiButtonModule} from '@taiga-ui/core';



@NgModule({
  declarations: [ConfirmDialogComponent],
  imports: [
    CommonModule,
    TuiButtonModule,
  ],
  exports: [ConfirmDialogComponent]
})
export class ConfirmDialogModule { }
