import { TuiRoot, TuiDialog, provideTaiga, TuiNotification, tuiTextfieldOptionsProvider } from '@taiga-ui/core';
import { registerLocaleData } from '@angular/common';
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import localeRu from '@angular/common/locales/ru';
import { LOCALE_ID, NgModule, signal } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { NgxUiLoaderModule } from 'ngx-ui-loader';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { LOCALE_STRING } from './core/constants';
import { CoreModule } from './core/core.module';
import { AuthHttpInterceptor } from './core/interceptors/auth-http.interceptor';
import { ThemingService } from './core/services/theming.service';
import { HeaderModule } from './modules/header/header.module';

registerLocaleData(localeRu);

@NgModule({
  declarations: [
    AppComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    CoreModule,
    BrowserAnimationsModule,
    NgxUiLoaderModule.forRoot({
      fgsType: 'pulse',
      fgsColor: '#278E74',
      overlayColor: 'rgba(179,179,179,0.7)',
      pbColor: '#278E74',
      fastFadeOut: true
    }),
    HeaderModule,
    TuiRoot,
    TuiDialog,
    ...TuiNotification
  ],
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthHttpInterceptor,
      multi: true
    },
    {
      provide: LOCALE_ID,
      useValue: LOCALE_STRING
    },
    ThemingService,
    provideTaiga(),
    tuiTextfieldOptionsProvider({cleaner: signal(false)})
  ],
  bootstrap: [AppComponent]
})
export class AppModule {
}


