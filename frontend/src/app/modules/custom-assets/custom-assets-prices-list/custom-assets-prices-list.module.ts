import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { ActionsModule } from '../../actions/actions.module';
import { CustomAssetsPricesListComponent } from './custom-assets-prices-list.component';
import {TuiFormatNumberPipeModule, TuiScrollbarModule} from '@taiga-ui/core';
import {IntersectionObserverModule} from '@ng-web-apis/intersection-observer';
import {TuiTableModule} from '@taiga-ui/addon-table';


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
    TuiScrollbarModule,
    IntersectionObserverModule,
    TuiTableModule,
    TuiFormatNumberPipeModule,
  ]
})
export class CustomAssetsPricesListModule {
}
