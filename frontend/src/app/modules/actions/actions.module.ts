import { TuiTabs, TuiButtonLoading } from "@taiga-ui/kit";
import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ActionsComponent } from './actions.component';
import { BackButtonComponent } from './buttons/back-button/back-button.component';
import { RemoveButtonComponent } from './buttons/remove-button/remove-button.component';
import { SaveButtonComponent } from './buttons/save-button/save-button.component';
import { FormActionsComponent } from './form-actions/form-actions.component';
import { TuiDataList, TuiGroup, TuiButton } from '@taiga-ui/core';


@NgModule({
  declarations: [
    ActionsComponent,
    FormActionsComponent,
    BackButtonComponent,
    RemoveButtonComponent,
    SaveButtonComponent
  ],
  imports: [
    CommonModule,
    ...TuiTabs,
    TuiButton,
    TuiGroup,
    ...TuiDataList,
      TuiButtonLoading
],
  exports: [
    ActionsComponent,
    FormActionsComponent,
    BackButtonComponent,
    RemoveButtonComponent
  ]
})
export class ActionsModule {
}
