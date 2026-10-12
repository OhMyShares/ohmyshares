import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
    selector: 'app-form-actions',
    templateUrl: './form-actions.component.html',
    styleUrls: ['./form-actions.component.scss'],
    standalone: false
})
export class FormActionsComponent {
  @Input() isSaveDisabled = true;
  @Input() isSaveShowLoader = false;
  @Input() isDeleteDisabled = true;
  @Input() isDeleteShowLoader = false;
  @Input() confirmTitle = '';

  @Output() save: EventEmitter<void> = new EventEmitter();
  @Output() delete: EventEmitter<void> = new EventEmitter();

  constructor() {
  }


}
