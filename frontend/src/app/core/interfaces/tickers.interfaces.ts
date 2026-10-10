export interface Ticker {
  ticker: string;
  isin?: string;
  symbol: string;
  name: string;
  exchange?: string;
  type: string;
  region?: string;
  price?: number;
  marketOpen?: string;
  marketClose?: string;
  timezone?: string;
  currency?: string;
  currency_code?: string;
  currency_sign?: string;
  type_label?: string;
  matchScore?: string;
}

export interface TickerHistory {
  date: string;
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
}

export interface TickerDividend {
  date: string;
  dividend: number;
}

