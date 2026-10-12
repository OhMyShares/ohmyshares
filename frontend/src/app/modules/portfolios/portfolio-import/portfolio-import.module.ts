import { TuiCardLarge } from "@taiga-ui/layout";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { PortfolioImportComponent } from './portfolio-import.component';
import { PortfolioImportResolver } from './portfolio-import.resolver';
import { TuiDataListWrapper, TuiFiles, TuiSelect, TuiChevron } from "@taiga-ui/kit";
import { TuiDataList, TuiError, TuiButton, TuiInput, TuiDropdown } from "@taiga-ui/core";

@NgModule({
  declarations: [PortfolioImportComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: PortfolioImportComponent, resolve: {data: PortfolioImportResolver}}
    ]),
    ReactiveFormsModule,
    CoreModule,
    ActionsModule,
    TuiCardLarge,
    ...TuiFiles,
    ...TuiDataList,
    ...TuiDataListWrapper,
    ...TuiError,
    ...TuiInput,
    ...TuiSelect, TuiChevron,
    TuiButton,
      ...TuiDropdown
]
})
export class PortfolioImportModule {
}
