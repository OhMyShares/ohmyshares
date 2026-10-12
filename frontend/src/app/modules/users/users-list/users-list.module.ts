import { TuiCardLarge } from "@taiga-ui/layout";
import { WaIntersectionObserver } from "@ng-web-apis/intersection-observer";
import { TuiTable } from "@taiga-ui/addon-table";
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule } from '@angular/forms';
import { UsersListComponent } from './users-list.component';
import { ActionsModule } from '../../actions/actions.module';
import { TuiDataList, TuiError, TuiLoader, TuiScrollbar, TuiScrollable, TuiLabel, TuiIcon, TuiButton, TuiCheckbox, TuiInput, TuiDropdown } from '@taiga-ui/core';
import { TuiPagination, TuiDataListWrapper, TuiBadge, TuiAvatar, TuiSelect, TuiInitialsPipe, TuiPassword, TuiChevron } from '@taiga-ui/kit';
import { UserCardComponent } from '../user-card/user-card.component';
import { UserFormComponent } from '../user-form/user-form.component';

@NgModule({
  declarations: [
    UsersListComponent,
    UserCardComponent,
    UserFormComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild([
        {path: '', component: UsersListComponent},
        {path: 'create', component: UserFormComponent},
        {path: 'view/:id', component: UserCardComponent},
        {path: ':id/edit', component: UserFormComponent}
    ]),
    ReactiveFormsModule,
    ActionsModule,
    ...TuiTable,
    TuiPagination,
    TuiButton,
    TuiLoader,
    ...WaIntersectionObserver,
    TuiScrollbar, TuiScrollable,
    TuiAvatar,
    TuiCardLarge,
    TuiBadge,
    TuiIcon,
    ...TuiInput, TuiPassword,
    ...TuiSelect, TuiChevron,
    ...TuiDataList,
    ...TuiDataListWrapper,
    TuiLabel,
    ...TuiError, TuiInitialsPipe, TuiCheckbox, ...TuiDropdown]
})
export class UsersModule { }
