import { TuiInputDateRange } from "@taiga-ui/kit";
import { TuiCardLarge } from "@taiga-ui/layout";
import { WaIntersectionObserver } from "@ng-web-apis/intersection-observer";
import { ScrollingModule } from '@angular/cdk/scrolling';
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { ItemSummaryModule } from '../item-summary/item-summary.module';
import { FilterDialogModule } from './filter-dialog/filter-dialog.module';
import { ChangeTickerDialogModule } from './change-ticker-dialog/change-ticker-dialog.module';
import { TradeListComponent } from './trade-list.component';
import { TuiTablePagination, TuiTable } from '@taiga-ui/addon-table';
import { TuiDataList, TuiScrollbar, TuiScrollable, TuiDropdown, TuiButton } from '@taiga-ui/core';
import { NgApexchartsModule } from 'ng-apexcharts';

@NgModule({
  declarations: [ TradeListComponent ],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: TradeListComponent}
    ]),
    FilterDialogModule,
    ChangeTickerDialogModule,
    CoreModule,
    ScrollingModule,
    ReactiveFormsModule,
    ActionsModule,
    ItemSummaryModule,
    ...TuiTable,
    ...WaIntersectionObserver,
    TuiScrollbar, TuiScrollable,
    TuiTablePagination,
    TuiButton,
    ...TuiDropdown,
    ...TuiDataList,
    ...TuiInputDateRange,
    TuiCardLarge,
    NgApexchartsModule,
  ],
})
export class TradeListModule {
}
