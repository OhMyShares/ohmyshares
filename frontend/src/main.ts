import { enableProdMode, provideZoneChangeDetection } from '@angular/core';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';

import { AppModule } from './app/app.module';
import { applyTheme, resolveTheme } from './app/core/theme';
import { environment } from './environments/environment';

if (environment.production) {
  enableProdMode();
}

applyTheme(resolveTheme());

platformBrowserDynamic().bootstrapModule(AppModule, { applicationProviders: [provideZoneChangeDetection()], })
  .catch(err => console.error(err));
