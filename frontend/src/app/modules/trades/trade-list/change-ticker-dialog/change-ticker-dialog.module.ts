import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {ReactiveFormsModule} from '@angular/forms';
import {TuiButtonModule, TuiDataListModule, TuiTextfieldControllerModule} from '@taiga-ui/core';
import {TuiInputModule} from '@taiga-ui/kit';
import {ChangeTickerDialogComponent} from './change-ticker-dialog.component';

@NgModule({
  declarations: [ChangeTickerDialogComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TuiInputModule,
    TuiDataListModule,
    TuiTextfieldControllerModule,
    TuiButtonModule,
  ],
})
export class ChangeTickerDialogModule {}
