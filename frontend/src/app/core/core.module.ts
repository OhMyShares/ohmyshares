import { CommonModule } from '@angular/common';
import { provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';
import { NgModule } from '@angular/core';
// import { MatDialogModule } from '@angular/material/dialog';
// import { MatSnackBarModule } from '@angular/material/snack-bar';
import { RouterModule } from '@angular/router';
import { ConfirmDialogModule } from '../modules/confirm-dialog/confirm-dialog.module';
import { SafeHtmlPipe } from './pipes/safe-html.pipe';
import {numberColoredPipe} from './pipes/numberColored.pipe';

@NgModule({ declarations: [
        SafeHtmlPipe,
        numberColoredPipe,
    ],
    exports: [
        SafeHtmlPipe,
        numberColoredPipe,
    ], imports: [CommonModule,
        RouterModule,
        // MatSnackBarModule,
        // MatDialogModule,
        ConfirmDialogModule], providers: [provideHttpClient(withInterceptorsFromDi())] })
export class CoreModule {
}
