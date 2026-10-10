import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule } from '@angular/forms';
import { UsersListComponent } from './users-list.component';
import { ActionsModule } from '../../actions/actions.module';
import {TuiButtonModule, TuiDataListModule, TuiErrorModule, TuiLoaderModule, TuiScrollbarModule, TuiSvgModule} from '@taiga-ui/core';
import { TuiTableModule } from '@taiga-ui/addon-table';
import {
  TuiPaginationModule,
  TuiAvatarModule,
  TuiIslandModule,
  TuiBadgeModule,
  TuiCheckboxLabeledModule,
  TuiDataListWrapperModule,
  TuiFieldErrorPipeModule,
  TuiInputModule,
  TuiInputPasswordModule,
  TuiSelectModule
} from '@taiga-ui/kit';
import {IntersectionObserverModule} from "@ng-web-apis/intersection-observer";
import {TuiLetModule} from "@taiga-ui/cdk";
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
    TuiTableModule,
    TuiPaginationModule,
    TuiButtonModule,
    TuiLoaderModule,
    IntersectionObserverModule,
    TuiScrollbarModule,
    TuiLetModule,
    TuiAvatarModule,
    TuiIslandModule,
    TuiBadgeModule,
    TuiSvgModule,
    TuiInputModule,
    TuiInputPasswordModule,
    TuiSelectModule,
    TuiDataListModule,
    TuiDataListWrapperModule,
    TuiCheckboxLabeledModule,
    TuiErrorModule,
    TuiFieldErrorPipeModule
  ]
})
export class UsersModule { }
