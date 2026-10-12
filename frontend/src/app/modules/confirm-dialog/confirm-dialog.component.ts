import {Component, Inject, TemplateRef} from '@angular/core';
import {TuiDialogContext, TuiDialogService} from '@taiga-ui/core';
// import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import {POLYMORPHEUS_CONTEXT} from '@taiga-ui/polymorpheus';

export interface ConfirmDialogData {
  title?: string;
  content?: string;
  button?: {
    close?: string;
    action?: string;
  };
}

@Component({
    selector: 'app-property-delete-confirm-dialog',
    templateUrl: './confirm-dialog.component.html',
    styleUrls: ['./confirm-dialog.component.scss'],
    standalone: false
})
export class ConfirmDialogComponent {

  constructor(
    @Inject(TuiDialogService) private readonly dialogService: TuiDialogService,
    @Inject(POLYMORPHEUS_CONTEXT)
    private readonly context: TuiDialogContext<ConfirmDialogData>,
  ) {}

  get data(): ConfirmDialogData {
    return this.context.data;
  }

  showDialog(content: TemplateRef<TuiDialogContext<void>>): void {
    this.dialogService.open(content, {dismissible: true}).subscribe();
  }

  submit(): void {
    if (this.context.data !== null) {
      this.context.completeWith(this.context.data);
    }
  }

  close(): void {
    this.context.completeWith(null);
  }
}
