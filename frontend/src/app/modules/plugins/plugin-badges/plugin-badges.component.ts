import {ChangeDetectionStrategy, Component, Input} from '@angular/core';

@Component({
    selector: 'app-plugin-badges',
    templateUrl: './plugin-badges.component.html',
    styleUrls: ['./plugin-badges.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    standalone: false
})
export class PluginBadgesComponent {
  @Input() version: string | null = null;
  @Input() approved = false;
  @Input() popular = false;
  @Input() stars: number | null = null;

  readonly approvedHint = $localize`:@@plugins.badge.approvedHint:Official plugin from github.com/OhMyShares`;
  readonly approvedLabel = $localize`:@@plugins.badge.approved:Approved`;
  readonly popularLabel = $localize`:@@plugins.badge.popular:Popular`;

  get popularHint(): string {
    return $localize`:@@plugins.badge.popularHint:${this.stars ?? ''}:stars: stars on GitHub`;
  }
}
