import { TuiCardLarge } from "@taiga-ui/layout";
import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {CoreModule} from '../../../core/core.module';
import {ActionsModule} from '../../actions/actions.module';
import {CustomAssetViewComponent} from './custom-asset-view.component';
import {CustomAssetViewResolver} from './custom-asset-view.resolver';
import {CustomAssetsPricesListModule} from '../custom-assets-prices-list/custom-assets-prices-list.module';
import {CustomAssetPriceCardComponent} from '../custom-asset-price-card/custom-asset-price-card.component';
import {CustomAssetPriceCardModule} from '../custom-asset-price-card/custom-asset-price-card.module';
import {CustomAssetPriceCardResolver} from '../custom-asset-price-card/custom-asset-price-card.resolver';
import {CustomAssetCardModule} from '../custom-asset-card/custom-asset-card.module';
import {CustomAssetCardResolver} from '../custom-asset-card/custom-asset-card.resolver';
import {CustomAssetCardComponent} from '../custom-asset-card/custom-asset-card.component';
import { TuiFormatNumberPipe, TuiButton } from '@taiga-ui/core';
import { TuiAxes, TuiLineChart, TuiLineChartHint } from '@taiga-ui/addon-charts';

@NgModule({
  declarations: [CustomAssetViewComponent],
  imports: [
    CommonModule,
    CustomAssetCardModule,
    CustomAssetPriceCardModule,
    RouterModule.forChild([
      {path: '', component: CustomAssetViewComponent, resolve: {data: CustomAssetViewResolver}},
      {
        path: 'edit',
        component: CustomAssetCardComponent,
        resolve: {data: CustomAssetCardResolver}
      },
      {
        path: 'price/:price_id',
        component: CustomAssetPriceCardComponent,
        resolve: {data: CustomAssetPriceCardResolver}
      }
    ]),
    ReactiveFormsModule,
    CoreModule,
    ActionsModule,
    CustomAssetsPricesListModule,
    TuiButton,
    TuiCardLarge,
    TuiFormatNumberPipe,
    TuiAxes,
    TuiLineChart, TuiLineChartHint,
  ]
})
export class CustomAssetViewModule {
}
