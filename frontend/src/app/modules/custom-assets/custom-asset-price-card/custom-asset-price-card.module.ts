import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { CustomAssetPriceCardComponent } from './custom-asset-price-card.component';
import {TuiFieldErrorPipeModule, TuiInputDateModule, TuiInputModule, TuiIslandModule, TuiSelectModule} from '@taiga-ui/kit';
import {TuiErrorModule} from '@taiga-ui/core';
import {TuiLetModule} from '@taiga-ui/cdk';


@NgModule({
  declarations: [CustomAssetPriceCardComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    CoreModule,
    ActionsModule,
    TuiIslandModule,
    TuiInputModule,
    TuiFieldErrorPipeModule,
    TuiErrorModule,
    TuiSelectModule,
    TuiLetModule,
    TuiInputDateModule
  ]
})
export class CustomAssetPriceCardModule {
}
