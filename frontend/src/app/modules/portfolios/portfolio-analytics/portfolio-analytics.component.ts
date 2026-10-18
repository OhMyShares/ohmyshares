import {Component, OnInit} from '@angular/core';
import {
  AnalyticsBreakdownItem,
  AnalyticsEarningsItem,
  AnalyticsIncomeByYear,
  Portfolio,
  PortfolioAnalytics,
  PortfolioAnalyticsData,
  Position
} from '../../../core/interfaces/portfolios.interfaces';
import { CommonService } from '../../../core/services/common.service';
import {ANALYTICS_CURRENCY_KEY, CURRENT_PORTFOLIO_KEY, PORTFOLIOS_LIST_KEY} from '../../../core/constants';
import {ActivatedRoute} from '@angular/router';
import {Common, CurrencyReference, REFERENCE, ReferencesService} from '../../../core/services/references.service';
import {PortfoliosService} from '../../../core/services/portfolios.service';
import {catchError} from 'rxjs/operators';
import {of} from 'rxjs';

export interface RingChart {
  labels: string[];
  values: number[];
  percents: number[];
  total: number;
  activeIndex: number;
}

export interface IncomeChart {
  labelsX: string[];
  labelsY: string[];
  values: number[][];
  max: number;
}

const MAX_RING_SEGMENTS = 8;
const TOP_MOVERS_COUNT = 5;
const MONEY_TICKER = '$$';

@Component({
    selector: 'app-portfolio-analytics',
    templateUrl: './portfolio-analytics.component.html',
    styleUrls: ['./portfolio-analytics.component.scss'],
    standalone: false
})
export class PortfolioAnalyticsComponent implements OnInit {
  // @HostBinding('class') classes = 'row justify-content-center align-items-center';

  public analytics: PortfolioAnalyticsData;
  public portfolio: Portfolio;
  public portfolios: Portfolio[];
  public assetTypes: Common[];
  public content: Position[];

  public currencies: CurrencyReference[] = [];
  public currencyCodes: string[] = [];
  public displayCurrencyCode: string;
  public displayCurrencySign: string;

  public chartGrowth: any;
  public chartAssetTypes: RingChart;
  public chartSectors: RingChart;
  public chartCurrencies: RingChart;
  public chartEarnings: RingChart;
  public chartEmitents: RingChart;
  public chartIncome: IncomeChart;

  public topGainers: Position[] = [];
  public topLosers: Position[] = [];

  public skeletonVisible = true;

  constructor(
    public commonService: CommonService,
    private portfoliosService: PortfoliosService,
    public referencesService: ReferencesService,
    private route: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.referencesService.getReference<CurrencyReference[]>(REFERENCE.CURRENCY, {available: '1'})
      .subscribe((currencies) => {
        this.currencies = currencies;
        this.currencyCodes = currencies.map((item) => item.code);
        if (this.displayCurrencyCode && !this.currencyCodes.includes(this.displayCurrencyCode)) {
          this.currencyCodes = [...this.currencyCodes, this.displayCurrencyCode];
        }
      });
    this.referencesService.getReference<Common[]>(REFERENCE.ASSET).subscribe((assetTypes) => {
      this.assetTypes = assetTypes;
    });
    this.loadAnalytics(localStorage.getItem(ANALYTICS_CURRENCY_KEY));
  }

  loadAnalytics(currency?: string) {
    this.skeletonVisible = true;
    const params = currency ? {currency} : undefined;
    this.portfoliosService.getAnalyticsByUid(this.route.snapshot.params.uid, params)
      .pipe(catchError(() => {
        // this.router.navigate(['/portfolios']);
        return of({} as PortfolioAnalytics);
      }))
      .subscribe((data ) => {
        this.portfolio = data.portfolio;
        this.content = data.content;
        this.analytics = data.analytics;
        this.displayCurrencyCode = data.display_currency?.code || this.portfolio?.currency_code;
        this.displayCurrencySign = data.display_currency?.sign || this.portfolio?.currency_sign;
        if (this.displayCurrencyCode && !this.currencyCodes.includes(this.displayCurrencyCode)) {
          this.currencyCodes = [...this.currencyCodes, this.displayCurrencyCode];
        }
        localStorage.setItem(CURRENT_PORTFOLIO_KEY, `${ this.portfolio.uid }`);
        this.portfolios = JSON.parse(localStorage.getItem(PORTFOLIOS_LIST_KEY));
        this.chartGrowth = this.processGrowthChart(this.analytics.growth);
        this.chartAssetTypes = this.processBreakdownChart(this.analytics.asset_types);
        this.chartSectors = this.processBreakdownChart(this.analytics.sectors);
        this.chartCurrencies = this.processBreakdownChart(this.analytics.currencies);
        this.chartEarnings = this.processEarningsChart(this.analytics.earnings);
        this.chartEmitents = this.processEmitentsChart(this.content);
        this.chartIncome = this.processIncomeChart(this.analytics.income_by_years);
        this.processTopMovers(this.content);
        this.skeletonVisible = false;
      });
  }

  changeDisplayCurrency(currencyCode: string) {
    if (!currencyCode || currencyCode === this.displayCurrencyCode) {
      return;
    }
    localStorage.setItem(ANALYTICS_CURRENCY_KEY, currencyCode);
    this.loadAnalytics(currencyCode);
  }

  processGrowthChart(growthData: any[]) {
    const labelsX = growthData.map((item) => (item.month));
    const currencySign = this.displayCurrencySign;
    const valuesGrowth = [];
    const valuesChange = [];
    let max = 0;
    growthData.map((item) => {
      valuesGrowth.push(+item.cumulative.toFixed());
      valuesChange.push(+item.total.toFixed());
      if (+item.cumulative.toFixed() > max) {
        max = +item.cumulative.toFixed();
      }
      if (+item.total.toFixed() > max) {
        max = +item.total.toFixed();
      }
    });
    const labelsY = [0, currencySign + ' ' + max.toLocaleString()];
    const hint = ({$implicit: e}) => {
      return currencySign + ' ' + valuesGrowth[e].toLocaleString() + '\n'
        + currencySign + ' ' + valuesChange[e].toLocaleString();
    };
    return {
      labelsX,
      labelsY,
      currencySign,
      values: [
        valuesGrowth,
        valuesChange
      ],
      hint,
      max
    };
  }

  processBreakdownChart(items: AnalyticsBreakdownItem[] = []): RingChart {
    return this.buildRingChart(items.map((item) => ({label: item.label, value: item.total_cost})));
  }

  processEarningsChart(earnings: AnalyticsEarningsItem[] = []): RingChart {
    return this.buildRingChart(earnings.map((item) => ({label: item.label, value: item.earnings})));
  }

  processEmitentsChart(content: Position[] = []): RingChart {
    return this.buildRingChart(content
      .filter((item) => item.ticker !== MONEY_TICKER)
      .map((item) => ({label: item.title ?? item.ticker, value: item.current_cost})));
  }

  processIncomeChart(rows: AnalyticsIncomeByYear[] = []): IncomeChart {
    const income = rows.map((row) => +(row.dividends + row.coupons).toFixed());
    const expenses = rows.map((row) => +(row.fees + row.taxes).toFixed());
    const max = Math.max(1, ...income, ...expenses);
    return {
      labelsX: rows.map((row) => `${row.year}`),
      labelsY: ['', `${this.displayCurrencySign} ${max.toLocaleString()}`],
      values: [income, expenses],
      max,
    };
  }

  processTopMovers(content: Position[] = []) {
    const open = content
      .filter((item) => item.ticker !== MONEY_TICKER && item.count !== 0)
      .sort((a, b) => b.current_gain_percent - a.current_gain_percent);
    this.topGainers = open.filter((item) => item.current_gain_percent > 0).slice(0, TOP_MOVERS_COUNT);
    this.topLosers = open.filter((item) => item.current_gain_percent < 0).reverse().slice(0, TOP_MOVERS_COUNT);
  }

  buildRingChart(items: {label: string, value: number}[]): RingChart {
    const sorted = items
      .filter((item) => item.value > 0)
      .sort((a, b) => b.value - a.value);
    const segments = sorted.length > MAX_RING_SEGMENTS
      ? [
        ...sorted.slice(0, MAX_RING_SEGMENTS - 1),
        {
          label: $localize`:@@analytics.other:Other`,
          value: sorted.slice(MAX_RING_SEGMENTS - 1).reduce((sum, item) => sum + item.value, 0),
        },
      ]
      : sorted;
    const total = segments.reduce((sum, item) => sum + item.value, 0);
    return {
      labels: segments.map((item) => item.label),
      values: segments.map((item) => item.value),
      percents: segments.map((item) => total ? item.value / total * 100 : 0),
      total,
      activeIndex: NaN,
    };
  }

  ringLabel(chart: RingChart): string {
    return isNaN(chart.activeIndex) ? $localize`:@@analytics.total:Total` : chart.labels[chart.activeIndex];
  }

  ringValue(chart: RingChart): number {
    return isNaN(chart.activeIndex) ? chart.total : chart.values[chart.activeIndex];
  }

  ringPercent(chart: RingChart): number | null {
    return isNaN(chart.activeIndex) ? null : chart.percents[chart.activeIndex];
  }

  setActive(chart: RingChart, index: number = NaN) {
    chart.activeIndex = index;
  }

  getColor(index: number): string {
    return `var(--tui-chart-categorical-0${index})`;
  }

  netIncome(row: AnalyticsIncomeByYear): number {
    return row.dividends + row.coupons - row.fees - row.taxes;
  }

}
