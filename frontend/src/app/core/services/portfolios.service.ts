import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import {Observable, throwError} from 'rxjs';
import { environment } from '../../../environments/environment';
import {Portfolio, PortfolioAnalytics, PortfolioContent, PortfolioImportResponse, Position} from '../interfaces/portfolios.interfaces';
import {catchError} from 'rxjs/operators';

const API = {
  PORTFOLIOS: `${environment.API_ENDPOINT}/portfolios`,
  PORTFOLIO_ITEMS: `${environment.API_ENDPOINT}/portfolio-items`,
};

@Injectable({
  providedIn: 'root'
})
export class PortfoliosService {

  constructor(
    private http: HttpClient
  ) {
  }

  public getList(): Observable<Portfolio[]> {
    return this.http.get<Portfolio[]>(API.PORTFOLIOS);
  }

  public getOneByUid(uid: string): Observable<Portfolio> {
    return this.http.get<Portfolio>(`${API.PORTFOLIOS}/${uid}`);
  }

  public getContentByUid(uid: string, params?: { [param: string]: string }): Observable<PortfolioContent> {
    return this.http.get<PortfolioContent>(`${API.PORTFOLIOS}/${uid}/content`, {params});
  }

  public getAnalyticsByUid(uid: string, params?: { [param: string]: string }): Observable<PortfolioAnalytics> {
    return this.http.get<PortfolioAnalytics>(`${API.PORTFOLIOS}/${uid}/analytics`, {params});
  }

  public deletePortfolioItem(itemUid: string): Observable<Position> {
    return this.http.delete<Position>(`${API.PORTFOLIO_ITEMS}/${itemUid}`);
  }

  public changePortfolioItemTicker(itemUid: string, ticker: string, exchange?: string): Observable<Position> {
    return this.http.patch<Position>(`${API.PORTFOLIO_ITEMS}/${itemUid}/ticker`, exchange ? {ticker, exchange} : {ticker});
  }

  public create(portfolio: Portfolio, sender): Observable<Portfolio> {
    return this.http.post<Portfolio>(`${API.PORTFOLIOS}`, portfolio)
      .pipe(
        catchError((err) => {
          sender.showLoader = false;
          return throwError(err);
        })
      );
  }

  public update(portfolio: Portfolio): Observable<Portfolio> {
    return this.http.patch<Portfolio>(`${API.PORTFOLIOS}/${portfolio.uid}`, portfolio);
  }

  public delete(uid: string): Observable<Portfolio> {
    return this.http.delete<Portfolio>(`${API.PORTFOLIOS}/${uid}`);
  }

  public import(portfolioUid: string, parser: string, formData: FormData): Observable<PortfolioImportResponse> {
    return this.http.post<PortfolioImportResponse>(`${API.PORTFOLIOS}/${portfolioUid}/import/${parser}`, formData);
  }

  public clone(portfolioUid: string): Observable<Portfolio> {
    return this.http.post<Portfolio>(`${API.PORTFOLIOS}/${portfolioUid}/clone`, {});
  }
}
