import { WaIntersectionObserver } from "@ng-web-apis/intersection-observer";
import { TuiTable } from "@taiga-ui/addon-table";
import { TuiScrollbar, TuiScrollable, TuiInput } from "@taiga-ui/core";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { CompanyListComponent } from './company-list.component';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';

@NgModule({
  declarations: [CompanyListComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {
        path: '',
        component: CompanyListComponent,
        runGuardsAndResolvers: 'always',
      }
    ]),
    CoreModule,
    ActionsModule,
    FormsModule,
    ReactiveFormsModule,
    TuiScrollbar, TuiScrollable,
    ...TuiTable,
    ...TuiInput,
    ...WaIntersectionObserver
  ]
})
export class CompanyListModule {
}
