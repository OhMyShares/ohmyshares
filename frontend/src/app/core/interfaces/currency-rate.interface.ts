export interface CurrencyRateRecord {
  id: number;
  base: string;
  currency: string;
  date: string;
  rate: number;
  source: string | null;
  created_at: string;
  updated_at: string;
}

export interface CurrencyRatesProvider {
  package: string;
  title: string;
}

export interface CurrencyRatesParams {
  currency?: string;
  limit: number;
  offset: number;
}

export interface CurrencyRatesResponse {
  data: CurrencyRateRecord[];
  total: number;
  providers: CurrencyRatesProvider[];
}

export interface CurrencyRatesUpdateResponse {
  count: number;
  providers: CurrencyRatesProvider[];
}
