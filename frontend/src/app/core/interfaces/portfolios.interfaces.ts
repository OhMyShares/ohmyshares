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
  display_currency: AnalyticsDisplayCurrency;
}

export interface AnalyticsDisplayCurrency {
  code: string;
  sign: string;
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

export interface AnalyticsBreakdownItem {
  label: string;
  currency_sign: string;
  total_cost: number;
  part_percent: number;
}

export interface AnalyticsEarningsItem {
  label: string;
  currency_sign: string;
  earnings: number;
  earnings_native: number;
}

export interface AnalyticsGrowthItem {
  month: string;
  total: number;
  cumulative: number;
}

export interface AnalyticsIncomeByYear {
  year: number;
  dividends: number;
  coupons: number;
  fees: number;
  taxes: number;
}

export interface AnalyticsSummary {
  total_cost: number;
  cash: number;
  cost_basis: number;
  total_gain: number;
  unrealized_gain: number;
  unrealized_gain_percent: number;
  realized_gain: number;
  earnings: number;
  total_return: number;
  positions_count: number;
  profitable_count: number;
  losing_count: number;
  largest_part_percent: number;
  top5_part_percent: number;
}

export interface PortfolioAnalyticsData {
  asset_types: AnalyticsBreakdownItem[];
  sectors: AnalyticsBreakdownItem[];
  currencies: AnalyticsBreakdownItem[];
  earnings: AnalyticsEarningsItem[];
  growth: AnalyticsGrowthItem[];
  income_by_years: AnalyticsIncomeByYear[];
  summary: AnalyticsSummary;
  total_earnings: number;
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
