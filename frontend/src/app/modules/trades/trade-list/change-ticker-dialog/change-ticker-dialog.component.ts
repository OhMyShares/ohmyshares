import {Component, Inject, OnDestroy} from '@angular/core';
import {UntypedFormControl, UntypedFormGroup, Validators} from '@angular/forms';
import { TuiDialogContext, TuiNotificationService } from '@taiga-ui/core';
import {POLYMORPHEUS_CONTEXT} from '@taiga-ui/polymorpheus';
import {Observable, of, Subject} from 'rxjs';
import {catchError, debounceTime, distinctUntilChanged, finalize, map, switchMap, takeUntil, tap} from 'rxjs/operators';
import {Position} from '../../../../core/interfaces/portfolios.interfaces';
import {Ticker} from '../../../../core/interfaces/tickers.interfaces';
import {PortfoliosService} from '../../../../core/services/portfolios.service';
import {REFERENCE, ReferencesService} from '../../../../core/services/references.service';

export interface ChangeTickerDialogData {
  position: Position;
}

@Component({
    selector: 'app-change-ticker-dialog',
    templateUrl: './change-ticker-dialog.component.html',
    standalone: false
})
export class ChangeTickerDialogComponent implements OnDestroy {
  public form = new UntypedFormGroup({
    ticker: new UntypedFormControl('', [Validators.required]),
  });
  public tickers: Ticker[] = [];
  public isSearching = false;
  public isSaving = false;

  private selected: Ticker | null = null;
  private readonly destroy$ = new Subject<void>();

  constructor(
    private portfoliosService: PortfoliosService,
    private referencesService: ReferencesService,
    private alertService: TuiNotificationService,
    @Inject(POLYMORPHEUS_CONTEXT) private readonly context: TuiDialogContext<Position | null, ChangeTickerDialogData>,
  ) {
    this.form.get('ticker').valueChanges
      .pipe(
        map((value: string) => (value || '').trim()),
        debounceTime(300),
        distinctUntilChanged(),
        tap(() => this.isSearching = true),
        switchMap((q: string) => this.searchTickers(q)),
        takeUntil(this.destroy$),
      )
      .subscribe((tickers) => {
        this.isSearching = false;
        this.tickers = tickers || [];
      });
  }

  get position(): Position {
    return this.context.data.position;
  }

  get newTicker(): string {
    return (this.form.value.ticker || '').trim().toUpperCase();
  }

  isSameTicker(): boolean {
    return this.newTicker === (this.position.ticker || '').toUpperCase() && !this.selectedExchange();
  }

  select(ticker: Ticker): void {
    this.selected = ticker;
  }

  submit(): void {
    if (this.form.invalid || !this.newTicker || this.isSameTicker() || this.isSaving) {
      return;
    }
    this.isSaving = true;
    this.portfoliosService.changePortfolioItemTicker(this.position.uid, this.newTicker, this.selectedExchange())
      .pipe(finalize(() => this.isSaving = false))
      .subscribe((position) => {
        this.alertService.open($localize`:@@alert.portfolioItem.changeTicker.message:Ticker has been successfully changed`, {
          appearance: 'positive'
        }).subscribe();
        this.context.completeWith(position);
      });
  }

  close(): void {
    this.context.completeWith(null);
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private selectedExchange(): string | undefined {
    return this.selected && this.selected.symbol.toUpperCase() === this.newTicker
      ? this.selected.exchange || undefined
      : undefined;
  }

  private searchTickers(q: string): Observable<Ticker[]> {
    if (!q) {
      return of([]);
    }
    const params: { [param: string]: string } = {q};
    if (this.position.asset) {
      params.asset = String(this.position.asset);
    }
    return this.referencesService.getReference<Ticker[]>(REFERENCE.TICKER, params, {noSpinner: ''})
      .pipe(catchError(() => of([])));
  }
}
