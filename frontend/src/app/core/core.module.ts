import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
// import { MatDialogModule } from '@angular/material/dialog';
// import { MatSnackBarModule } from '@angular/material/snack-bar';
import { RouterModule } from '@angular/router';
import { ConfirmDialogModule } from '../modules/confirm-dialog/confirm-dialog.module';
import { SafeHtmlPipe } from './pipes/safe-html.pipe';
import {numberColoredPipe} from './pipes/numberColored.pipe';

@NgModule({
  declarations: [
    SafeHtmlPipe,
    numberColoredPipe,
  ],
  imports: [
    CommonModule,
    RouterModule,
    HttpClientModule,
    // MatSnackBarModule,
    // MatDialogModule,
    ConfirmDialogModule
  ],
  exports: [
    SafeHtmlPipe,
    numberColoredPipe,
  ]
})
export class CoreModule {
}
