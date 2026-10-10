import { Position } from './portfolios.interfaces';
import { Trade } from './trades.interfaces';
import { CustomAsset, CustomAssetPrice } from './custom_assets.interfaces';

export interface FormField {
  name: string;
  label: string;
  id?: string;
  type?: string;
  required?: boolean;
  cssClass?: string;
  cssClassWrapper?: string;
  prefix?: string;
  suffix?: string;
  pattern?: string;
  inputMode?: string;
  disabled?: (value: any) => boolean;
  autofocus?: boolean;
  data?: () => void;
  set?: (value?: any) => any;
  get?: (value?: any) => any;
  getValue?: (value?: any) => any;
  onChange?: (value?: any) => any;
}

export interface TableColumn {
  key: string;
  header: string;
  footer?: () => void;
  cell?: (row: Position | Trade | CustomAsset | CustomAssetPrice) => any;
  tooltip?: (row: Position) => any;
}

export interface ValidationError {
  [param: string]: string[];
}
