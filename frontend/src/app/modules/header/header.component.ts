import { Component, HostBinding } from '@angular/core';
import { CURRENT_PORTFOLIO_KEY } from '../../core/constants';
import {AuthService} from "../../core/services/auth.service";

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss']
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
      icon: 'tuiIconBriefcase',
      route: () => `/portfolios/${localStorage.getItem(CURRENT_PORTFOLIO_KEY)}/content`,
      visible: true,
    },
    {
      label: $localize`:@@nav.trades:Trades`,
      icon: 'tuiIconInbox',
      route: () => '/trades',
      visible: true,
    },
    {
      label: $localize`:@@nav.companies:Companies`,
      icon: 'tuiIconTable',
      route: () => '/companies',
      visible: true,
    },
    {
      label: $localize`:@@nav.profile:Profile`,
      icon: 'tuiIconSliders',
      route: () => '/profile',
      visible: true,
    },
    {
      label: $localize`:@@nav.users:Users`,
      icon: 'tuiIconUsers',
      route: () => '/users',
      visible: this.authService.me$.value?.role === 'Admin',
    },
    {
      label: $localize`:@@nav.plugins:Plugins`,
      icon: 'tuiIconPackage',
      route: () => '/plugins',
      visible: this.authService.me$.value?.role === 'Admin',
    },
    {
      label: $localize`:@@nav.currencyRates:Currency rates`,
      icon: 'tuiIconDollarSign',
      route: () => '/currency-rates',
      visible: this.authService.me$.value?.role === 'Admin',
    },
  ];
}
