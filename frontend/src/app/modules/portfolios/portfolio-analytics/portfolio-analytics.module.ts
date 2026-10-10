import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PortfolioAnalyticsComponent } from './portfolio-analytics.component';
import {RouterModule} from '@angular/router';
import {ActionsModule} from '../../actions/actions.module';
import {TuiAxesModule, TuiBarChartModule, TuiLegendItemModule, TuiRingChartModule} from '@taiga-ui/addon-charts';
import {TuiIslandModule, TuiProgressModule} from '@taiga-ui/kit';
import {TuiMoneyModule} from '@taiga-ui/addon-commerce';
import {TuiButtonModule, TuiDataListModule, TuiHostedDropdownModule, TuiLinkModule} from '@taiga-ui/core';



@NgModule({
  declarations: [PortfolioAnalyticsComponent],
  exports: [
    PortfolioAnalyticsComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {
        path: '',
        component: PortfolioAnalyticsComponent,
        runGuardsAndResolvers: 'always',
      }
    ]),
    ActionsModule,
    TuiRingChartModule,
    TuiIslandModule,
    TuiMoneyModule,
    TuiButtonModule,
    TuiLinkModule,
    TuiAxesModule,
    TuiBarChartModule,
    TuiHostedDropdownModule,
    TuiDataListModule,
    TuiLegendItemModule,
    TuiProgressModule,
  ]
})
export class PortfolioAnalyticsModule { }
