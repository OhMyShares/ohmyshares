import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { GetTradesListResponse, Trade, TradesCurrency, TradesTicker } from '../interfaces/trades.interfaces';

const API = {
  TRADES: `${environment.API_ENDPOINT}/trades`,
};

@Injectable({
  providedIn: 'root'
})
export class TradesService {

  constructor(
    private http: HttpClient
  ) {
  }

  public getList(params?: { [param: string]: string }, headers?: {[header: string]: string}): Observable<GetTradesListResponse> {
    return this.http.get<GetTradesListResponse>(API.TRADES, {params, headers});
  }

  public getTickers(params?: { [param: string]: string }): Observable<TradesTicker[]> {
    return this.http.get<TradesTicker[]>(`${API.TRADES}/tickers`, {params});
  }

  public getCurrencies(params?: { [param: string]: string }): Observable<TradesCurrency[]> {
    return this.http.get<TradesCurrency[]>(`${API.TRADES}/currencies`, {params});
  }

  public getOneById(id: number): Observable<Trade> {
    return this.http.get<Trade>(`${API.TRADES}/${id}`);
  }

  public create(trade: Trade): Observable<Trade> {
    return this.http.post<Trade>(`${API.TRADES}`, trade);
  }

  public update(trade: Trade): Observable<Trade> {
    return this.http.patch<Trade>(`${API.TRADES}/${trade.id}`, trade);
  }

  public delete(id: number): Observable<Trade> {
    return this.http.delete<Trade>(`${API.TRADES}/${id}`);
  }
}
