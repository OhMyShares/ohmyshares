import {Component, EventEmitter, Inject, Injector, Input, Output} from '@angular/core';
// import { MatDialog } from '@angular/material/dialog';
import { filter, take } from 'rxjs/operators';
import { ConfirmDialogData, ConfirmDialogComponent } from '../../../confirm-dialog/confirm-dialog.component';
import {TuiDialogService} from '@taiga-ui/core';
import {PolymorpheusComponent} from '@tinkoff/ng-polymorpheus';

@Component({
  selector: 'app-remove-button',
  templateUrl: './remove-button.component.html',
  styleUrls: [ './remove-button.component.scss' ]
})
export class RemoveButtonComponent {
  @Input() isDisabled = true;
  @Input() isMenuButton = false;
  @Input() confirmTitle = '';
  @Input() showLoader = false;

  @Output() delete: EventEmitter<void> = new EventEmitter();

  constructor(
    @Inject(TuiDialogService) private readonly dialogService: TuiDialogService,
    @Inject(Injector) private readonly injector: Injector,
  ) {
  }

  remove() {
    const data: ConfirmDialogData = {
      // content: 'Any content',
      button: {
        close: $localize`:@@action.confirmDialog.no:No`,
        action: $localize`:@@action.confirmDialog.yes:Yes`
      }
    };
    const dialog = this.dialogService.open<ConfirmDialogData>(
      new PolymorpheusComponent(ConfirmDialogComponent, this.injector),
      {
        dismissible: true,
        label: this.confirmTitle,
        data,
      },
    );
    dialog.subscribe({
      next: (data) => {
        if (data !== null) {
          this.delete.emit();
          // console.log(`Dialog emitted data = ${data}`);
        }
      },
      complete: () => {
        console.log(`Dialog closed`);
      },
    });
  }

}
