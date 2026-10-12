import { WaIntersectionObserver } from "@ng-web-apis/intersection-observer";
import { TuiTable } from "@taiga-ui/addon-table";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { ActionsModule } from '../../actions/actions.module';
import { CustomAssetsPricesListComponent } from './custom-assets-prices-list.component';
import { TuiFormatNumberPipe, TuiScrollbar, TuiScrollable } from '@taiga-ui/core';

@NgModule({
  declarations: [CustomAssetsPricesListComponent],
  exports: [
    CustomAssetsPricesListComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: 'prices', component: CustomAssetsPricesListComponent}
    ]),
    ActionsModule,
    TuiScrollbar, TuiScrollable,
    ...WaIntersectionObserver,
    ...TuiTable,
    TuiFormatNumberPipe,
  ]
})
export class CustomAssetsPricesListModule {
}
