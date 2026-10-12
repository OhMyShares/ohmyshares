import { TuiButtonLoading } from "@taiga-ui/kit";
import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import { TuiTablePagination, TuiTable } from '@taiga-ui/addon-table';
import { TuiNotification, TuiLoader, TuiScrollbar, TuiScrollable, TuiLink, TuiButton, TuiInput } from '@taiga-ui/core';
import {CurrencyRatesListComponent} from './currency-rates-list/currency-rates-list.component';

@NgModule({
  declarations: [
    CurrencyRatesListComponent,
  ],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: CurrencyRatesListComponent},
    ]),
    ReactiveFormsModule,
    ...TuiTable,
    TuiTablePagination,
    TuiButton,
    TuiLink,
    TuiLoader,
    ...TuiNotification,
    TuiScrollbar, TuiScrollable,
    ...TuiInput, TuiButtonLoading],
})
export class CurrencyRatesModule {
}
