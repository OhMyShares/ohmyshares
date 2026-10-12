import { HttpClient } from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Router} from '@angular/router';
import {BehaviorSubject, Observable, throwError} from 'rxjs';
import {catchError, map, take, tap} from 'rxjs/operators';
import {environment} from '../../../environments/environment';
import {ConfirmDialogData} from '../../modules/confirm-dialog/confirm-dialog.component';
import {
  AuthParams,
  ChangePasswordParams,
  ForgotParams,
  LoginResponse,
  Me,
  ResetParams,
  SignUpParams
} from '../interfaces/auth.interfaces';
import { TuiDialogService, TuiNotificationService } from '@taiga-ui/core';

export const API_TOKEN = 'API_TOKEN';
const API = {
  LOGIN: `${ environment.API_ENDPOINT }/auth`,
  SIGNUP: `${ environment.API_ENDPOINT }/signup`,
  CHANGE_PASSWORD: `${ environment.API_ENDPOINT }/password-change`,
  LOGOUT: `${ environment.API_ENDPOINT }/logout`,
  ME: `${ environment.API_ENDPOINT }/me`,
  FORGOT: `${ environment.API_ENDPOINT }/password-forgot`,
  RESET_PASSWORD: `${ environment.API_ENDPOINT }/password-reset`
};

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  public token$: BehaviorSubject<string> = new BehaviorSubject(localStorage.getItem(API_TOKEN));
  public me$: BehaviorSubject<Me> = new BehaviorSubject(null);

  constructor(
    private http: HttpClient,
    private router: Router,
    private readonly alertService: TuiNotificationService,
    private dialogService: TuiDialogService,
  ) {
  }

  logout(): void {
    this.http.post(API.LOGOUT, null)
      .subscribe(() => {
        localStorage.removeItem(API_TOKEN);
        this.token$.next(null);
        this.me$.next(null);
        this.router.navigate([ 'login' ]);
      });
  }

  signUp(signUpParams: SignUpParams): void {
    this.http.post(API.SIGNUP, signUpParams)
      .pipe(
        catchError((err) => {
          if (err.status === 400) {
            this.alertService.open($localize`:@@alert.auth.signUpError.message:Signup error`, {
              appearance: 'negative',
            }).subscribe();
          }
          return throwError(err);
        })
      )
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.auth.signUp.message:You have successfully registered. Check your email for password`, {
          appearance: 'positive',
        }).subscribe();
        this.router.navigate([ '/login' ]);
      });
  }

  changePassword(changePasswordParams: ChangePasswordParams): void {
    this.http.post(API.CHANGE_PASSWORD, changePasswordParams)
      .pipe(
        catchError((err) => {
          if (err.status === 400) {
            this.alertService.open(err.error.message, {
              appearance: 'negative',
            }).subscribe();
          }
          return throwError(err);
        })
      )
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.auth.changePassword.message:Password was successfully changed`, {
          appearance: 'positive',
        }).subscribe();
        this.router.navigate([ '/logout' ]);
      });
  }

  impersonate(token: string): void {
    localStorage.clear();
    this.token$.next(null);
    this.me$.next(null);
    localStorage.setItem(API_TOKEN, token);
    this.token$.next(token);
    this.router.navigate([ 'portfolios' ]);
  }

  login(authParams: AuthParams, sender): void {
    this.http.post<LoginResponse>(API.LOGIN, authParams)
      .pipe(
        catchError((err) => {
          sender.showLoader = false;
          if (err.status === 401) {
            this.alertService.open($localize`:@@alert.auth.login.message:Incorrect login or password`, {
              appearance: 'negative',
            }).subscribe();
          }
          return throwError(err);
        })
      )
      .subscribe((res) => {
        localStorage.setItem(API_TOKEN, res.api_token);
        this.token$.next(res.api_token);
        this.router.navigate([ 'portfolios' ]);
      });
  }

  forgot(forgotParams: ForgotParams): void {
    this.http.post(API.FORGOT, forgotParams)
      .pipe(
        catchError((err) => {
          if (err.status === 400) {
            this.alertService.open(err.error.message, {
              appearance: 'negative',
            }).subscribe();
          }
          return throwError(err);
        })
      )
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.auth.forgot.success:Instructions to reset password successfully sent`, {
          appearance: 'positive',
        }).subscribe();
        this.router.navigate([ 'login' ]);
      });
  }

  resetPassword(resetParams: ResetParams, sender): void {
    this.http.post(API.RESET_PASSWORD, resetParams)
      .pipe(
        catchError((err) => {
          sender.showLoader = false;
          if (err.status >= 400) {
            this.alertService.open(err.error.message, {
              appearance: 'negative',
            }).subscribe();
          }
          return throwError(err);
        })
      )
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.auth.resetPassword.success:Password successfully changed`, {
          appearance: 'positive',
        }).subscribe();
        this.router.navigate([ 'login' ]);
      });
  }

  me(options = {}): Observable<boolean> {
    return this.http.get(API.ME, options)
      .pipe(
        map((data: Me) => {
          this.me$.next(data);
          localStorage.setItem('LOCALE_ID', data.locale);
          return true;
        })
      );
  }

  updateMe(body = {}): Observable<Me> {
    return this.http.patch<Me>(API.ME, body)
      .pipe(
        catchError((err) => {
          if (err.status >= 400) {
            this.alertService.open(err.error.message, {
              appearance: 'negative',
            }).subscribe();
          }
          return throwError(err);
        }),
        take(1),
        tap(((me: Me) => {
          this.alertService.open($localize`:@@alert.auth.updateMe.message:Your profile has been successfully updated`, {
            appearance: 'positive',
          }).subscribe();
          this.me$.next(me);
        }))
      );
  }

  isTest(): boolean {
    return this.me$.getValue()?.is_test;
  }

  isOmsEmail(): boolean {
    return /@ohmyshares.com/i.test(this.me$.getValue()?.email);
  }
}
