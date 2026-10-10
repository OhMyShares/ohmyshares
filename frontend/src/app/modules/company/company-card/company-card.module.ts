import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { CompanyCardComponent } from './company-card.component';
// import { CompanyCardResolver } from './company-card.resolver';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {TuiTableModule} from '@taiga-ui/addon-table';
import {TuiIslandModule} from '@taiga-ui/kit';
import {TuiButtonModule, TuiFormatNumberPipeModule} from '@taiga-ui/core';
import {TuiAxesModule, TuiBarChartModule} from '@taiga-ui/addon-charts';
import {TuiCurrencyPipeModule} from '@taiga-ui/addon-commerce';

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
    TuiTableModule,
    TuiIslandModule,
    TuiFormatNumberPipeModule,
    TuiAxesModule,
    TuiBarChartModule,
    TuiCurrencyPipeModule,
    TuiButtonModule,
  ]
})
export class CompanyCardModule {
}
