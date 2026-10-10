import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, Resolve, RouterStateSnapshot } from '@angular/router';
import { Observable, zip } from 'rxjs';
import { CustomAsset, CustomAssetPrice } from '../../../core/interfaces/custom_assets.interfaces';
import { CustomAssetsService } from '../../../core/services/custom_assets.service';

@Injectable({
  providedIn: 'root'
})
export class CustomAssetViewResolver implements Resolve<[CustomAsset, CustomAssetPrice[]]> {

  constructor(
    private customAssetsService: CustomAssetsService
  ) {
  }

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<[CustomAsset, CustomAssetPrice[]]> {
    return zip(
      this.customAssetsService.getOneById(route.params.id),
      this.customAssetsService.getPrices({custom_asset_id: route.params.id})
    );
  }
}
