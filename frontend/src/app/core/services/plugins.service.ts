import { HttpClient, HttpHeaders } from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {environment} from '../../../environments/environment';
import {CatalogItem, Plugin} from '../interfaces/plugin.interface';

export const PLUGINS_OFFICIAL_URL = 'https://github.com/OhMyShares/';

@Injectable({
  providedIn: 'root'
})
export class PluginsService {
  private apiUrl = `${environment.API_ENDPOINT}/plugins`;

  constructor(private http: HttpClient) {}

  isApprovedRemote(remote: string): boolean {
    const url = remote.trim();
    const prefix = PLUGINS_OFFICIAL_URL.toLowerCase();
    return url.toLowerCase().startsWith(prefix)
      && /^[^\/#\s]/.test(url.substring(prefix.length))
      && !url.includes('..');
  }

  getPlugins(): Observable<Plugin[]> {
    return this.http.get<Plugin[]>(this.apiUrl);
  }

  getPlugin(id: number): Observable<Plugin> {
    return this.http.get<Plugin>(`${this.apiUrl}/${id}`);
  }

  getCatalog(refresh = false): Observable<CatalogItem[]> {
    return this.http.get<CatalogItem[]>(`${this.apiUrl}/catalog`, {params: refresh ? {refresh: 1} : {}});
  }

  install(remote: string): Observable<Plugin> {
    return this.http.post<Plugin>(this.apiUrl, {remote}, {headers: new HttpHeaders({noSpinner: 'true'})});
  }

  upgrade(id: number): Observable<Plugin> {
    return this.http.post<Plugin>(`${this.apiUrl}/${id}/upgrade`, null, {headers: new HttpHeaders({noSpinner: 'true'})});
  }

  setEnabled(id: number, isEnabled: boolean): Observable<Plugin> {
    return this.http.patch<Plugin>(`${this.apiUrl}/${id}/enable`, {is_enabled: isEnabled});
  }

  saveConfig(id: number, config: Record<string, any>): Observable<Plugin> {
    return this.http.put<Plugin>(`${this.apiUrl}/${id}/config`, {config});
  }

  uninstall(id: number): Observable<{message: string}> {
    return this.http.delete<{message: string}>(`${this.apiUrl}/${id}`);
  }
}
