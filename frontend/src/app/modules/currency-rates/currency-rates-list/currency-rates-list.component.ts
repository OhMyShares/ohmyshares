import { TuiNotificationService } from "@taiga-ui/core";
import { TuiTablePaginationEvent } from '@taiga-ui/addon-table';
import {Component, OnDestroy, OnInit} from '@angular/core';
import {FormControl} from '@angular/forms';
import {Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged} from 'rxjs/operators';
import {CurrencyRateRecord, CurrencyRatesProvider} from '../../../core/interfaces/currency-rate.interface';
import {CurrencyRatesService} from '../../../core/services/currency-rates.service';

@Component({
    selector: 'app-currency-rates-list',
    templateUrl: './currency-rates-list.component.html',
    styleUrls: ['./currency-rates-list.component.scss'],
    standalone: false
})
export class CurrencyRatesListComponent implements OnInit, OnDestroy {
  rates: CurrencyRateRecord[] = [];
  providers: CurrencyRatesProvider[] = [];
  total = 0;
  pageIndex = 0;
  pageSize = 50;
  isLoaded = false;
  isLoading = false;
  isUpdating = false;

  readonly pageSizes = [20, 50, 100, 200];
  readonly columns = ['date', 'base', 'currency', 'rate', 'source', 'updated_at'];
  readonly currencyControl = new FormControl('');

  private filterSubscription: Subscription;

  constructor(
    private currencyRatesService: CurrencyRatesService,
    private alertService: TuiNotificationService,
  ) {}

  ngOnInit(): void {
    this.load();
    this.filterSubscription = this.currencyControl.valueChanges
      .pipe(debounceTime(300), distinctUntilChanged())
      .subscribe(() => {
        this.pageIndex = 0;
        this.load();
      });
  }

  ngOnDestroy(): void {
    this.filterSubscription.unsubscribe();
  }

  load(): void {
    this.isLoading = true;
    this.currencyRatesService.getRates({
      currency: (this.currencyControl.value || '').trim(),
      limit: this.pageSize,
      offset: this.pageIndex * this.pageSize,
    }).subscribe({
      next: response => {
        this.rates = response.data;
        this.total = response.total;
        this.providers = response.providers;
        this.isLoaded = true;
        this.isLoading = false;
      },
      error: () => this.isLoading = false,
    });
  }

  onPagination({page, size}: TuiTablePaginationEvent): void {
    this.pageIndex = size === this.pageSize ? page : 0;
    this.pageSize = size;
    this.load();
  }

  update(): void {
    this.isUpdating = true;
    this.currencyRatesService.update().subscribe({
      next: response => {
        this.isUpdating = false;
        this.providers = response.providers;
        this.alertService.open(
          $localize`:@@alert.currencyRates.updated:Stored rates: ${response.count}:count:`,
          {appearance: response.count > 0 ? 'positive' : 'warning'},
        ).subscribe();
        this.pageIndex = 0;
        this.load();
      },
      error: () => this.isUpdating = false,
    });
  }

  providersTitles(): string {
    return this.providers.map(provider => provider.title).join(', ');
  }
}
