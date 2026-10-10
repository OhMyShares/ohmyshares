import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import {TuiContextWithImplicit, tuiPure, TuiStringHandler} from '@taiga-ui/cdk';

const API = {
  REFERENCES: `${environment.API_ENDPOINT}/reference`
};

export enum REFERENCE {
  CURRENCY = 'currency',
  CURRENCY_RATES = 'currency-rates',
  ACCOUNT_TYPE = 'account-type',
  TICKER = 'ticker',
  TRADE_TYPE = 'trade-type',
  QUOTE = 'quote',
  ASSET = 'trade-asset',
  PARSER = 'parsers'
}

export interface Quote {
  symbol: string;
  open: string;
  high: string;
  low: string;
  price: string;
  currency_code: string;
  volume: string;
  latest_trading_day: string;
  previous_close: string;
  change: string;
  change_percent: string;
}

export interface Common {
  id: number;
  name: string;
  default?: boolean;
  code?: string;
  title?: string;
  linked_asset?: number[];
  linked_type?: number[];
}

export interface CurrencyReference {
  code: string;
  name: string;
  title?: string;
}

export interface CurrencyRate {
  from_name: string;
  from_code: string;
  to_name: string;
  to_code: string;
  rate: number;
}

@Injectable({
  providedIn: 'root'
})
export class ReferencesService {

  constructor(
    private http: HttpClient
  ) {
  }

  public getReference<T>(reference: REFERENCE, params?: { [param: string]: string }, headers?: {[header: string]: string}): Observable<T> {
    return this.http.get<T>(`${API.REFERENCES}/${reference}`, {params, headers});
  }

  @tuiPure
  stringify(items: readonly Common[]): TuiStringHandler<TuiContextWithImplicit<number>> {
    const map = new Map(items.map(({id, name}) => [id, name] as [number, string]));
    return ({$implicit}: TuiContextWithImplicit<number>) => map.get($implicit) || ``;
  }

  @tuiPure
  stringifyCurrency(items: readonly CurrencyReference[]): TuiStringHandler<TuiContextWithImplicit<string>> {
    const map = new Map(items.map(({code, name}) => [code, name] as [string, string]));
    return ({$implicit}: TuiContextWithImplicit<string>) => map.get($implicit) || ``;
  }

  public currencyLabel(item: CurrencyReference): string {
    return `${item.name} ${item.code}${item.title ? ` — ${item.title}` : ``}`;
  }

  @tuiPure
  stringifyCurrencySearch(items: readonly CurrencyReference[]): TuiStringHandler<string> {
    const map = new Map(items.map((item) => [item.code, this.currencyLabel(item)] as [string, string]));
    return (code: string) => map.get(code) || code;
  }
}
