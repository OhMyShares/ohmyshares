import { ApplicationRef, Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import {TuiBrightness} from '@taiga-ui/core';

@Injectable()
export class ThemingService {
  themes = [ 'dark-theme', 'light-theme' ];
  theme = new BehaviorSubject('light-theme');
  themeTuiMode: TuiBrightness = 'onLight';
  private readonly savedTheme: string;

  constructor(
    private ref: ApplicationRef
  ) {

    // initially trigger dark mode if preference is set to dark mode on system
    const darkModeOn =
      window.matchMedia &&
      window.matchMedia('(prefers-color-scheme: dark)').matches;

    this.savedTheme = localStorage.getItem('THEME_ID');
    if (!this.savedTheme) {
      this.savedTheme = 'light-theme';
      if (darkModeOn) {
        this.savedTheme = 'dark-theme';
      }
      localStorage.setItem('THEME_ID', this.savedTheme);
    }

    if (this.themes.includes(this.savedTheme)) {
      this.theme.next(this.savedTheme);
      this.ref.tick();
      document.body.className = this.savedTheme;
      this.themeTuiMode = this.savedTheme === 'dark-theme' ? 'onDark' : null;
      return;
    }

    // watch for changes of the preference
    window.matchMedia('(prefers-color-scheme: dark)').addListener(e => {
      const turnOn = e.matches;
      this.theme.next(turnOn ? 'dark-theme' : 'light-theme');

      // trigger refresh of UI
      this.ref.tick();
    });
  }

  public setTheme(theme) {
    document.body.className = theme;
    document.body.classList.add('animate-colors-transition');
    localStorage.setItem('THEME_ID', theme);
    this.themeTuiMode = theme === 'dark-theme' ? 'onDark' : null;
    this.theme.next(theme);
  }

  get isDark(): boolean {
    return this.theme.value === 'dark-theme';
  }
}
