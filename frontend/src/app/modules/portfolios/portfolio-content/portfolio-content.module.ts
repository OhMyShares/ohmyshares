import { TuiCardLarge } from "@taiga-ui/layout";
import { WaIntersectionObserver } from "@ng-web-apis/intersection-observer";
import { TuiTable } from "@taiga-ui/addon-table";
import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {CoreModule} from '../../../core/core.module';
import {ActionsModule} from '../../actions/actions.module';
import {PortfolioContentComponent} from './portfolio-content.component';
import { TuiDataList, TuiScrollbar, TuiScrollable, TuiDropdown, TuiIcon, TuiButton, TuiHint, TuiInput } from '@taiga-ui/core';
import { TuiProgress } from '@taiga-ui/kit';

@NgModule({
  declarations: [PortfolioContentComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {
        path: '',
        component: PortfolioContentComponent,
        runGuardsAndResolvers: 'always',
      }
    ]),
    CoreModule,
    ActionsModule,
    ReactiveFormsModule,
    FormsModule,
    TuiButton,
    ...TuiTable,
    TuiScrollbar, TuiScrollable,
    ...WaIntersectionObserver,
    ...TuiDropdown,
    ...TuiDataList,
    ...TuiHint,
    TuiIcon,
    TuiCardLarge,
    ...TuiInput,
    ...TuiProgress,
  ]
})
export class PortfolioContentModule {
}
