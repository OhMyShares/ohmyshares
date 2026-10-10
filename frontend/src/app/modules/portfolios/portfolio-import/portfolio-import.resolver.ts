import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, Resolve, RouterStateSnapshot } from '@angular/router';
import { Observable, of, zip } from 'rxjs';
import { Common, REFERENCE, ReferencesService } from '../../../core/services/references.service';

@Injectable({
  providedIn: 'root'
})
export class PortfolioImportResolver implements Resolve<[string, Common[]]> {

  constructor(
    private referencesService: ReferencesService
  ) {
  }

  resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<[string, Common[]]> {
    return zip(
      of(route.params.uid),
      this.referencesService.getReference<Common[]>(REFERENCE.PARSER)
    );
  }
}
