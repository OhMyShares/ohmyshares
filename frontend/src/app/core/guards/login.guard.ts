import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, Router, RouterStateSnapshot, UrlTree } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class LoginGuard  {

  constructor(
    private authService: AuthService,
    private router: Router
  ) {
  }

  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean | UrlTree> | Promise<boolean | UrlTree> | boolean | UrlTree {
    if (!this.authService.me$.getValue()) {
      if (!this.authService.token$.getValue()) {
        return true;
      }
      return this.authService.me$
        .pipe(
          map(() => {
            this.router.navigate([ '/' ]);
            return false;
          })
        );
    }
    this.router.navigate([ '/' ]);
    return false;
  }

}
