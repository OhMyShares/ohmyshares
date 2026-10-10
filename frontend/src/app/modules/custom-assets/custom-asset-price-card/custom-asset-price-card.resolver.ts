import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, Resolve, RouterStateSnapshot } from '@angular/router';
import { Observable, of, zip } from 'rxjs';
import { CustomAssetPrice } from '../../../core/interfaces/custom_assets.interfaces';
import { CustomAssetsService } from '../../../core/services/custom_assets.service';
import { Common } from '../../../core/services/references.service';

@Injectable({
  providedIn: 'root'
})
export class CustomAssetPriceCardResolver implements Resolve<[CustomAssetPrice, Common[]]> {

  constructor(
    private customAssetsService: CustomAssetsService
  ) {
  }

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<[CustomAssetPrice, Common[]]> {
    return zip(
      route.params.price_id !== 'new'
        ? this.customAssetsService.getPriceById(route.params.price_id)
        : of({custom_asset_id: +route.params.id} as CustomAssetPrice),
      this.customAssetsService.getList()
    );
  }
}
