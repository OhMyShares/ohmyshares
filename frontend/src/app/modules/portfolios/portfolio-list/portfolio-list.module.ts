import { WaIntersectionObserver } from "@ng-web-apis/intersection-observer";
import { TuiTable } from "@taiga-ui/addon-table";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { PortfolioListComponent } from './portfolio-list.component';
import { TuiFormatNumberPipe, TuiScrollbar, TuiScrollable, TuiButton } from '@taiga-ui/core';

@NgModule({
  declarations: [PortfolioListComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: PortfolioListComponent}
    ]),
    CoreModule,
    ActionsModule,
    TuiButton,
    ...TuiTable,
    TuiFormatNumberPipe,
    TuiScrollbar, TuiScrollable,
    ...WaIntersectionObserver
  ]
})
export class PortfolioListModule {
}
