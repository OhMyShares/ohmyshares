import {Portfolio, Position} from './portfolios.interfaces';

export interface Trade {
  id: number;
  portfolio_uid: string;
  asset: number;
  type: number;
  ticker: string;
  cost: number;
  shares: number;
  sum: number;
  fee: number;
  user_id?: number;
  currency_sign: string;
  currency_code: string;
  fee_currency_sign: string;
  fee_currency_code: string;
  created_at: number;
  related_trade_id: number;
  is_wo_money: boolean;
  notes: string;
}

export interface TradesTicker {
  symbol: string;
  name: string;
}

export interface TradesCurrency {
  currency_sign: string;
  currency_code: string;
}

export interface GetTradesListResponse {
  data: Trade[];
  summary: Position;
  total: number;
}
