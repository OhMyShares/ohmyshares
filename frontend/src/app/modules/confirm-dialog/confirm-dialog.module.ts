import { TuiButton } from "@taiga-ui/core";
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ConfirmDialogComponent } from './confirm-dialog.component';

@NgModule({
  declarations: [ConfirmDialogComponent],
  imports: [
    CommonModule,
    TuiButton,
  ],
  exports: [ConfirmDialogComponent]
})
export class ConfirmDialogModule { }
