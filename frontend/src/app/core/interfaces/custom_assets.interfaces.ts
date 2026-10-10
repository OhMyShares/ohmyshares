export interface CustomAsset {
    id: number;
    ticker: string;
    currency_code: string;
    name: string;
    info: string;
    currency_sign: string;
    price?: number;
}

export interface CustomAssetPrice {
    id: number;
    custom_asset_id?: number;
    created_at?: number;
    price: number;
    currency_sign?: string;
}
