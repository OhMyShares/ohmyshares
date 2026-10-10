import { Injectable } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { Subject } from 'rxjs';
import { FormField, ValidationError } from '../interfaces/common.interfaces';
import { Router } from '@angular/router';

declare global {
  interface Window {
    OMSApp: any;
  }
}

@Injectable({
  providedIn: 'root'
})
export class CommonService {

  public validationError$: Subject<ValidationError> = new Subject();
  public isMobile: boolean = /iphone|iPad|android/i.test(navigator.userAgent);
  public isIOS: boolean = /iphone|iPad/i.test(navigator.userAgent);

  // Detects if device is in standalone mode
  public isInStandaloneMode = () => (window.matchMedia('(display-mode: standalone)').matches);

  public get OMSApp() {
    return window.OMSApp;
  }

  constructor(
    private router: Router,
  ) {
  }

  public reloadRoute() {
    this.router.navigate([]);
  }

  public reloadForce() {
    window.location.reload();
  }

  public prepareParams(params: { [param: string]: string }): { [param: string]: string } {
    return Object.entries(params)
      .filter((item) => !!item[1]).reduce((obj, item) => Object.assign(obj, { [item[0]]: item[1] }), {});
  }

  // public selectOne(arr: any[], id = 'id'): number {
  //   return arr.length === 1 && arr[0][id] || null;
  // }

  // public getFormValues<T>(form: FormGroup, formFields: FormField[]): Partial<T> {
  //   return Object.keys(form.controls)
  //     .reduce((obj, key) => [ null, '', undefined ].includes(form.controls[key].value)
  //       ? obj
  //       : Object.assign(obj, { [key]: this.getFormFieldValue(formFields, key, form.controls[key].value) }), {});
  // }

  // private getFormFieldValue(formFields, key, value): any {
  //   const field = formFields.find((item) => item.name === key);
  //   return field && field.get
  //     ? field.get(value)
  //     : value;
  // }

  // public unixtimeToIsoDate(timestamp) {
  //   return new Date(timestamp * 1e3).toISOString().slice(0, 10);
  // }
}
