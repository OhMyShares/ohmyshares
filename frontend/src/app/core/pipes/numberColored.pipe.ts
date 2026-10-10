import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

@Pipe({ name: 'numberColored' })
export class numberColoredPipe implements PipeTransform {
  constructor(private sanitized: DomSanitizer) {
  }

  transform(value) {
    const colorCss = value > 0
      ? 'text-success'
      : value < 0
        ? 'text-danger'
        : 'text-regular';
    return this.sanitized.bypassSecurityTrustHtml(`<span class="${ colorCss }">${ Number(value).toLocaleString() }</span>`);
  }
}
