import { TuiIcon } from "@taiga-ui/core";
import { TuiTabs } from "@taiga-ui/kit";
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { HeaderComponent } from './header.component';

@NgModule({
  declarations: [HeaderComponent],
  imports: [
    CommonModule,
    RouterModule,
    ...TuiTabs,
    TuiIcon
  ],
  exports: [HeaderComponent]
})
export class HeaderModule { }
