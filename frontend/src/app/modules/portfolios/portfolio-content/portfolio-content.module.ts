import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {CoreModule} from '../../../core/core.module';
import {ActionsModule} from '../../actions/actions.module';
import {PortfolioContentComponent} from './portfolio-content.component';
import {
  TuiButtonModule,
  TuiDataListModule,
  TuiHintModule,
  TuiHostedDropdownModule,
  TuiScrollbarModule, TuiSvgModule, TuiTextfieldControllerModule
} from '@taiga-ui/core';
import {TuiTableModule} from '@taiga-ui/addon-table';
import {IntersectionObserverModule} from '@ng-web-apis/intersection-observer';
import {TuiInputModule, TuiIslandModule, TuiProgressModule} from '@taiga-ui/kit';

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
    TuiButtonModule,
    TuiTableModule,
    TuiScrollbarModule,
    IntersectionObserverModule,
    TuiHostedDropdownModule,
    TuiDataListModule,
    TuiHintModule,
    TuiSvgModule,
    TuiIslandModule,
    TuiInputModule,
    TuiTextfieldControllerModule,
    TuiProgressModule,
  ]
})
export class PortfolioContentModule {
}
