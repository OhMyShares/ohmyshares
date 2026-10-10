import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {TuiTableModule, TuiTablePaginationModule} from '@taiga-ui/addon-table';
import {
  TuiButtonModule,
  TuiLinkModule,
  TuiLoaderModule,
  TuiNotificationModule,
  TuiScrollbarModule,
  TuiTextfieldControllerModule
} from '@taiga-ui/core';
import {TuiInputModule} from '@taiga-ui/kit';
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
    TuiTableModule,
    TuiTablePaginationModule,
    TuiButtonModule,
    TuiLinkModule,
    TuiLoaderModule,
    TuiNotificationModule,
    TuiScrollbarModule,
    TuiTextfieldControllerModule,
    TuiInputModule,
  ],
})
export class CurrencyRatesModule {
}
