import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { PortfolioListComponent } from './portfolio-list.component';
import {TuiButtonModule, TuiFormatNumberPipeModule, TuiScrollbarModule} from '@taiga-ui/core';
import {TuiTableModule} from '@taiga-ui/addon-table';
import {IntersectionObserverModule} from '@ng-web-apis/intersection-observer';


@NgModule({
  declarations: [PortfolioListComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: PortfolioListComponent}
    ]),
    CoreModule,
    ActionsModule,
    TuiButtonModule,
    TuiTableModule,
    TuiFormatNumberPipeModule,
    TuiScrollbarModule,
    IntersectionObserverModule
  ]
})
export class PortfolioListModule {
}
