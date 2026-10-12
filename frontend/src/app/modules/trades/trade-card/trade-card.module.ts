import { TuiCardLarge } from "@taiga-ui/layout";
import { TuiCurrencyPipe } from "@taiga-ui/addon-commerce";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
// import { MatAutocompleteModule } from '@angular/material/autocomplete';
// import { MatCardModule } from '@angular/material/card';
// import { MatCheckboxModule } from '@angular/material/checkbox';
// import { MatFormFieldModule } from '@angular/material/form-field';
// import { MatInputModule } from '@angular/material/input';
// import { MatSelectModule } from '@angular/material/select';
import { RouterModule } from '@angular/router';
// import { NgxDaterangepickerMd } from 'ngx-daterangepicker-material';
// import { DATERANGEPICKER_CONFIG } from '../../../core/constants';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { TradeCardComponent } from './trade-card.component';
// import { TradeCardResolver } from './trade-card.resolver';
import { TuiDataListWrapper, TuiBlock, TuiInputNumber, TuiComboBox, TuiSelect, TuiInputDate, TuiChevron, TuiUnfinishedValidator } from '@taiga-ui/kit';
import { TuiDataList, TuiError, TuiLoader, TuiGroup, TuiLabel, TuiLink, TuiCheckbox, TuiRadio, TuiFilterByInputPipe, TuiInput, TuiDropdown } from '@taiga-ui/core';

@NgModule({
  declarations: [TradeCardComponent],
    imports: [
        CommonModule,
        RouterModule.forChild([
            {path: '', component: TradeCardComponent}
        ]),
        // MatCardModule,
        ReactiveFormsModule,
        // MatFormFieldModule,
        // MatInputModule,
        // MatSelectModule,
        // NgxDaterangepickerMd.forRoot(DATERANGEPICKER_CONFIG),
        // MatAutocompleteModule,
        // MatCheckboxModule,
        CoreModule,
        ActionsModule,
        FormsModule,
        TuiCardLarge,
        ...TuiInput,
        ...TuiError,
        ...TuiSelect,
        ...TuiDataList,
        ...TuiInputDate,
        TuiLoader,
        TuiGroup,
        TuiBlock, ...TuiRadio,
        TuiLabel,
        ...TuiComboBox,
        ...TuiDataListWrapper,
        TuiFilterByInputPipe,
        ...TuiInputNumber,
        TuiCurrencyPipe,
        TuiLink, TuiCheckbox, ...TuiDropdown,
        TuiChevron,
        TuiUnfinishedValidator,
    ]
})
export class TradeCardModule {
}
