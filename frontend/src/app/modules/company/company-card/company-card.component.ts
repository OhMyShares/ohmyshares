import {DatePipe} from '@angular/common';
import {Component, HostBinding, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {AuthService} from '../../../core/services/auth.service';
import {CommonService} from '../../../core/services/common.service';
import {Ticker, TickerDividend, TickerHistory} from '../../../core/interfaces/tickers.interfaces';
import {TickersService} from '../../../core/services/tickers.service';

@Component({
    selector: 'app-company-card',
    templateUrl: './company-card.component.html',
    styleUrls: ['./company-card.component.scss'],
    providers: [DatePipe],
    standalone: false
})
export class CompanyCardComponent implements OnInit {
  @HostBinding('class') classes = 'align-items-center justify-content-center';

  public info: Ticker;
  public history: TickerHistory[];
  public dividends: TickerDividend[];

  public labelsX = [];
  public labelsY = [];
  public chartMax = 100;
  public chartData = [];

  constructor(
    public commonService: CommonService,
    public authService: AuthService,
    public tickersService: TickersService,
    private route: ActivatedRoute,
  ) {
  }

  ngOnInit() {
    this.tickersService.getInfo(this.route.snapshot.params.ticker).subscribe((info) => {
      this.info = info;
    });
    this.tickersService.getHistory(this.route.snapshot.params.ticker).subscribe((history) => {
      this.history = history;
    });
    this.tickersService.getDividends(this.route.snapshot.params.ticker).subscribe((dividends) => {
      this.dividends = dividends;
      this.processChartData(dividends);
    });
  }

  processChartData(dividends) {
    const currYear = new Date().getFullYear();
    let max = 0;
    const groups = dividends.reduce((acc, div) => {
      const year = div.date.split('-')[0];
      const month = div.date.split('-')[1];
      let q = 0;
      if (month >= 1 && month <= 3) { q = 0; }
      if (month >= 4 && month <= 6) { q = 1; }
      if (month >= 7 && month <= 9) { q = 2; }
      if (month >= 10 && month <= 12) { q = 3; }
      if (!acc[q]) {
        acc[q] = [];
      }
      if (currYear - year <= 10) {
        if (!this.labelsX[year]) { this.labelsX[year] = year; }
        if (div.dividends > max) { max = div.dividends; }
        acc[q][year] = div.dividends;
        acc[q] = Object.values(acc[q]);
      }
      return acc;
    }, {});
    this.labelsX = Object.values(this.labelsX);
    this.labelsY = [
      this.info.currency_sign + `0`,
      this.info.currency_sign + max.toFixed(2),
      this.info.currency_sign + (max * 2).toFixed(2),
      this.info.currency_sign + (max * 3).toFixed(2),
      this.info.currency_sign + (max * 4).toFixed(2)
    ];
    this.chartMax = max * 4;
    this.chartData = Object.values(groups);
    // console.log(this.labelsX);
    // console.log(this.labelsY);
    // console.log(this.chartData);
  }

}
