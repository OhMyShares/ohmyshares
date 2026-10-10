import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { CustomAsset, CustomAssetPrice } from '../interfaces/custom_assets.interfaces';

const API = {
  CUSTOM_ASSETS: `${ environment.API_ENDPOINT }/custom-assets`,
  CUSTOM_ASSETS_PRICES: `${ environment.API_ENDPOINT }/custom-assets-prices`,
};

@Injectable({
  providedIn: 'root'
})
export class CustomAssetsService {

  constructor(
    private http: HttpClient
  ) {
  }

  public getList(params?: { [param: string]: string }, headers?: { [header: string]: string }): Observable<CustomAsset[]> {
    return this.http.get<CustomAsset[]>(API.CUSTOM_ASSETS, { params, headers });
  }

  public getOneById(id: number): Observable<CustomAsset> {
    return this.http.get<CustomAsset>(`${ API.CUSTOM_ASSETS }/${ id }`);
  }

  public create(customAsset: CustomAsset): Observable<CustomAsset> {
    return this.http.post<CustomAsset>(`${ API.CUSTOM_ASSETS }`, customAsset);
  }

  public update(customAsset: CustomAsset): Observable<CustomAsset> {
    return this.http.patch<CustomAsset>(`${ API.CUSTOM_ASSETS }/${ customAsset.id }`, customAsset);
  }

  public delete(id: number): Observable<CustomAsset> {
    return this.http.delete<CustomAsset>(`${ API.CUSTOM_ASSETS }/${ id }`);
  }

  // Prices

  public getPrices(params?: { [param: string]: string }, headers?: { [header: string]: string }): Observable<CustomAssetPrice[]> {
    return this.http.get<CustomAssetPrice[]>(API.CUSTOM_ASSETS_PRICES, { params, headers });
  }

  public getPriceById(id: number): Observable<CustomAssetPrice> {
    return this.http.get<CustomAssetPrice>(`${ API.CUSTOM_ASSETS_PRICES }/${ id }`);
  }

  public createPrice(price: CustomAssetPrice): Observable<CustomAssetPrice> {
    return this.http.post<CustomAssetPrice>(`${ API.CUSTOM_ASSETS_PRICES }`, price);
  }

  public updatePrice(price: CustomAssetPrice): Observable<CustomAssetPrice> {
    return this.http.patch<CustomAssetPrice>(`${ API.CUSTOM_ASSETS_PRICES }/${ price.id }`, price);
  }

  public deletePrice(id: number): Observable<CustomAssetPrice> {
    return this.http.delete<CustomAssetPrice>(`${ API.CUSTOM_ASSETS_PRICES }/${ id }`);
  }
}
