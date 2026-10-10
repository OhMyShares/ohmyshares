import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {
  TuiButtonModule,
  TuiErrorModule,
  TuiLinkModule,
  TuiLoaderModule,
  TuiNotificationModule,
  TuiSvgModule,
  TuiTextfieldControllerModule
} from '@taiga-ui/core';
import {
  TuiBadgeModule,
  TuiCheckboxLabeledModule,
  TuiFieldErrorPipeModule,
  TuiInputModule,
  TuiInputNumberModule,
  TuiInputPasswordModule,
  TuiIslandModule,
  TuiTabsModule,
  TuiTextAreaModule,
  TuiToggleModule
} from '@taiga-ui/kit';
import {ActionsModule} from '../actions/actions.module';
import {PluginBadgesComponent} from './plugin-badges/plugin-badges.component';
import {PluginCardComponent} from './plugin-card/plugin-card.component';
import {PluginsListComponent} from './plugins-list/plugins-list.component';

@NgModule({
  declarations: [
    PluginsListComponent,
    PluginCardComponent,
    PluginBadgesComponent,
  ],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: PluginsListComponent},
      {path: ':id', component: PluginCardComponent},
    ]),
    FormsModule,
    ReactiveFormsModule,
    ActionsModule,
    TuiButtonModule,
    TuiErrorModule,
    TuiLinkModule,
    TuiLoaderModule,
    TuiNotificationModule,
    TuiSvgModule,
    TuiTextfieldControllerModule,
    TuiBadgeModule,
    TuiCheckboxLabeledModule,
    TuiFieldErrorPipeModule,
    TuiInputModule,
    TuiInputNumberModule,
    TuiInputPasswordModule,
    TuiIslandModule,
    TuiTabsModule,
    TuiTextAreaModule,
    TuiToggleModule,
  ],
})
export class PluginsModule {
}
