import { Component, OnInit } from '@angular/core';
import {LOCALE_STRING, LOCALES} from '../../core/constants';
import { Me } from '../../core/interfaces/auth.interfaces';
import { AuthService } from '../../core/services/auth.service';
import { CommonService } from '../../core/services/common.service';
import { ThemingService } from '../../core/services/theming.service';
import {UntypedFormBuilder, UntypedFormControl, UntypedFormGroup, Validators} from '@angular/forms';
import {catchError} from 'rxjs/operators';
import {throwError} from 'rxjs';

@Component({
    selector: 'app-profile',
    templateUrl: './profile.component.html',
    styleUrls: ['./profile.component.scss'],
    standalone: false
})
export class ProfileComponent implements OnInit {

  public profileForm = new UntypedFormGroup({
    name: new UntypedFormControl('', [
      Validators.required, Validators.minLength(1), Validators.maxLength(100)
    ]),
    locale: new UntypedFormControl('', [
      Validators.required
    ]),
    is_ignore_funds_sufficiency: new UntypedFormControl(false, [
      Validators.required
    ]),
  });

  languages = LOCALES;
  locale = LOCALE_STRING;
  theme = 'light-theme';
  themes: string[];
  me: Me;
  showLoaderOnSave = false;

  constructor(
    public commonService: CommonService,
    public authService: AuthService,
    private theming: ThemingService,
    private formBuilder: UntypedFormBuilder
  ) {
  }

  ngOnInit() {
    this.themes = this.theming.themes;
    this.theme = localStorage.getItem('THEME_ID');
    this.me = this.authService.me$.value;
    this.createForm(this.me);
  }

  createForm(me: Me) {
    this.profileForm = this.formBuilder.group(me);
  }

  save() {
    if (this.profileForm.invalid) {
      return;
    }
    this.showLoaderOnSave = true;
    this.authService.updateMe({
      name: this.profileForm.value.name,
      locale: this.profileForm.value.locale,
      is_ignore_funds_sufficiency: this.profileForm.value.is_ignore_funds_sufficiency,
    }).pipe(
      catchError((err) => {
        this.showLoaderOnSave = false;
        return throwError(err);
      })
    ).subscribe(() => {
      this.showLoaderOnSave = false;
      localStorage.setItem('LOCALE_ID', this.profileForm.value.locale);
      if (this.commonService.OMSApp) {
        this.commonService.OMSApp.reload();
      } else {
        location.reload();
      }
    });
  }

  get darkThemeEnabled() {
    return this.theming.isDark;
  }

  set darkThemeEnabled(value) {
    this.theming.setTheme(value ? 'dark-theme' : 'light-theme');
  }

}
