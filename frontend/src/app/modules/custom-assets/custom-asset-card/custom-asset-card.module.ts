import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { CustomAssetCardComponent } from './custom-asset-card.component';
import {
  TuiComboBoxModule,
  TuiDataListWrapperModule,
  TuiFieldErrorPipeModule,
  TuiFilterByInputPipeModule,
  TuiInputModule,
  TuiIslandModule,
  TuiSelectModule
} from '@taiga-ui/kit';
import {TuiDataListModule, TuiErrorModule} from '@taiga-ui/core';
import {TuiLetModule} from '@taiga-ui/cdk';


@NgModule({
  declarations: [CustomAssetCardComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    CoreModule,
    ActionsModule,
    TuiIslandModule,
    TuiInputModule,
    TuiErrorModule,
    TuiSelectModule,
    TuiComboBoxModule,
    TuiDataListWrapperModule,
    TuiFilterByInputPipeModule,
    TuiFieldErrorPipeModule,
    TuiDataListModule,
    TuiLetModule
  ]
})
export class CustomAssetCardModule {
}
