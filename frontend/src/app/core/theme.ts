export const THEME_ID_KEY = 'THEME_ID';
export const TUI_DARK_MODE_KEY = 'tuiDark';
export const DARK_THEME = 'dark-theme';
export const LIGHT_THEME = 'light-theme';

export function resolveTheme(): string {
  const saved = localStorage.getItem(THEME_ID_KEY);

  if (saved === DARK_THEME || saved === LIGHT_THEME) {
    return saved;
  }

  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? DARK_THEME : LIGHT_THEME;
}

export function applyTheme(theme: string): void {
  const isDark = theme === DARK_THEME;

  localStorage.setItem(THEME_ID_KEY, theme);
  // Keep Taiga UI dark mode in sync with the application theme
  localStorage.setItem(TUI_DARK_MODE_KEY, `${isDark}`);
  document.body.className = theme;

  if (isDark) {
    document.body.setAttribute('tuiTheme', 'dark');
  } else {
    document.body.removeAttribute('tuiTheme');
  }
}
