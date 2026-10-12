import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable, of, zip } from 'rxjs';
import { CustomAsset } from '../../../core/interfaces/custom_assets.interfaces';
import { CustomAssetsService } from '../../../core/services/custom_assets.service';
import { CurrencyReference, REFERENCE, ReferencesService } from '../../../core/services/references.service';

@Injectable({
  providedIn: 'root'
})
export class CustomAssetCardResolver  {

  constructor(
    private customAssetsService: CustomAssetsService,
    private referencesService: ReferencesService
  ) {
  }

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<[CustomAsset, CurrencyReference[]]> {
    return zip(
      route.params.id !== 'new'
        ? this.customAssetsService.getOneById(route.params.id)
        : of({} as CustomAsset),
      this.referencesService.getReference<CurrencyReference[]>(REFERENCE.CURRENCY)
    );
  }
}
