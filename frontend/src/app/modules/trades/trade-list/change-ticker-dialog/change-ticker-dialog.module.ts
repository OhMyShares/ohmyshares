import { TuiButtonLoading } from "@taiga-ui/kit";
import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {ReactiveFormsModule} from '@angular/forms';
import { TuiDataList, TuiButton, TuiInput, TuiDropdown } from '@taiga-ui/core';
import {ChangeTickerDialogComponent} from './change-ticker-dialog.component';

@NgModule({
  declarations: [ChangeTickerDialogComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    ...TuiInput,
    ...TuiDataList,
    TuiButton,
      TuiButtonLoading,
      ...TuiDropdown
],
})
export class ChangeTickerDialogModule {}
