import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';


import { FilterDialogComponent } from './filter-dialog.component';
import {TuiRadioLabeledModule} from '@taiga-ui/kit';

@NgModule({
    declarations: [FilterDialogComponent],
    imports: [
        ReactiveFormsModule,
        CommonModule,
        TuiRadioLabeledModule
    ]
})
export class FilterDialogModule {}
