import { TuiCardLarge } from "@taiga-ui/layout";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../core/core.module';
import { ActionsModule } from '../actions/actions.module';
import { ProfileComponent } from './profile.component';
import { TuiDataListWrapper, TuiSwitch, TuiTabs, TuiButtonLoading, TuiSelect, TuiChevron } from '@taiga-ui/kit';
import { TuiError, TuiLabel, TuiButton, TuiCheckbox, TuiInput, TuiDropdown } from '@taiga-ui/core';


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
        ...TuiInput,
        ReactiveFormsModule,
        TuiLabel,
        TuiButton,
        ...TuiSelect, TuiChevron,
        ...TuiDataListWrapper,
        TuiCardLarge,
        ...TuiTabs,
        TuiSwitch,
        ...TuiError, TuiButtonLoading, TuiCheckbox, ...TuiDropdown],
})
export class ProfileModule {
}
