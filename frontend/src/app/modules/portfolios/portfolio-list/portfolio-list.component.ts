import {DatePipe} from '@angular/common';
import {Component, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {CURRENT_PORTFOLIO_KEY, PORTFOLIOS_LIST_KEY} from '../../../core/constants';
import {Portfolio} from '../../../core/interfaces/portfolios.interfaces';
import {AuthService} from '../../../core/services/auth.service';
import {CommonService} from '../../../core/services/common.service';
import {Common, CurrencyReference, REFERENCE, ReferencesService} from '../../../core/services/references.service';
import {PortfoliosService} from '../../../core/services/portfolios.service';

@Component({
  selector: 'app-portfolio-list',
  templateUrl: './portfolio-list.component.html',
  styleUrls: [ './portfolio-list.component.scss' ],
  providers: [ DatePipe ]
})
export class PortfolioListComponent implements OnInit {
  // @HostBinding('class') classes = 'row justify-content-center align-items-center';

  currentProfile = localStorage.getItem(CURRENT_PORTFOLIO_KEY);
  columns = ['name', 'currency_code', 'opened_at', 'account_type'];
  accountTypes: Common[];
  currencies: CurrencyReference[];
  portfolios: Portfolio[];
  skeletonVisible = true;

  constructor(
    public commonService: CommonService,
    public authService: AuthService,
    public portfoliosService: PortfoliosService,
    public referenceService: ReferencesService,
    private route: ActivatedRoute,
    private datePipe: DatePipe
  ) {
  }

  ngOnInit() {
    this.skeletonVisible = true;
    this.portfoliosService.getList().subscribe((portfolios) => {
      this.portfolios = portfolios;
      if (!localStorage.getItem(CURRENT_PORTFOLIO_KEY)) {
        localStorage.setItem(CURRENT_PORTFOLIO_KEY, `${ portfolios[0]?.uid }`);
      }
      localStorage.setItem(PORTFOLIOS_LIST_KEY, JSON.stringify(portfolios));
      this.skeletonVisible = false;
    });
    this.referenceService.getReference<CurrencyReference[]>(REFERENCE.CURRENCY).subscribe((currencies) => {
      this.currencies = currencies;
    });
    this.referenceService.getReference<Common[]>(REFERENCE.ACCOUNT_TYPE).subscribe((accountTypes) => {
      this.accountTypes = accountTypes;
    });
  }

  getCurrency(portfolio: Portfolio): CurrencyReference {
    return this.currencies?.find((item) => item.code === portfolio.currency_code);
  }

  getAccountTypeName(portfolio: Portfolio): string {
    return this.accountTypes?.find((item) => item.id === portfolio.account_type)?.name;
  }

}
