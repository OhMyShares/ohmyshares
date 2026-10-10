import {HttpClient, HttpHeaders, HttpParams} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {environment} from '../../../environments/environment';
import {
  CurrencyRatesParams,
  CurrencyRatesResponse,
  CurrencyRatesUpdateResponse
} from '../interfaces/currency-rate.interface';

@Injectable({
  providedIn: 'root'
})
export class CurrencyRatesService {
  private apiUrl = `${environment.API_ENDPOINT}/currency-rates`;

  constructor(private http: HttpClient) {}

  getRates(params: CurrencyRatesParams): Observable<CurrencyRatesResponse> {
    let httpParams = new HttpParams()
      .set('limit', params.limit)
      .set('offset', params.offset);
    if (params.currency) {
      httpParams = httpParams.set('currency', params.currency);
    }
    return this.http.get<CurrencyRatesResponse>(this.apiUrl, {params: httpParams});
  }

  update(): Observable<CurrencyRatesUpdateResponse> {
    return this.http.post<CurrencyRatesUpdateResponse>(`${this.apiUrl}/update`, null, {headers: new HttpHeaders({noSpinner: 'true'})});
  }
}
