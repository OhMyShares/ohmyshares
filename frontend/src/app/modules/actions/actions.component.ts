import { AfterViewInit, Component, HostBinding, Input } from '@angular/core';
import { CommonService } from '../../core/services/common.service';

@Component({
    selector: 'app-actions',
    templateUrl: './actions.component.html',
    styleUrls: ['./actions.component.scss'],
    standalone: false
})
export class ActionsComponent implements AfterViewInit{
  // @HostBinding('class') classes = '';

  @Input() isFormActions = false;

  constructor(
    public commonService: CommonService
  ) {
  }

  ngAfterViewInit(): void {
    // if (this.commonService.isMobile) {
    //   setTimeout(() => this.classes += ' backdrop-blur mat-elevation-z6 fixed-bottom');
    // } else {
    //   if (!this.isFormActions) {
    //     setTimeout(() => this.classes += ' rounded');
    //   }
    // }
  }

}
