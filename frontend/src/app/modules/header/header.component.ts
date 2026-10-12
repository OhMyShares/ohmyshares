import { Component, HostBinding } from '@angular/core';
import { CURRENT_PORTFOLIO_KEY } from '../../core/constants';
import {AuthService} from "../../core/services/auth.service";

@Component({
    selector: 'app-header',
    templateUrl: './header.component.html',
    styleUrls: ['./header.component.scss'],
    standalone: false
})
export class HeaderComponent {
  @HostBinding('class') classes = 'backdrop-blur shadow-sm';

  constructor(
    private authService: AuthService,
  ) {
  }

  public items = [
    {
      label: $localize`:@@nav.portfolio:Portfolio`,
      icon: '@tui.briefcase',
      route: () => `/portfolios/${localStorage.getItem(CURRENT_PORTFOLIO_KEY)}/content`,
      visible: true,
    },
    {
      label: $localize`:@@nav.trades:Trades`,
      icon: '@tui.inbox',
      route: () => '/trades',
      visible: true,
    },
    {
      label: $localize`:@@nav.companies:Companies`,
      icon: '@tui.table',
      route: () => '/companies',
      visible: true,
    },
    {
      label: $localize`:@@nav.profile:Profile`,
      icon: '@tui.sliders-vertical',
      route: () => '/profile',
      visible: true,
    },
    {
      label: $localize`:@@nav.users:Users`,
      icon: '@tui.users',
      route: () => '/users',
      visible: this.authService.me$.value?.role === 'Admin',
    },
    {
      label: $localize`:@@nav.plugins:Plugins`,
      icon: '@tui.package',
      route: () => '/plugins',
      visible: this.authService.me$.value?.role === 'Admin',
    },
    {
      label: $localize`:@@nav.currencyRates:Currency rates`,
      icon: '@tui.dollar-sign',
      route: () => '/currency-rates',
      visible: this.authService.me$.value?.role === 'Admin',
    },
  ];
}
