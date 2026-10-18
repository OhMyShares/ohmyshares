import { HttpClient, HttpHeaders } from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, timer} from 'rxjs';
import {switchMap, takeWhile} from 'rxjs/operators';
import {environment} from '../../../environments/environment';
import {CatalogItem, Plugin, PluginInstallation} from '../interfaces/plugin.interface';

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

  install(remote: string): Observable<PluginInstallation> {
    return this.http.post<PluginInstallation>(this.apiUrl, {remote}, {headers: new HttpHeaders({noSpinner: 'true'})});
  }

  upgrade(id: number): Observable<PluginInstallation> {
    return this.http.post<PluginInstallation>(`${this.apiUrl}/${id}/upgrade`, null, {headers: new HttpHeaders({noSpinner: 'true'})});
  }

  getInstallation(id: string): Observable<PluginInstallation> {
    return this.http.get<PluginInstallation>(`${this.apiUrl}/installations/${id}`, {headers: new HttpHeaders({noSpinner: 'true'})});
  }

  pollInstallation(id: string): Observable<PluginInstallation> {
    return timer(1000, 2000).pipe(
      switchMap(() => this.getInstallation(id)),
      takeWhile(installation => installation.status === 'pending' || installation.status === 'running', true),
    );
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
