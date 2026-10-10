import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { DATERANGEPICKER_CONFIG } from '../../../core/constants';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { PortfolioCardComponent } from './portfolio-card.component';
import { PortfolioCardResolver } from './portfolio-card.resolver';
import {
    TuiComboBoxModule,
    TuiDataListWrapperModule,
    TuiFieldErrorPipeModule,
    TuiFilterByInputPipeModule,
    TuiInputDateModule,
    TuiInputModule, TuiInputNumberModule,
    TuiIslandModule,
    TuiSelectModule
} from '@taiga-ui/kit';
import {TuiDataListModule, TuiErrorModule} from '@taiga-ui/core';
import {TuiLetModule} from '@taiga-ui/cdk';


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
        TuiInputModule,
        TuiErrorModule,
        TuiFieldErrorPipeModule,
        TuiIslandModule,
        TuiSelectModule,
        TuiComboBoxModule,
        TuiFilterByInputPipeModule,
        TuiDataListWrapperModule,
        TuiDataListModule,
        TuiInputDateModule,
        TuiLetModule,
        TuiInputNumberModule
    ]
})
export class PortfolioCardModule {
}
