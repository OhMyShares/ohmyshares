import { TuiNotificationService } from "@taiga-ui/core";
import {OverlayContainer} from '@angular/cdk/overlay';
import {Component, ElementRef, HostBinding} from '@angular/core';
import {NavigationCancel, NavigationEnd, NavigationError, NavigationStart, Router} from '@angular/router';
import {AuthService} from './core/services/auth.service';
import {CommonService} from './core/services/common.service';
import {SpinnerService} from './core/services/spinner.service';
import {ThemingService} from './core/services/theming.service';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss'],
    standalone: false
})
export class AppComponent {

  @HostBinding('class') public cssClass: string;

  constructor(
    elementRef: ElementRef,
    public authService: AuthService,
    public commonService: CommonService,
    private spinnerService: SpinnerService,
    private alertService: TuiNotificationService,
    public themingService: ThemingService,
    private overlayContainer: OverlayContainer,
    private router: Router,
  ) {
    this.router.events.subscribe((event) => {
      switch (event.constructor) {
        case NavigationStart:
          this.spinnerService.start();
          break;
        case NavigationError:
          if ((event as NavigationError).error?.name === 'ChunkLoadError') {
            this.alertService.open($localize`:@@alert.connectionLost.message:Connection error`, {
              appearance: 'negative'
            }).subscribe();
          }
        // tslint:disable-next-line:no-switch-case-fall-through
        case NavigationCancel:
        case NavigationEnd:
          this.spinnerService.stop();
          break;
      }
    });
  }

}
