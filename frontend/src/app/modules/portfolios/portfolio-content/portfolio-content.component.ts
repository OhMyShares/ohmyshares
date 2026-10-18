import { tuiScrollbarOptionsProvider } from '@taiga-ui/core';
import { TuiNotificationService } from "@taiga-ui/core";
import {Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import * as PullToRefresh from 'pulltorefreshjs';
import {CURRENT_PORTFOLIO_KEY, PORTFOLIOS_LIST_KEY} from '../../../core/constants';
import {Portfolio, PortfolioContent, PortfolioContentSummary, Position} from '../../../core/interfaces/portfolios.interfaces';
import {CommonService} from '../../../core/services/common.service';
import {PortfoliosService} from '../../../core/services/portfolios.service';
import {Common, REFERENCE, ReferencesService} from '../../../core/services/references.service';
import {AuthService} from '../../../core/services/auth.service';
import {UntypedFormControl} from '@angular/forms';
import {catchError, debounceTime} from 'rxjs/operators';
import {of} from 'rxjs';
import _ from 'lodash';

export class Group {
  level = 0;
  parent: Group;
  expanded = true;
  totalCounts = 0;

  get visible(): boolean {
    return !this.parent || (this.parent.visible && this.parent.expanded);
  }
}

const MONEY_TICKER = '$$';

@Component({
    selector: 'app-portfolio-content',
    templateUrl: './portfolio-content.component.html',
    styleUrls: ['./portfolio-content.component.scss'],
    providers: [tuiScrollbarOptionsProvider({mode: 'hidden'})],
    standalone: false
})
export class PortfolioContentComponent implements OnInit, OnDestroy {
  // @HostBinding('class') classes = '';

  public displayedColumns = [
    'ticker', 'current_price', 'average_price', 'current_cost', 'part_percent',
    'current_gain', 'current_gain_percent'
  ];
  public summary: PortfolioContentSummary;
  public portfolio: Portfolio;
  public portfolios: Portfolio[];
  public assetTypes: Common[];
  public pullToRefresh: any;
  public content: Position[];
  public searchField: UntypedFormControl;
  public skeletonVisible = true;
  public groupedData = [];

  constructor(
    private referencesService: ReferencesService,
    private portfoliosService: PortfoliosService,
    private route: ActivatedRoute,
    private router: Router,
    public commonService: CommonService,
    public authService: AuthService,
    public alertService: TuiNotificationService,
  ) {
  }

  ngOnInit() {
    const that = this;
    this.portfoliosService.getContentByUid(this.route.snapshot.params.uid)
      .pipe(catchError(() => {
        this.router.navigate(['/portfolios']);
        return of({} as PortfolioContent);
      }))
      .subscribe((portfolioContent) => {
        this.content = portfolioContent.content;
        this.summary = portfolioContent.summary;
        this.portfolio = portfolioContent.portfolio;
        this.refreshIfPortfolioLocked();
        this.groupData(this.content);
        localStorage.setItem(CURRENT_PORTFOLIO_KEY, `${ this.portfolio.uid }`);
        this.skeletonVisible = false;
      });

    this.referencesService.getReference<Common[]>(REFERENCE.ASSET).subscribe((assetTypes) => {
      this.assetTypes = assetTypes;
    });

    this.portfolios = JSON.parse(localStorage.getItem(PORTFOLIOS_LIST_KEY));

    this.pullToRefresh = PullToRefresh.init({
      mainElement: 'app-portfolio-content',
      iconRefreshing: '&#9203;',
      instructionsPullToRefresh: $localize`:@@pullToRefresh.start:Pull down to refresh`,
      instructionsReleaseToRefresh: $localize`:@@pullToRefresh.end:Release to refresh`,
      instructionsRefreshing: $localize`:@@pullToRefresh.refreshing:Refreshing...`,
      onRefresh() {
        that.commonService.reloadRoute();
      }
    });

    this.searchField = new UntypedFormControl();
    this.searchField.setValue(this.route.snapshot.queryParamMap.get('search'));
    this.searchField.valueChanges.pipe(debounceTime(300))
      .subscribe(value => {
        this.searchItems(value);
      });
  }

  refreshIfPortfolioLocked() {
    // Set timer if import locked
    if (this.portfolio.is_locked) {
      const intervalId = setInterval(() => {
        this.portfoliosService.getOneByUid(this.portfolio.uid).subscribe((p) => {
          if (!p.is_locked) {
            // clearInterval(intervalId);
            // this.portfolio = p;
            window.location.reload();
          }
        });
      }, 10000);
    }
  }

  groupData(data) {
    this.groupedData = _.groupBy(data, 'asset');
  }

  ngOnDestroy() {
    this.pullToRefresh.destroy();
  }

  getAssetType(id?: number): Common {
    return (id
        ? this.assetTypes.find((item) => item.id === id)
        : this.assetTypes.find((item) => item.default)
    ) || {} as Common;
  }

  get openPositionsCount(): number {
    return (this.content || []).filter((item) => item.ticker !== MONEY_TICKER && item.count !== 0).length;
  }

  get cashTotal(): number {
    return (this.content || [])
      .filter((item) => item.ticker === MONEY_TICKER)
      .reduce((sum, item) => sum + item.current_cost, 0);
  }

  // isEmpty() {
  //   return !this.dataSource?.data?.length;
  // }

  searchItems(value) {
    let content = this.content;
    if (value) {
      content = content.filter(item => {
        return item.ticker.toLowerCase().includes(value.toLowerCase())
          || item.title?.toLowerCase().includes(value.toLowerCase());
      });
    }
    this.groupData(content);
  }

  clone() {
    this.portfoliosService.clone(this.portfolio.uid.toString())
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.portfolio.clone.message:Portfolio was successfully cloned`, {
          appearance: 'positive'
        }).subscribe();
        this.router.navigate(['/portfolios']);
      });
  }

  changeCurrency(currencyCode) {
    this.portfolio.currency_code = currencyCode;
    this.portfoliosService.update(this.portfolio)
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.portfolio.update.message:The portfolio has been successfully updated`, {
          appearance: 'positive'
        }).subscribe();
        this.commonService.reloadRoute();
      });
  }

}
