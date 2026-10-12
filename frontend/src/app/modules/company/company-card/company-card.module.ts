import { TuiCardLarge } from "@taiga-ui/layout";
import { TuiCurrencyPipe } from "@taiga-ui/addon-commerce";
import { TuiTable } from "@taiga-ui/addon-table";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { CompanyCardComponent } from './company-card.component';
// import { CompanyCardResolver } from './company-card.resolver';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import { TuiFormatNumberPipe, TuiButton } from '@taiga-ui/core';
import { TuiAxes, TuiBarChart } from '@taiga-ui/addon-charts';

@NgModule({
  declarations: [CompanyCardComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {
        path: '',
        component: CompanyCardComponent,
        runGuardsAndResolvers: 'always',
      }
    ]),
    CoreModule,
    ActionsModule,
    FormsModule,
    ReactiveFormsModule,
    ...TuiTable,
    TuiCardLarge,
    TuiFormatNumberPipe,
    TuiAxes,
    TuiBarChart,
    TuiCurrencyPipe,
    TuiButton,
  ]
})
export class CompanyCardModule {
}
