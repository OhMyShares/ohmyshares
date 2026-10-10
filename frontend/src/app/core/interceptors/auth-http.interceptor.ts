import {HttpErrorResponse, HttpEvent, HttpHandler, HttpInterceptor, HttpRequest} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Router} from '@angular/router';
import {Observable, throwError} from 'rxjs';
import {catchError, finalize} from 'rxjs/operators';
import {AuthService} from '../services/auth.service';
import {CommonService} from '../services/common.service';
import {SpinnerService} from '../services/spinner.service';
import {TuiAlertService, TuiNotification} from '@taiga-ui/core';

@Injectable()
export class AuthHttpInterceptor implements HttpInterceptor {
  constructor(
    private authService: AuthService,
    private alertService: TuiAlertService,
    private commonService: CommonService,
    private spinnerService: SpinnerService,
    private router: Router
  ) {
  }

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = this.authService.token$.getValue();
    if (!req.headers.has('noSpinner')) {
      this.spinnerService.start();
    }
    const authReq = !token ? req : req.clone({
      headers: req.headers.set('Authorization', `Bearer ${ token }`).delete('noSpinner')
    });
    return next.handle(authReq)
      .pipe(
        finalize(() => this.spinnerService.stop()),
        catchError((err: HttpEvent<any>) => {
          if (err instanceof HttpErrorResponse) {
            if (!err.status) {
              // this.alertService.message$.next({
              //   message: $localize`:@@alert.connectionLost.message:Connection error`,
              //   type: AlertTypes.ERROR,
              //   action: this.router.navigate.bind(this.router, []),
              //   actionTitle: $localize`:@@alert.connectionLost.action:Retry`
              // });
              this.alertService.open($localize`:@@alert.connectionLost.message:Connection error`, {
                status: TuiNotification.Error,
                label: $localize`:@@alert.connectionLost.action:Retry`,
              });
            } else if (err.status === 401) {
              this.authService.logout();
            } else if (err.status === 400) {
              this.commonService.validationError$.next(err.error);
            } else {
              this.alertService.open(err.error.message, {
                status: TuiNotification.Error,
              }).subscribe();
            }
          }
          return throwError(err);
        })
      );
  }
}
