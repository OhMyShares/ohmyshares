import { TuiLabel, TuiRadio } from "@taiga-ui/core";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';


import { FilterDialogComponent } from './filter-dialog.component';

@NgModule({
    declarations: [FilterDialogComponent],
    imports: [
        ReactiveFormsModule,
        CommonModule,
        TuiLabel,
        ...TuiRadio
    ]
})
export class FilterDialogModule {}
