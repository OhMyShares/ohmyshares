import { ApplicationRef, Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { applyTheme, resolveTheme } from '../theme';

@Injectable()
export class ThemingService {
  themes = [ 'dark-theme', 'light-theme' ];
  theme = new BehaviorSubject(resolveTheme());
  private readonly savedTheme: string;

  constructor(
    private ref: ApplicationRef
  ) {
    this.savedTheme = resolveTheme();
    this.theme.next(this.savedTheme);
    this.ref.tick();
    applyTheme(this.savedTheme);
  }

  public setTheme(theme) {
    applyTheme(theme);
    document.body.classList.add('animate-colors-transition');
    this.theme.next(theme);
  }

  get isDark(): boolean {
    return this.theme.value === 'dark-theme';
  }
}
