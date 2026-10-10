import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { CompanyListComponent } from './company-list.component';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {TuiScrollbarModule, TuiTextfieldControllerModule} from '@taiga-ui/core';
import {TuiTableModule} from '@taiga-ui/addon-table';
import {TuiInputModule} from '@taiga-ui/kit';
import {IntersectionObserverModule} from '@ng-web-apis/intersection-observer';


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
    TuiScrollbarModule,
    TuiTableModule,
    TuiInputModule,
    TuiTextfieldControllerModule,
    IntersectionObserverModule
  ]
})
export class CompanyListModule {
}
