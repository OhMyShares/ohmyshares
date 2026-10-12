import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable, of, zip } from 'rxjs';
import { Portfolio } from '../../../core/interfaces/portfolios.interfaces';
import { PortfoliosService } from '../../../core/services/portfolios.service';
import { Common, CurrencyReference, REFERENCE, ReferencesService } from '../../../core/services/references.service';

@Injectable({
  providedIn: 'root'
})
export class PortfolioCardResolver  {

  constructor(
    private portfoliosService: PortfoliosService,
    private referencesService: ReferencesService
  ) {
  }

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<[ Portfolio, CurrencyReference[], Common[] ]> {
    return zip(
      route.params.uid !== 'new'
        ? this.portfoliosService.getOneByUid(route.params.uid)
        : of({} as Portfolio),
      this.referencesService.getReference<CurrencyReference[]>(REFERENCE.CURRENCY),
      this.referencesService.getReference<Common[]>(REFERENCE.ACCOUNT_TYPE)
    );
  }
}
