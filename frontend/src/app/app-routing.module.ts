import {NgModule} from '@angular/core';
import {PreloadAllModules, RouterModule, Routes} from '@angular/router';
import {AuthGuard} from './core/guards/auth.guard';
import {LoginGuard} from './core/guards/login.guard';


const routes: Routes = [
  {
    path: 'companies',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/company/company-list/company-list.module')
      .then(({CompanyListModule}) => CompanyListModule)
  },
  {
    path: 'companies/:ticker',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/company/company-card/company-card.module')
      .then(({CompanyCardModule}) => CompanyCardModule)
  },
  {
    path: 'portfolios',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/portfolios/portfolio-list/portfolio-list.module')
      .then(({PortfolioListModule}) => PortfolioListModule)
  },
  {
    path: 'portfolios/:uid',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/portfolios/portfolio-card/portfolio-card.module')
      .then(({PortfolioCardModule}) => PortfolioCardModule)
  },
  {
    path: 'portfolios/:uid/content',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/portfolios/portfolio-content/portfolio-content.module')
      .then(({PortfolioContentModule}) => PortfolioContentModule),
  },
  {
    path: 'portfolios/:uid/analytics',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/portfolios/portfolio-analytics/portfolio-analytics.module')
      .then(({PortfolioAnalyticsModule}) => PortfolioAnalyticsModule),
  },
  {
    path: 'portfolios/:uid/import',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/portfolios/portfolio-import/portfolio-import.module')
      .then(({PortfolioImportModule}) => PortfolioImportModule)
  },
  {
    path: 'custom-assets',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/custom-assets/custom-assets-list/custom-assets-list.module')
      .then(({CustomAssetsListModule}) => CustomAssetsListModule)
  },
  {
    path: 'custom-assets/:id',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/custom-assets/custom-asset-view/custom-asset-view.module')
      .then(({CustomAssetViewModule}) => CustomAssetViewModule)
  },
  {
    path: 'trades',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/trades/trade-list/trade-list.module')
      .then(({TradeListModule}) => TradeListModule)
  },
  {
    path: 'trades/:id',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/trades/trade-card/trade-card.module')
      .then(({TradeCardModule}) => TradeCardModule)
  },
  {
    path: 'login',
    canActivate: [LoginGuard],
    loadChildren: () => import('./modules/login/login.module')
      .then(({LoginModule}) => LoginModule)
  },
  {
    path: 'signup',
    canActivate: [LoginGuard],
    loadChildren: () => import('./modules/signup/signup.module')
      .then(({SignupModule}) => SignupModule)
  },
  {
    path: 'forgot',
    canActivate: [LoginGuard],
    loadChildren: () => import('./modules/forgot/forgot.module')
      .then(({ForgotModule}) => ForgotModule)
  },
  {
    path: 'reset-password',
    canActivate: [LoginGuard],
    loadChildren: () => import('./modules/reset-password/reset-password.module')
      .then(({ResetPasswordModule}) => ResetPasswordModule)
  },
  {
    path: 'change-password',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/change-password/change-password.module')
      .then(({ChangePasswordModule}) => ChangePasswordModule)
  },
  {
    path: 'logout',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/logout/logout.module')
      .then(({LogoutModule}) => LogoutModule)
  },
  {
    path: 'profile',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/profile/profile.module')
      .then(({ProfileModule}) => ProfileModule)
  },
  {
    path: 'users',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/users/users-list/users-list.module')
      .then(({UsersModule}) => UsersModule)
  },
  {
    path: 'plugins',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/plugins/plugins.module')
      .then(({PluginsModule}) => PluginsModule)
  },
  {
    path: 'currency-rates',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/currency-rates/currency-rates.module')
      .then(({CurrencyRatesModule}) => CurrencyRatesModule)
  },
  {
    path: '**',
    redirectTo: '/portfolios',
    pathMatch: 'full',
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {
    preloadingStrategy: PreloadAllModules,
    // enableTracing: isDevMode(),
    onSameUrlNavigation: 'reload'
})],
  exports: [RouterModule]
})
export class AppRoutingModule {
}
