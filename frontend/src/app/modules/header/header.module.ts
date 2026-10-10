import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { HeaderComponent } from './header.component';
import {TuiTabsModule} from '@taiga-ui/kit';
import {TuiSvgModule} from "@taiga-ui/core";



@NgModule({
  declarations: [HeaderComponent],
  imports: [
    CommonModule,
    RouterModule,
    TuiTabsModule,
    TuiSvgModule
  ],
  exports: [HeaderComponent]
})
export class HeaderModule { }
