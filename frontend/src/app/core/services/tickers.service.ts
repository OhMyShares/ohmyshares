import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {environment} from '../../../environments/environment';
import {Ticker, TickerDividend, TickerHistory} from '../interfaces/tickers.interfaces';
import {TuiContextWithImplicit, tuiPure, TuiStringHandler} from '@taiga-ui/cdk';
import {Common} from './references.service';

const API = {
  TICKERS: `${environment.API_ENDPOINT}/tickers`,
};

@Injectable({
  providedIn: 'root'
})
export class TickersService {

  constructor(
    private http: HttpClient
  ) {
  }

  public getList(params?: { [param: string]: string }): Observable<Ticker[]> {
    return this.http.get<Ticker[]>(`${API.TICKERS}/list`, {params});
  }

  public getInfo(ticker: string): Observable<Ticker> {
    return this.http.get<Ticker>(`${API.TICKERS}/${ticker}/info`);
  }

  public getHistory(ticker: string, params?: { [param: string]: string }): Observable<TickerHistory[]> {
    return this.http.get<TickerHistory[]>(`${API.TICKERS}/${ticker}/history`, {params});
  }

  public getDividends(ticker: string, params?: { [param: string]: string }): Observable<TickerDividend[]> {
    return this.http.get<TickerDividend[]>(`${API.TICKERS}/${ticker}/dividends`, {params});
  }

  @tuiPure
  stringify(items: readonly Ticker[]): TuiStringHandler<TuiContextWithImplicit<string>> {
    const map = new Map(items.map(({symbol, name}) => [symbol, name] as [string, string]));
    return ({$implicit}: TuiContextWithImplicit<string>) => map.get($implicit) || ``;
  }
}
