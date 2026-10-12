import { tuiPure } from "@taiga-ui/legacy";
import { TuiNotificationService } from "@taiga-ui/core";
import {Component, HostBinding} from '@angular/core';
import {UntypedFormBuilder, UntypedFormControl, UntypedFormGroup, Validators} from '@angular/forms';
// import {MatAutocompleteSelectedEvent} from '@angular/material/autocomplete';
import {ActivatedRoute, Router} from '@angular/router';
import * as moment from 'moment';
import {forkJoin, Observable, of, Subject, throwError} from 'rxjs';
import {catchError, debounceTime, filter, finalize, map, mergeMap, take, tap} from 'rxjs/operators';
// import {AlertTypes} from '../../../core/interfaces/alert.interfaces';
import {FormField} from '../../../core/interfaces/common.interfaces';
import {Portfolio} from '../../../core/interfaces/portfolios.interfaces';
import {Trade} from '../../../core/interfaces/trades.interfaces';
// import {AlertService} from '../../../core/services/alert.service';
import {CommonService} from '../../../core/services/common.service';
import {Common, CurrencyRate, CurrencyReference, Quote, REFERENCE, ReferencesService} from '../../../core/services/references.service';
import {TradesService} from '../../../core/services/trades.service';
import {Ticker} from '../../../core/interfaces/tickers.interfaces';
import { TuiDay, TuiStringHandler, TuiContext } from '@taiga-ui/cdk';
import {PortfoliosService} from '../../../core/services/portfolios.service';
import {ASSET_TYPE, CURRENT_PORTFOLIO_KEY, TRADE_TYPE} from '../../../core/constants';
import {SpinnerService} from '../../../core/services/spinner.service';
import {delay, startWith, switchMap} from 'rxjs/operators';
import {TickersService} from '../../../core/services/tickers.service';

@Component({
    selector: 'app-trade-card',
    templateUrl: './trade-card.component.html',
    styleUrls: ['./trade-card.component.scss'],
    standalone: false
})
export class TradeCardComponent {
  // @HostBinding('class') classes = '';

  public editForm = new UntypedFormGroup({
    portfolio_uid: new UntypedFormControl('', [Validators.required]),
    asset: new UntypedFormControl('', [Validators.required]),
    type: new UntypedFormControl('', [Validators.required]),
    ticker: new UntypedFormControl('', [Validators.required]),
    currency_code: new UntypedFormControl('', [Validators.required]),
    cost: new UntypedFormControl('', [Validators.required]),
    shares: new UntypedFormControl('', [Validators.required]),
    sum: new UntypedFormControl('', [Validators.required]),
    created_at: new UntypedFormControl('', [Validators.required]),
    fee_currency_code: new UntypedFormControl('', [Validators.required]),
    fee: new UntypedFormControl('', [
      Validators.pattern('^[0-9]+((\\.|\\,)[0-9]{1,5})?$'),
    ]),
    notes: new UntypedFormControl(''),
    is_wo_money: new UntypedFormControl(''),
  });
  public trade: Trade;
  public portfolios: Portfolio[];
  public tickers: Ticker[];
  public searchTicker$: Subject<string> = new Subject();
  public isLoading = false;
  public isLoadingCost = false;
  public isSaving = false;
  public isDeleting = false;
  public currencies: CurrencyReference[];
  public currencyCodes: string[];
  public tradeTypes: Common[];
  public tradeTypesFiltered: Common[];
  public assetTypes: Common[];
  public currencyRates: CurrencyRate[];
  public showSkeleton = false;

  isLoading$ = this.spinner$.isLoading$;

  constructor(
    private tradesService: TradesService,
    private portfoliosService: PortfoliosService,
    public referencesService: ReferencesService,
    public tickersService: TickersService,
    private route: ActivatedRoute,
    private router: Router,
    private spinner$: SpinnerService,
    private alertService: TuiNotificationService,
    public commonService: CommonService,
    private formBuilder: UntypedFormBuilder
  ) {
    this.searchTicker$
      .pipe(
        filter(Boolean),
        debounceTime(500),
        tap(() => {
          this.tickers = [];
          this.isLoading = true;
        }),
        mergeMap((q: string) =>
          this.referencesService.getReference<Ticker[]>(REFERENCE.TICKER,
            {q, asset: this.editForm.get('asset').value}, {noSpinner: ''})
          .pipe(
            finalize(() => this.isLoading = false)
          )
        )
      )
      .subscribe((tickers) => this.tickers = tickers);

    this.showSkeleton = true;
    const tradeId = this.route.snapshot.params.id;
    forkJoin({
      portfolios: this.portfoliosService.getList(),
      assetTypes: this.referencesService.getReference<Common[]>(REFERENCE.ASSET),
      tradeTypes: this.referencesService.getReference<Common[]>(REFERENCE.TRADE_TYPE),
      currencies: this.referencesService.getReference<CurrencyReference[]>(REFERENCE.CURRENCY),
      trade: tradeId === 'new'
        ? of({portfolio_uid: localStorage.getItem(CURRENT_PORTFOLIO_KEY)} as Trade)
        : this.tradesService.getOneById(+tradeId).pipe(
          map((trade) => {
            if (!trade.related_trade_id) {
              return trade;
            }
            this.alertService.open($localize`:@@alert.trade.linkedTrade.message:To edit a linked trade, change the parent trade`, {
              appearance: 'info'
            }).subscribe();
            this.router.navigate(['/trades', trade.related_trade_id]);
          }),
          filter((trade: Trade) => !!trade)
        )
    }).subscribe(({portfolios, assetTypes, tradeTypes, currencies, trade}) => {
      this.portfolios = portfolios;
      this.assetTypes = assetTypes;
      this.tradeTypes = tradeTypes;
      this.currencies = currencies;
      this.currencyCodes = currencies.map((item) => item.code);
      this.trade = trade;
      if (trade.id) {
        this.editForm = this.formBuilder.group(trade);
        this.editForm.patchValue({shares: Math.abs(trade.shares)});
      } else {
        this.editForm.patchValue({
          asset: ASSET_TYPE.stock,
          type: TRADE_TYPE.buy,
          is_wo_money: false,
        });
      }
      this.assetTypeChanged();
      const portfolio = this.portfolios.find((item) => item.uid === trade.portfolio_uid);
      const createdAt = moment.unix(trade.created_at || moment().unix());
      this.editForm.patchValue({created_at: new TuiDay(createdAt.year(), createdAt.month(), createdAt.date())});
      if (portfolio && !trade.id) {
        this.editForm.patchValue({
          portfolio_uid: portfolio.uid,
          currency_code: portfolio.currency_code,
          fee_currency_code: portfolio.currency_code,
        });
        // prefill ticker only for new trades
        let ticker = this.route.snapshot.queryParamMap.get('ticker');
        if (ticker && ticker !== '$$') {
          ticker = ticker.replace(/(<([^>]+)>)/gi, '');
          // const e = {option: {value: ticker}};
          // this.trade.ticker = ticker;
          this.editForm.get('ticker').setValue(ticker);
          this.tickerSelected();
          // this.tickerSelected(e as MatAutocompleteSelectedEvent, 'ticker');
        }
      }
      this.showSkeleton = false;
    });
  }

  ngOnInit() {
    this.isLoading$.subscribe((value) => {
      this.isLoading = value;
    });
  }

  assetTypeChanged() {
    const asset = this.getTradeAsset(+this.editForm.value.asset);
    this.tradeTypesFiltered = this.tradeTypes.filter(tradeType => tradeType.linked_asset.includes(asset.id));
    this.tradeTypeChanged();
  }

  tradeTypeChanged() {
    const type = this.getTradeType(+this.editForm.value.type);
    if (type[`hide_ticker`]) { this.editForm.controls.ticker.disable(); } else { this.editForm.controls.ticker.enable(); }
    if (type[`hide_sum`]) { this.editForm.controls.sum.disable(); } else { this.editForm.controls.sum.enable(); }
    if (type[`hide_cost`]) { this.editForm.controls.cost.disable(); } else { this.editForm.controls.cost.enable(); }
    if (type[`hide_shares`]) { this.editForm.controls.shares.disable(); } else { this.editForm.controls.shares.enable(); }
    if (type[`hide_fee`]) { this.editForm.controls.fee.disable(); } else { this.editForm.controls.fee.enable(); }
    if (type[`hide_fee_currency_code`]) { this.editForm.controls.fee_currency_code.disable(); }
      else { this.editForm.controls.fee_currency_code.enable(); }
  }

  getTradeType(id?: number): Common {
    return (id
        ? this.tradeTypes.find((item) => item.id === id)
        : this.tradeTypes.find((item) => item.default)
    ) || {} as Common;
  }

  getTradeAsset(id?: number): Common {
    return (id
        ? this.assetTypes.find((item) => item.id === id)
        : this.assetTypes.find((item) => item.default)
    ) || {} as Common;
  }

  save() {
    if (this.editForm.invalid || this.isLoading || this.isSaving) {
      return;
    }
    this.isSaving = true;
    const data = Object.assign({}, this.trade, this.editForm.value);
    data.created_at = this.editForm.value.created_at.toUtcNativeDate().getTime() / 1000;
    // data.sum = Number(this.editForm.value.sum?.replace(/(\d),(?=\d)/g, '$1.'));
    if (data.id) {
      this.tradesService.update(data).pipe(
        catchError((err) => {
          this.isSaving = false;
          return throwError(err);
        })
      ).subscribe(() => {
        this.alertService.open($localize`:@@alert.trade.update.message:The trade has been successfully updated`, {
          appearance: 'positive'
        }).subscribe();
        history.back();
        });
    } else {
      this.tradesService.create(data).pipe(
        catchError((err) => {
          this.isSaving = false;
          return throwError(err);
        })
      ).subscribe(() => {
        this.alertService.open($localize`:@@alert.trade.create.message:The trade has been successfully created`, {
          appearance: 'positive'
        }).subscribe();
        history.back();
        });
    }
  }

  delete() {
    if (this.isLoading || this.isLoading || this.isDeleting) {
      return;
    }
    this.isDeleting = true;
    this.tradesService.delete(this.trade.id)
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.trade.delete.message:The trade has been successfully deleted`, {
          appearance: 'positive'
        }).subscribe();
        history.back();
      });
  }

  // extractValueFromEvent(event: Event): string | null {
  //   return (event.target as HTMLInputElement)?.value || null;
  // }

  tickerSelected() {
    const symbol = this.editForm.value.ticker;
    const asset = this.editForm.value.asset;
    if (symbol && asset) {
      this.isLoadingCost = true;
      this.referencesService.getReference<Quote>(REFERENCE.QUOTE, {q: symbol, asset}, {noSpinner: ''})
        .pipe(
          take(1)
        )
        .subscribe((quote) => {
          this.isLoadingCost = false;
          if (quote.price) {
            this.editForm.get('cost').setValue(quote.price);
          }
          if (quote.currency_code) {
            this.editForm.get('currency_code').setValue(quote.currency_code);
            // this.editForm.get('fee_currency_code').setValue(quote.currency_code);
          }
        }).add(() => this.isLoadingCost = false);
    }
  }

  getControl(field: string): UntypedFormControl {
    return this.editForm.controls[field] as UntypedFormControl;
  }

  findPortfolio(): Portfolio {
    const uid = this.editForm.get('portfolio_uid').value;
    return this.portfolios.find((item) => item.uid === uid);
  }

  calcFee() {
    const count = this.editForm.get('shares').value;
    const price = this.editForm.get('cost').value;
    const currencyCode = this.editForm.get('currency_code').value;
    const feeCurrencyCode = this.editForm.get('fee_currency_code').value;
    const portfolio = this.findPortfolio();
    const feePercent = Number(portfolio?.fee_percent);
    if (currencyCode && feeCurrencyCode && currencyCode !== feeCurrencyCode) {
      if (!this.currencyRates) {
        this.referencesService.getReference<CurrencyRate[]>(REFERENCE.CURRENCY_RATES).subscribe((rates) => {
          this.currencyRates = rates;
          const rate = this.currencyRates.find(x => x.from_code === currencyCode && x.to_code === feeCurrencyCode).rate;
          this.editForm.get('fee').setValue((rate * count * price / 100 * feePercent).toFixed(2));
        });
      } else {
        const rate = this.currencyRates.find(x => x.from_code === currencyCode && x.to_code === feeCurrencyCode).rate;
        this.editForm.get('fee').setValue((rate * count * price / 100 * feePercent).toFixed(2));
      }
    } else {
      this.editForm.get('fee').setValue((count * price / 100 * feePercent).toFixed(2));
    }
  }

  // setAssetType(asset) {
  //   if (isNumeric(asset)) {
  //     const sharesField = this.formFields.find(x => x.name === 'shares');
  //     sharesField.inputMode = 'numeric';
  //     sharesField.pattern = '[0-9]+';
  //     if (asset === AssetTypes.CRYPTO) {
  //       sharesField.inputMode = 'decimal';
  //       sharesField.pattern = '^[0-9]+((\.|\,)[0-9]{1,10})?$';
  //     }
  //   }
  // }

  @tuiPure
  stringifyPortfolios(items: readonly Portfolio[]): TuiStringHandler<string> {
    const map = new Map(items.map(({uid, name}) => [uid, name] as [string, string]));
    return (uid: string) => map.get(uid) || ``;
  }
}
