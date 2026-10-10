import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { ActionsModule } from '../../actions/actions.module';
import { CustomAssetsListComponent } from './custom-assets-list.component';
import {TuiButtonModule, TuiFormatNumberPipeModule, TuiScrollbarModule} from '@taiga-ui/core';
import {IntersectionObserverModule} from '@ng-web-apis/intersection-observer';
import {TuiTableModule} from '@taiga-ui/addon-table';
import {TuiCurrencyPipeModule} from '@taiga-ui/addon-commerce';


@NgModule({
  declarations: [CustomAssetsListComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: CustomAssetsListComponent}
    ]),
    ActionsModule,
    TuiScrollbarModule,
    IntersectionObserverModule,
    TuiTableModule,
    TuiButtonModule,
    TuiCurrencyPipeModule,
    TuiFormatNumberPipeModule,
  ]
})
export class CustomAssetsListModule {
}
