import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { ProfileComponent } from './profile.component';
import {
    TuiCheckboxLabeledModule,
    TuiDataListWrapperModule, TuiFieldErrorPipeModule,
    TuiInputModule,
    TuiIslandModule,
    TuiSelectModule,
    TuiTabsModule, TuiToggleModule
} from '@taiga-ui/kit';
import {TuiButtonModule, TuiErrorModule} from '@taiga-ui/core';


@NgModule({
  declarations: [ProfileComponent],
    imports: [
        CommonModule,
        RouterModule.forChild([
            {path: '', component: ProfileComponent}
        ]),
        CoreModule,
        FormsModule,
        ActionsModule,
        TuiInputModule,
        ReactiveFormsModule,
        TuiCheckboxLabeledModule,
        TuiButtonModule,
        TuiSelectModule,
        TuiDataListWrapperModule,
        TuiIslandModule,
        TuiTabsModule,
        TuiToggleModule,
        TuiErrorModule,
        TuiFieldErrorPipeModule,
    ],
})
export class ProfileModule {
}
