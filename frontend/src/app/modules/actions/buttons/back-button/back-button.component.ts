import {Component, Input} from '@angular/core';

@Component({
    selector: 'app-back-button',
    templateUrl: './back-button.component.html',
    styleUrls: ['./back-button.component.scss'],
    standalone: false
})
export class BackButtonComponent {

  @Input() appearance = 'outline'; // secondary
  @Input() size = 'm'; // s, m, l
  @Input() icon = '@tui.chevron-left';

  constructor() {
  }

  back() {
    history.back();
  }
}
