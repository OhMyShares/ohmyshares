import { TuiNotificationService } from "@taiga-ui/core";
import {Component, OnInit} from '@angular/core';
import {FormControl, FormGroup, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {switchMap} from 'rxjs/operators';
import {Plugin, PluginConfigField} from '../../../core/interfaces/plugin.interface';
import {PluginsService} from '../../../core/services/plugins.service';

interface ConfigFieldView {
  key: string;
  field: PluginConfigField;
}

@Component({
    selector: 'app-plugin-card',
    templateUrl: './plugin-card.component.html',
    styleUrls: ['./plugin-card.component.scss'],
    standalone: false
})
export class PluginCardComponent implements OnInit {
  plugin: Plugin | null = null;
  fields: ConfigFieldView[] = [];
  configForm = new FormGroup({});
  rawConfig = new FormControl('');
  isUpgrading = false;
  isSaving = false;
  isRemoving = false;

  readonly removeTitle = $localize`:@@plugins.confirm.uninstall:Uninstall plugin?`;
  readonly enabledLabel = $localize`:@@plugins.status.enabled:Enabled`;
  readonly disabledLabel = $localize`:@@plugins.status.disabled:Disabled`;
  readonly invalidJsonLabel = $localize`:@@plugins.config.invalidJson:Invalid JSON object`;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private pluginsService: PluginsService,
    private alertService: TuiNotificationService,
  ) {}

  ngOnInit(): void {
    this.route.paramMap.pipe(
      switchMap(params => this.pluginsService.getPlugin(+params.get('id')))
    ).subscribe(plugin => this.setPlugin(plugin));
  }

  get hasSchema(): boolean {
    return this.fields.length > 0;
  }

  private setPlugin(plugin: Plugin): void {
    this.plugin = plugin;
    const schema = plugin.config_schema || {};
    this.fields = Object.keys(schema).map(key => ({key, field: schema[key]}));
    const config = plugin.config || {};

    const controls: Record<string, FormControl> = {};
    for (const {key, field} of this.fields) {
      const value = config[key] !== undefined ? config[key] : (field.default !== undefined ? field.default : (field.type === 'boolean' ? false : null));
      controls[key] = new FormControl(value, field.required ? [Validators.required] : []);
    }
    this.configForm = new FormGroup(controls);
    this.rawConfig = new FormControl(JSON.stringify(config, null, 2), [this.jsonValidator]);
  }

  private jsonValidator(control: FormControl) {
    if (!control.value || !control.value.trim()) {
      return null;
    }
    try {
      const parsed = JSON.parse(control.value);
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? null : {json: true};
    } catch (e) {
      return {json: true};
    }
  }

  saveConfig(): void {
    let config: Record<string, any>;
    if (this.hasSchema) {
      if (this.configForm.invalid) {
        return;
      }
      config = this.configForm.value;
    } else {
      if (this.rawConfig.invalid) {
        return;
      }
      config = this.rawConfig.value.trim() ? JSON.parse(this.rawConfig.value) : {};
    }
    this.isSaving = true;
    this.pluginsService.saveConfig(this.plugin.id, config).subscribe({
      next: plugin => {
        this.isSaving = false;
        this.setPlugin(plugin);
        this.alertService.open($localize`:@@alert.plugins.configSaved:Configuration saved`, {appearance: 'positive'}).subscribe();
      },
      error: () => this.isSaving = false,
    });
  }

  toggle(): void {
    this.pluginsService.setEnabled(this.plugin.id, !this.plugin.is_enabled).subscribe(plugin => this.setPlugin(plugin));
  }

  upgrade(): void {
    this.isUpgrading = true;
    this.pluginsService.upgrade(this.plugin.id).subscribe({
      next: plugin => {
        this.isUpgrading = false;
        this.setPlugin(plugin);
        this.alertService.open($localize`:@@alert.plugins.upgraded:Plugin upgraded to ${plugin.version}:version:`, {appearance: 'positive'}).subscribe();
      },
      error: () => this.isUpgrading = false,
    });
  }

  uninstall(): void {
    this.isRemoving = true;
    this.pluginsService.uninstall(this.plugin.id).subscribe({
      next: () => {
        this.alertService.open($localize`:@@alert.plugins.uninstalled:Plugin uninstalled`, {appearance: 'positive'}).subscribe();
        this.router.navigate(['/plugins']);
      },
      error: () => this.isRemoving = false,
    });
  }
}
