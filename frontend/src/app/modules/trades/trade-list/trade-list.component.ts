import { DatePipe } from '@angular/common';
import {Component, HostBinding, Inject, Injector, OnInit} from '@angular/core';
import {UntypedFormControl, UntypedFormGroup, Validators} from '@angular/forms';
import { ActivatedRoute, Params, Router } from '@angular/router';
import * as moment from 'moment';
import { BehaviorSubject, of, Subject, zip } from 'rxjs';
import { finalize, map, mergeMap, takeWhile, tap } from 'rxjs/operators';
import {COLORS, CURRENT_PORTFOLIO_KEY, TRADE_TYPE} from '../../../core/constants';
import { FormField, TableColumn } from '../../../core/interfaces/common.interfaces';
import { Portfolio, Position } from '../../../core/interfaces/portfolios.interfaces';
import { Trade, TradesCurrency, TradesTicker } from '../../../core/interfaces/trades.interfaces';
import { CommonService } from '../../../core/services/common.service';
import { PortfoliosService } from '../../../core/services/portfolios.service';
import { Common, REFERENCE, ReferencesService } from '../../../core/services/references.service';
import { TradesService } from '../../../core/services/trades.service';
import { FilterDialogComponent } from './filter-dialog/filter-dialog.component';
import { ChangeTickerDialogComponent, ChangeTickerDialogData } from './change-ticker-dialog/change-ticker-dialog.component';
import { TickersService } from '../../../core/services/tickers.service';
import {Ticker, TickerHistory} from '../../../core/interfaces/tickers.interfaces';
import { AuthService } from '../../../core/services/auth.service';
import {TuiAlertService, TuiDialogService, TuiNotification} from '@taiga-ui/core';
import {PolymorpheusComponent} from '@tinkoff/ng-polymorpheus';

const TRADE_TYPE_CSS_CLASSES = [
  '', 'text-success', 'text-danger', 'text-success', 'text-danger', 'text-success',
  'text-danger', 'text-success', 'text-danger' ];

@Component({
  selector: 'app-trade-list',
  templateUrl: './trade-list.component.html',
  styleUrls: [ './trade-list.component.scss' ],
  providers: [ DatePipe ]
})
export class TradeListComponent implements OnInit {
  // @HostBinding('class') classes = '';

  public showSkeleton = false;
  public data: Trade[] = [];
  public displayedColumns = ['ticker', 'cost', 'shares', 'sum', 'fee', 'created_at'];
  public tradeTypes: Common[];
  public assetTypes: Common[];
  public tradeTickers: TradesTicker[];
  public tradeCurrencies: TradesCurrency[];
  public portfolios: Portfolio[];
  public params: Params = {
    offset: '0',
    limit: '20',
    sort: 'created_at',
    direction: 'desc',
    portfolio_uid: localStorage.getItem(CURRENT_PORTFOLIO_KEY)
  };
  public summary: Position;
  public total: number;
  public pageIndex: number;
  public pageSize: number;
  public ranges: any = {
    [$localize`:@@table.trades.dateRanges.today:Today`]: [ moment(), moment() ],
    [$localize`:@@table.trades.dateRanges.yesterday:Yesterday`]: [ moment().subtract(1, 'days'), moment().subtract(1, 'days') ],
    [$localize`:@@table.trades.dateRanges.last7days:Last 7 days`]: [ moment().subtract(6, 'days'), moment() ],
    [$localize`:@@table.trades.dateRanges.last30days:Last 30 days`]: [ moment().subtract(1, 'month'), moment() ],
    [$localize`:@@table.trades.dateRanges.thisMonth:This month`]: [ moment().startOf('month'), moment().endOf('month') ],
    [$localize`:@@table.trades.dateRanges.lastMonth:Last month`]: [
      moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')
    ],
    [$localize`:@@table.trades.dateRanges.last6months:Last 6 months`]: [ moment().subtract(6, 'month'), moment() ],
    [$localize`:@@table.trades.dateRanges.lastYear:Last year`]: [ moment().subtract(1, 'year'), moment() ],
    [$localize`:@@table.trades.dateRanges.wholeTime:Whole time`]: [ moment(0), moment() ]
  };
  public searchForm: UntypedFormGroup = new UntypedFormGroup({
    asset: new UntypedFormControl('', [Validators.required]),
    type: new UntypedFormControl('', [Validators.required]),
    // currency_code: new FormControl('', [Validators.required]),
  });

  public showForm = false;
  public today = moment();
  public trades$: BehaviorSubject<Trade[]> = new BehaviorSubject([]);
  public loadMore$: Subject<void> = new Subject();
  public isShowFilter = false;
  public isLoading = false;
  public portfolioUid = null;
  public isActionsOpen = false;

  public tickerHistories: TickerHistory[];
  public chartOptions: any;

  constructor(
    private tradesService: TradesService,
    private tickersService: TickersService,
    private portfoliosService: PortfoliosService,
    private referencesService: ReferencesService,
    public authService: AuthService,
    public commonService: CommonService,
    private alertService: TuiAlertService,
    private router: Router,
    public route: ActivatedRoute,
    private datePipe: DatePipe,
    @Inject(TuiDialogService) private readonly dialogService: TuiDialogService,
    @Inject(Injector) private readonly injector: Injector,
  ) {
  }

  ngOnInit() {
    this.showSkeleton = true;
    this.router.routeReuseStrategy.shouldReuseRoute = () => false;
    this.route.queryParams
      .pipe(
        mergeMap((params: Params) => zip(
          this.params.portfolio_uid && this.params.portfolio_uid === params.portfolio_uid
            ? of(this.tradeTickers)
            : this.tradesService.getTickers(params.portfolio_uid ? { portfolio_uid: params.portfolio_uid } : null),
          this.params.portfolio_uid && this.params.portfolio_uid === params.portfolio_uid
            ? of(this.tradeCurrencies)
            : this.tradesService.getCurrencies(params.portfolio_uid ? { portfolio_uid: params.portfolio_uid } : null),
          this.tradesService.getList(this.commonService.prepareParams(Object.assign(this.params, params))),
          this.portfolios ? of(this.portfolios) : this.portfoliosService.getList(),
          this.tradeTypes ? of(this.tradeTypes) : this.referencesService.getReference<Common[]>(REFERENCE.TRADE_TYPE),
          this.assetTypes ? of(this.assetTypes) : this.referencesService.getReference<Common[]>(REFERENCE.ASSET)
          )
        )
      )
      .subscribe(([tradeTickers, tradeCurrencies, trades, portfolios, tradeTypes, assetTypes]) => {
        this.portfolioUid = this.route.snapshot.queryParamMap.get('portfolio_uid');
        this.trades$.next(trades.data);
        this.showSkeleton = false;
        this.data = trades.data;
        this.total = trades.total;
        this.summary = trades.summary;
        this.pageSize = parseInt(this.params.limit, 10);
        this.pageIndex = parseInt(this.params.offset, 10) / this.pageSize;
        this.portfolios = portfolios;
        this.tradeTypes = tradeTypes;
        this.assetTypes = assetTypes;
        this.tradeTickers = tradeTickers;
        this.tradeCurrencies = tradeCurrencies;
        if (this.summary && this.summary.asset && trades.data.length) {
          const tradeDates = trades.data.map(item => item.created_at);
          const beginDate = moment.unix(Math.min(...tradeDates)).add(-1, 'month').format('YYYY-MM-DD');
          const endDate = moment.min(moment.unix(Math.max(...tradeDates)).add(1, 'month'), moment()).format('YYYY-MM-DD');
          const tickerHistoriesObservable = this.tickersService.getHistory(this.params.ticker, {
            begin: beginDate,
            end: endDate,
            asset: this.summary.asset.toString(),
          });
          tickerHistoriesObservable.subscribe(tickerHistories => {
            this.tickerHistories = tickerHistories;
            this.chartOptions = this.params.ticker ? this.processChart(this.tickerHistories, trades.data) : null;
          });
        }
        // this.formFields
        //   .forEach((field) =>
        //     this.searchForm.addControl(
        //       field.name,
        //       new FormControl(field.set(this.params[field.name] || ''))
        //     )
        //   );
        // const datesRange = {
        //   startDate: +this.params.created_at_begin ? moment.unix(+this.params.created_at_begin) : moment().subtract(1, 'month'),
        //   endDate: +this.params.created_at_end ? moment.unix(+this.params.created_at_end) : moment()
        // };
        // this.searchForm.addControl('rangePicker', new FormControl(datesRange));
        // this.searchForm.addControl('startDate', new FormControl(datesRange.startDate.format('YYYY-MM-DD')));
        // this.searchForm.addControl('endDate', new FormControl(datesRange.endDate.format('YYYY-MM-DD')));
        this.showForm = true;
      });

    this.loadMore$
      .pipe(
        takeWhile(() => this.trades$.getValue().length < this.total),
        map(() => this.trades$.getValue().length),
        tap(() => this.isLoading = true),
        mergeMap((offset: number) =>
          this.tradesService.getList(
            this.commonService.prepareParams(Object.assign(this.params, { offset })), { noSpinner: '' }
          ).pipe(finalize(() => this.isLoading = false))
        ),
        map(({ data }) => data)
      )
      .subscribe((trades) => this.trades$.next(this.trades$.getValue().concat(trades)));
  }

  getTradeTypeCssClass(type): string {
    return TRADE_TYPE_CSS_CLASSES[type];
  }

  getTradeTypeName(trade: Trade): string {
    return (this.tradeTypes || []).find((item) => item.id === trade.type)?.name;
  }

  getPortfolioName(trade: Trade): string {
    return (this.portfolios || []).find((item) => item.uid === trade.portfolio_uid)?.name;
  }

  search(params?: { [param: string]: string }): void {
    const queryParams = this.commonService.prepareParams(Object.assign(this.params, params));
    console.log(queryParams);
    this.isShowFilter = false;
    this.router.navigate([], { queryParams });
  }

  pageChanged(index) {
    this.search({
      limit: this.pageSize.toString(),
      offset: (this.pageSize * index).toString(),
    });
  }

  datesRangeChanged(event: any) {
    const { startDate, endDate } = event;
    if (!startDate || !endDate) {
      return;
    }
    const range = {
      created_at_begin: Math.floor(+startDate / 1000),
      created_at_end: Math.floor(+endDate / 1000)
    };
    this.router.navigate([], { queryParams: Object.assign(this.params, range) });
  }

  searchDate(key: string, field: string) {
    const date = this.searchForm.get(field).value;
    if (date) {
      this.search({ [key]: `${ moment(date).unix() }`, offset: '0' });
    }
  }

  openTradeCard(trade: Trade): void {
    if (!trade?.related_trade_id) {
      this.router.navigate([ '/trades', trade.id ]);
    }
  }

  openFilterDialog() {
    const dialog = this.dialogService.open(
      new PolymorpheusComponent(FilterDialogComponent, this.injector),
      {
        data: {
          opener: this,
        },
        dismissible: true,
        label: $localize`:@@table.trades.filter.title:Trades Filter`,
      },
    );
    dialog.subscribe({
      next: data => {
        console.log(`Dialog emitted data = ${data}`);
      },
      complete: () => {
        console.log(`Dialog closed`);
      },
    });
  }

  openChangeTickerDialog() {
    this.isActionsOpen = false;
    this.dialogService.open<Position | null>(
      new PolymorpheusComponent(ChangeTickerDialogComponent, this.injector),
      {
        data: { position: this.summary } as ChangeTickerDialogData,
        dismissible: true,
        label: $localize`:@@dialog.changeTicker.title:Change ticker`,
      },
    ).subscribe((position) => {
      if (!position) {
        return;
      }
      this.router.navigate([], {
        queryParams: {
          ...this.route.snapshot.queryParams,
          ticker: position.ticker,
          uid: position.uid,
          offset: '0',
        },
      });
    });
  }

  deleteItem() {
    this.portfoliosService.deletePortfolioItem(this.summary.uid)
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.portfolioItem.delete.message:Position has been successfully deleted`, {
          status: TuiNotification.Success
        }).subscribe();
        history.back();
      });
  }

  processChart(tickerHistories: TickerHistory[], trades: Trade[]) {
    const ticker = this.route.snapshot.queryParamMap.get('ticker').toUpperCase();
    const seriesHistory = tickerHistories.map((item) => ({
          x: item.date,
          y: [
            Number(item.open).toFixed(2),
            Number(item.high).toFixed(2),
            Number(item.low).toFixed(2),
            Number(item.close).toFixed(2)
          ]
        })
      );
    const tradesFiltered = trades.filter(item => item.type === TRADE_TYPE.buy || item.type === TRADE_TYPE.sell);
    const earnings = trades.filter(item => item.type === TRADE_TYPE.dividend || item.type === TRADE_TYPE.coupon);
    const seriesTrades = tradesFiltered.map((item) => ({
      x: new Date(item.created_at * 1000),
      y: Number(item.cost).toFixed(2),
      fillColor: item.type === TRADE_TYPE.buy ? COLORS.buy : COLORS.sell,
    }));
    const annotationsEarnings = earnings.map((item) => ({
      x: item.created_at * 1000,
      borderColor: '#00E396',
      label: {
        text: item.currency_sign + ' ' + Number(item.sum).toFixed(2),
        borderColor: '#00E396',
        style: {
          color: '#fff',
          background: '#00E396'
        },
        orientation: 'horizontal',
      },
    }));
    // console.log(annotationsEarnings);
    return {
      series: [
        {
          name: $localize`:@@chart.trades:Trades`,
          type: 'line',
          data: seriesTrades,
        },
        {
          name: $localize`:@@chart.history:History`,
          type: 'candlestick',
          data: seriesHistory,
        },
      ],
      chart: {
        type: 'candlestick',
        height: 350,
        // events: {
        //   scrolled: (chartContext, {xaxis}) => {
        //     console.log(xaxis.min);
        //   }
        // }
        animations: {
          enabled: false
        },
        toolbar: {
          show: !this.commonService.isMobile
        },
      },
      title: {
        text: ticker,
        align: 'left'
      },
      xaxis: {
        type: 'datetime',
        // labels: {
        //   formatter: val => moment(val).format('MMM DD')
        // }
      },
      yaxis: {
        tooltip: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 1,
      },
      markers: {
        size: 5,
      },
      annotations: {
        xaxis: annotationsEarnings,
        yaxis: [
          {
            y: this.summary.average_price,
            borderColor: '#775DD0',
            label: {
              text: this.summary.currency_sign + ' ' + this.summary.average_price.toFixed(2),
              borderColor: '#775DD0',
              style: {
                color: '#fff',
                background: '#775DD0'
              },
            }
          }
        ]
      },
    };
  }
}

