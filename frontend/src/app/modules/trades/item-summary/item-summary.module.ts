import { TuiCardLarge } from "@taiga-ui/layout";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ItemSummaryComponent } from './item-summary.component';
import {CoreModule} from '../../../core/core.module';

@NgModule({
  declarations: [ItemSummaryComponent],
  imports: [
    CommonModule,
    CoreModule,
    TuiCardLarge
  ],
  exports: [
    ItemSummaryComponent
  ]
})
export class ItemSummaryModule {
}
