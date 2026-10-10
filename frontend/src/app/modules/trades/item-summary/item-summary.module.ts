import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ItemSummaryComponent } from './item-summary.component';
import {CoreModule} from '../../../core/core.module';
import {TuiIslandModule} from '@taiga-ui/kit';


@NgModule({
  declarations: [ItemSummaryComponent],
  imports: [
    CommonModule,
    CoreModule,
    TuiIslandModule
  ],
  exports: [
    ItemSummaryComponent
  ]
})
export class ItemSummaryModule {
}
