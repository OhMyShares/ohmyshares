import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CoreModule } from '../../../core/core.module';
import { ActionsModule } from '../../actions/actions.module';
import { PortfolioImportComponent } from './portfolio-import.component';
import { PortfolioImportResolver } from './portfolio-import.resolver';
import {
  TuiDataListWrapperModule,
  TuiFieldErrorPipeModule,
  TuiInputFilesModule,
  TuiInputModule,
  TuiIslandModule, TuiSelectModule
} from "@taiga-ui/kit";
import {TuiButtonModule, TuiDataListModule, TuiErrorModule} from "@taiga-ui/core";
import {TuiLetModule} from "@taiga-ui/cdk";


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
    TuiIslandModule,
    TuiInputFilesModule,
    TuiDataListModule,
    TuiDataListWrapperModule,
    TuiErrorModule,
    TuiFieldErrorPipeModule,
    TuiInputModule,
    TuiLetModule,
    TuiSelectModule,
    TuiButtonModule
  ]
})
export class PortfolioImportModule {
}
