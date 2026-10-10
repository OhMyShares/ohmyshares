import { Injectable } from '@angular/core';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import {Subject} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SpinnerService extends Subject<boolean> {

  private counter = 0;
  public isLoading$ = this.asObservable();

  public start() {
    this.counter += 1;
    this.next(true);
  }

  public stop() {
    this.counter -= 1;
    if (this.counter < 1) {
      this.counter = 0;
      this.next(false);
    }
  }
}
