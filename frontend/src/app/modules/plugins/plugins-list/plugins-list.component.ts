import {Component, Injector, OnInit} from '@angular/core';
import {FormControl, Validators} from '@angular/forms';
import {Router} from '@angular/router';
import { TuiDialogService, TuiNotificationService } from '@taiga-ui/core';
import {PolymorpheusComponent} from '@taiga-ui/polymorpheus';
import {CatalogItem, Plugin} from '../../../core/interfaces/plugin.interface';
import {PLUGINS_OFFICIAL_URL, PluginsService} from '../../../core/services/plugins.service';
import {ConfirmDialogComponent, ConfirmDialogData} from '../../confirm-dialog/confirm-dialog.component';

@Component({
    selector: 'app-plugins-list',
    templateUrl: './plugins-list.component.html',
    styleUrls: ['./plugins-list.component.scss'],
    standalone: false
})
export class PluginsListComponent implements OnInit {
  activeTab = 0;
  plugins: Plugin[] = [];
  catalog: CatalogItem[] = [];
  catalogLoaded = false;
  isLoading = false;
  isCatalogLoading = false;
  installing: string | null = null;

  readonly remoteControl = new FormControl('', [Validators.required, Validators.pattern(/^(https?:\/\/\S+|\/\S+)$/)]);

  constructor(
    private pluginsService: PluginsService,
    private alertService: TuiNotificationService,
    private dialogService: TuiDialogService,
    private injector: Injector,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.loadPlugins();
  }

  onTabChange(index: number): void {
    this.activeTab = index;
    if (index === 1 && !this.catalogLoaded) {
      this.loadCatalog();
    }
  }

  loadPlugins(): void {
    this.isLoading = true;
    this.pluginsService.getPlugins().subscribe({
      next: plugins => {
        this.plugins = plugins;
        this.isLoading = false;
      },
      error: () => this.isLoading = false,
    });
  }

  loadCatalog(refresh = false): void {
    this.isCatalogLoading = true;
    this.pluginsService.getCatalog(refresh).subscribe({
      next: catalog => {
        this.catalog = catalog;
        this.catalogLoaded = true;
        this.isCatalogLoading = false;
      },
      error: () => this.isCatalogLoading = false,
    });
  }

  installManual(): void {
    if (this.remoteControl.invalid) {
      return;
    }
    const remote = this.remoteControl.value.trim();
    this.confirmInstall(remote, this.pluginsService.isApprovedRemote(remote), () => this.remoteControl.reset(''));
  }

  installFromCatalog(item: CatalogItem): void {
    this.confirmInstall(item.url, item.is_approved);
  }

  private confirmInstall(remote: string, isApproved: boolean, onSuccess?: () => void): void {
    if (isApproved) {
      this.install(remote, onSuccess);
      return;
    }
    const data: ConfirmDialogData = {
      content: $localize`:@@plugins.security.warning:This plugin is not from the official repository ${PLUGINS_OFFICIAL_URL}:url:. Plugins run arbitrary code on your server and get full access to your data. Install it only if you trust the author and have reviewed the source code: ${remote}:remote:`,
      button: {
        action: $localize`:@@plugins.security.installAnyway:Install anyway`,
        close: $localize`:@@plugins.security.cancel:Cancel`,
      },
    };
    this.dialogService.open<ConfirmDialogData | null>(
      new PolymorpheusComponent(ConfirmDialogComponent, this.injector),
      {
        dismissible: true,
        label: $localize`:@@plugins.security.title:Security warning`,
        data,
      },
    ).subscribe(result => {
      if (result !== null) {
        this.install(remote, onSuccess);
      }
    });
  }

  private install(remote: string, onSuccess?: () => void): void {
    this.installing = remote;
    this.pluginsService.install(remote).subscribe({
      next: installation => this.trackInstallation(installation.id, onSuccess),
      error: () => this.installing = null,
    });
  }

  private trackInstallation(id: string, onSuccess?: () => void): void {
    this.pluginsService.pollInstallation(id).subscribe({
      next: installation => {
        if (installation.status === 'pending' || installation.status === 'running') {
          return;
        }
        this.installing = null;
        if (installation.status === 'failed') {
          this.alertService.open(
            $localize`:@@alert.plugins.installFailed:Plugin installation failed: ${installation.error}:error:`,
            {appearance: 'negative'},
          ).subscribe();
          return;
        }
        this.alertService.open(
          $localize`:@@alert.plugins.installed:Plugin "${installation.title}:title:" installed`,
          {appearance: 'positive'},
        ).subscribe();
        if (onSuccess) {
          onSuccess();
        }
        this.loadPlugins();
        if (this.catalogLoaded) {
          this.loadCatalog();
        }
        if (installation.plugin_id) {
          this.router.navigate(['/plugins', installation.plugin_id]);
        }
      },
      error: () => this.installing = null,
    });
  }

  toggle(plugin: Plugin): void {
    this.pluginsService.setEnabled(plugin.id, !plugin.is_enabled).subscribe(updated => {
      Object.assign(plugin, updated);
    });
  }

  isInstalling(remote: string): boolean {
    return this.installing === remote;
  }
}
