import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { LogoutComponent } from './logout.component';


@NgModule({
  declarations: [LogoutComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {path: '', component: LogoutComponent}
    ])
  ]
})
export class LogoutModule {
}
