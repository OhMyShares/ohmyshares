import { TuiCardLarge } from "@taiga-ui/layout";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { DATERANGEPICKER_CONFIG } from '../../../core/constants';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { PortfolioCardComponent } from './portfolio-card.component';
import { PortfolioCardResolver } from './portfolio-card.resolver';
import { TuiDataListWrapper, TuiInputNumber, TuiComboBox, TuiSelect, TuiInputDate, TuiChevron, TuiUnfinishedValidator } from '@taiga-ui/kit';
import { TuiDataList, TuiError, TuiFilterByInputPipe, TuiInput, TuiDropdown } from '@taiga-ui/core';

@NgModule({
  declarations: [PortfolioCardComponent],
    imports: [
        CommonModule,
        RouterModule.forChild([
            {path: '', component: PortfolioCardComponent, resolve: {data: PortfolioCardResolver}}
        ]),
        ReactiveFormsModule,
        CoreModule,
        ActionsModule,
        ...TuiInput,
        ...TuiError,
        TuiCardLarge,
        ...TuiSelect, TuiUnfinishedValidator, TuiChevron,
        ...TuiComboBox,
        TuiFilterByInputPipe,
        ...TuiDataListWrapper,
        ...TuiDataList,
        ...TuiInputDate,
        ...TuiInputNumber,
        ...TuiDropdown
    ]
})
export class PortfolioCardModule {
}
