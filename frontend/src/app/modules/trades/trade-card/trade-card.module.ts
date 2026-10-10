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
import {
  TuiCheckboxLabeledModule, TuiComboBoxModule, TuiDataListWrapperModule,
  TuiFieldErrorPipeModule, TuiFilterByInputPipeModule,
  TuiInputDateModule,
  TuiInputModule, TuiInputNumberModule,
  TuiIslandModule,
  TuiRadioBlockModule, TuiRadioLabeledModule,
  TuiSelectModule
} from '@taiga-ui/kit';
import {TuiDataListModule, TuiErrorModule, TuiGroupModule, TuiLinkModule, TuiLoaderModule} from '@taiga-ui/core';
import {TuiLetModule} from '@taiga-ui/cdk';
import {TuiCurrencyPipeModule} from '@taiga-ui/addon-commerce';


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
        TuiIslandModule,
        TuiInputModule,
        TuiErrorModule,
        TuiFieldErrorPipeModule,
        TuiSelectModule,
        TuiDataListModule,
        TuiInputDateModule,
        TuiLetModule,
        TuiLoaderModule,
        TuiGroupModule,
        TuiRadioBlockModule,
        TuiRadioLabeledModule,
        TuiCheckboxLabeledModule,
        TuiComboBoxModule,
        TuiDataListWrapperModule,
        TuiFilterByInputPipeModule,
        TuiInputNumberModule,
        TuiCurrencyPipeModule,
        TuiLinkModule
    ]
})
export class TradeCardModule {
}
