import { TuiCardLarge } from "@taiga-ui/layout";
import { TuiProgress } from "@taiga-ui/kit";
import { TuiAmountPipe } from "@taiga-ui/addon-commerce";
import { NgModule } from '@angular/core';
import { CommonModule, AsyncPipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PortfolioAnalyticsComponent } from './portfolio-analytics.component';
import {RouterModule} from '@angular/router';
import {ActionsModule} from '../../actions/actions.module';
import { TuiAxes, TuiBarChart, TuiLegendItem, TuiRingChart } from '@taiga-ui/addon-charts';
import { TuiDataList, TuiDropdown, TuiLink, TuiButton, TuiFilterByInputPipe, TuiLabel, TuiInput } from '@taiga-ui/core';
import { TuiComboBox, TuiDataListWrapper } from '@taiga-ui/kit';



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
    FormsModule,
    TuiRingChart,
    TuiCardLarge,
    TuiAmountPipe,
    TuiButton,
    TuiLink,
    TuiAxes,
    TuiBarChart,
    ...TuiDropdown,
    ...TuiDataList,
    ...TuiInput,
    ...TuiComboBox,
    ...TuiDataListWrapper,
    TuiFilterByInputPipe,
    TuiLabel,
    TuiLegendItem,
    ...TuiProgress,
      AsyncPipe
]
})
export class PortfolioAnalyticsModule { }
