export interface Portfolio {
  uid: string;
  name: string;
  user_id?: number;
  currency_code: string;
  currency_sign: string;
  account_type: number;
  opened_at?: string;
  created_at?: string;
  updated_at?: string;
  fee_percent: string;
  goal: number;
  notes: string;
  is_locked: boolean;
}

export interface PortfolioContent {
  content: Position[];
  summary: PortfolioContentSummary;
  portfolio: Portfolio;
}

export interface PortfolioAnalytics {
  content: Position[];
  // summary: any;
  analytics: PortfolioAnalyticsData;
  portfolio: Portfolio;
}

export interface PortfolioAmount {
  currency_code: string;
  currency_sign: string;
  sum: number;
}

export interface PortfolioContentSummary {
  total_cost: number;
  total_gain: number;
  total_gain_percent: number;
}

export interface PortfolioAnalyticsData {
  asset_types: [];
  sectors: [];
  earnings: [];
  growth: [];
  goal_achievement_percent: number;
}

export interface Position {
  ticker: string;
  title?: string;
  count: number;
  sum: string;
  current_price: number;
  average_price: number;
  current_cost: number;
  current_cost_native: number;
  part_percent: number;
  current_gain: number;
  current_gain_native: number;
  current_gain_percent: number;
  currency_code: string;
  currency_sign: string;
  asset: number;
  uid?: string;
  earned_dividends: number;
  earned_coupons: number;
  paid_fees: number;
  paid_taxes: number;
  portfolio?: Portfolio;
}

export interface PortfolioImportResponse {
  result?: boolean;
  message?: string;
}
