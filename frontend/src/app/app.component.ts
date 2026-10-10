import {OverlayContainer} from '@angular/cdk/overlay';
import {Component, ElementRef, HostBinding} from '@angular/core';
import {NavigationCancel, NavigationEnd, NavigationError, NavigationStart, Router, RouterEvent} from '@angular/router';
import {AuthService} from './core/services/auth.service';
import {CommonService} from './core/services/common.service';
import {SpinnerService} from './core/services/spinner.service';
import {ThemingService} from './core/services/theming.service';
import {TuiAlertService, TuiNotification} from '@taiga-ui/core';

export interface MyRouterEvent extends RouterEvent {
  error?: Error;
}

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: [ './app.component.scss' ]
})
export class AppComponent {

  @HostBinding('class') public cssClass: string;

  constructor(
    elementRef: ElementRef,
    public authService: AuthService,
    public commonService: CommonService,
    private spinnerService: SpinnerService,
    private alertService: TuiAlertService,
    public themingService: ThemingService,
    private overlayContainer: OverlayContainer,
    private router: Router,
  ) {
    this.router.events.subscribe((event: MyRouterEvent) => {
      switch (event.constructor) {
        case NavigationStart:
          this.spinnerService.start();
          break;
        case NavigationError:
          if (event?.error?.name === 'ChunkLoadError') {
            this.alertService.open($localize`:@@alert.connectionLost.message:Connection error`, {
              status: TuiNotification.Error
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
