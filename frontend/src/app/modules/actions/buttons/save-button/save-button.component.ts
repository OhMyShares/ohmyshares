import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
    selector: 'app-save-button',
    templateUrl: './save-button.component.html',
    styleUrls: ['./save-button.component.scss'],
    standalone: false
})
export class SaveButtonComponent {
  @Input() isDisabled = true;
  @Input() showLoader = false;

  @Output() save: EventEmitter<void> = new EventEmitter();

  constructor() {
  }

}
