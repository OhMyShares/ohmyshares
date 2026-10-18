import { formatNumber } from '@angular/common';
import { Inject, LOCALE_ID, Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

@Pipe({
    name: 'numberColored',
    standalone: false
})
export class numberColoredPipe implements PipeTransform {
  constructor(
    private sanitized: DomSanitizer,
    @Inject(LOCALE_ID) private locale: string,
  ) {
  }

  transform(value) {
    const colorCss = value > 0
      ? 'text-success'
      : value < 0
        ? 'text-danger'
        : 'text-regular';
    return this.sanitized.bypassSecurityTrustHtml(`<span class="${ colorCss }">${ this.format(value) }</span>`);
  }

  private format(value): string {
    const number = Number(value);
    try {
      return formatNumber(number, this.locale);
    } catch {
      return number.toLocaleString();
    }
  }
}
