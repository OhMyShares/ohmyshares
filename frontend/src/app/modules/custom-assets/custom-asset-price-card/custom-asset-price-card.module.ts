import { TuiCardLarge } from "@taiga-ui/layout";
import { TuiError, TuiInput } from "@taiga-ui/core";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { CustomAssetPriceCardComponent } from './custom-asset-price-card.component';
import { TuiSelect, TuiInputDate, TuiUnfinishedValidator } from '@taiga-ui/kit';

@NgModule({
  declarations: [CustomAssetPriceCardComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    CoreModule,
    ActionsModule,
    TuiCardLarge,
    ...TuiInput,
    ...TuiError,
    ...TuiSelect, TuiUnfinishedValidator,
    ...TuiInputDate
  ]
})
export class CustomAssetPriceCardModule {
}
