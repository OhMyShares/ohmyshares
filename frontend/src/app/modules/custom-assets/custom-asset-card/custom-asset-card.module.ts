import { TuiCardLarge } from "@taiga-ui/layout";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { CustomAssetCardComponent } from './custom-asset-card.component';
import { TuiDataListWrapper, TuiComboBox, TuiSelect } from '@taiga-ui/kit';
import { TuiDataList, TuiError, TuiFilterByInputPipe, TuiInput, TuiDropdown } from '@taiga-ui/core';

@NgModule({
  declarations: [CustomAssetCardComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    CoreModule,
    ActionsModule,
    TuiCardLarge,
    ...TuiInput,
    ...TuiError,
    ...TuiSelect,
    ...TuiComboBox,
    ...TuiDataListWrapper,
    TuiFilterByInputPipe,
    ...TuiError,
    ...TuiDataList,
      ...TuiDropdown
]
})
export class CustomAssetCardModule {
}
