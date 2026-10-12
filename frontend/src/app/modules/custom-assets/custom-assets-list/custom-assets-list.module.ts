import { WaIntersectionObserver } from "@ng-web-apis/intersection-observer";
import { TuiCurrencyPipe } from "@taiga-ui/addon-commerce";
import { TuiTable } from "@taiga-ui/addon-table";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { ActionsModule } from '../../actions/actions.module';
import { CustomAssetsListComponent } from './custom-assets-list.component';
import { TuiFormatNumberPipe, TuiScrollbar, TuiScrollable, TuiButton } from '@taiga-ui/core';

@NgModule({
  declarations: [CustomAssetsListComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: CustomAssetsListComponent}
    ]),
    ActionsModule,
    TuiScrollbar, TuiScrollable,
    ...WaIntersectionObserver,
    ...TuiTable,
    TuiButton,
    TuiCurrencyPipe,
    TuiFormatNumberPipe,
  ]
})
export class CustomAssetsListModule {
}
