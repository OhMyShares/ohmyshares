import { TuiCardLarge } from "@taiga-ui/layout";
import {CommonModule} from '@angular/common';
import {NgModule} from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import { TuiNotification, TuiError, TuiLoader, TuiLabel, TuiIcon, TuiLink, TuiButton, TuiCheckbox, TuiInput } from '@taiga-ui/core';
import { TuiBadge, TuiSwitch, TuiTabs, TuiButtonLoading, TuiInputNumber, TuiPassword, TuiTextarea } from '@taiga-ui/kit';
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
    TuiButton,
    ...TuiError,
    TuiLink,
    TuiLoader,
    ...TuiNotification,
    TuiIcon,
    TuiBadge,
    TuiLabel,
    ...TuiError,
    ...TuiInput,
    ...TuiInputNumber, TuiPassword,
    TuiCardLarge,
    ...TuiTabs,
    ...TuiTextarea,
    TuiSwitch, TuiButtonLoading, TuiCheckbox],
})
export class PluginsModule {
}
