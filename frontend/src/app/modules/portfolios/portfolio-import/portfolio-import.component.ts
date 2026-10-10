import { Component, HostBinding, OnInit } from '@angular/core';
import {FormControl, FormGroup, UntypedFormControl, UntypedFormGroup, Validators} from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { environment } from '../../../../environments/environment';
// import { AlertTypes } from '../../../core/interfaces/alert.interfaces';
import { FormField } from '../../../core/interfaces/common.interfaces';
// import { AlertService } from '../../../core/services/alert.service';
import { CommonService } from '../../../core/services/common.service';
import { PortfoliosService } from '../../../core/services/portfolios.service';
import {Common, ReferencesService} from '../../../core/services/references.service';
import {TuiAlertService, TuiNotification} from "@taiga-ui/core";
import {Observable, of, Subject, timer} from "rxjs";
import {TuiFileLike} from "@taiga-ui/kit";
import {finalize, map, switchMap} from "rxjs/operators";
import {TuiContextWithImplicit, tuiPure, TuiStringHandler} from "@taiga-ui/cdk";
import {toFormData} from "axios";

@Component({
  selector: 'app-portfolio-import',
  templateUrl: './portfolio-import.component.html',
  styleUrls: ['./portfolio-import.component.scss']
})
export class PortfolioImportComponent implements OnInit {
  @HostBinding('class') classes = 'row justify-content-center align-items-center';

  public importForm = new UntypedFormGroup({
    parser: new UntypedFormControl('', [
      Validators.required
    ]),
    file: new UntypedFormControl('', [
      Validators.required
    ]),
  });

  public portfolioId: string;
  public parsers: Common[];
  public env: any;

  readonly rejectedFiles$ = new Subject<TuiFileLike | null>();
  readonly loadingFiles$ = new Subject<TuiFileLike | null>();
  readonly loadedFiles$ = this.importForm.controls['file'].valueChanges.pipe(
    switchMap(file => (file ? this.makeRequest(file) : of(null))),
  );

  constructor(
    private portfoliosService: PortfoliosService,
    private route: ActivatedRoute,
    private router: Router,
    private alertService: TuiAlertService,
    public commonService: CommonService,
    public referencesService: ReferencesService,
  ) {
  }

  ngOnInit() {
    this.env = environment;
    this.route.data
      .subscribe(({data}) => {
        const [portfolioId, parsers] = data;
        this.portfolioId = portfolioId;
        this.parsers = parsers;
      });
  }

  submit() {
    if (this.importForm.invalid) {
      return;
    }
    const formData = new FormData();
    formData.append('report', this.importForm.value.file);
    this.portfoliosService.import(this.portfolioId, this.importForm.get('parser').value, formData)
      .subscribe((res) => {
        this.alertService.open($localize`:@@alert.portfolio.import.message:The data has been successfully uploaded`, {
          status: TuiNotification.Success
        }).subscribe();
        history.back();
      });
  }

  onReject(file: TuiFileLike | readonly TuiFileLike[]): void {
    this.rejectedFiles$.next(file as TuiFileLike);
  }

  removeFile(): void {
    this.importForm.controls['file'].setValue(null);
  }

  clearRejected(): void {
    this.removeFile();
    this.rejectedFiles$.next(null);
  }

  makeRequest(file: TuiFileLike): Observable<TuiFileLike | null> {
    this.loadingFiles$.next(file);
    return timer(1000).pipe(
      map(() => {
          return file;
      }),
      finalize(() => this.loadingFiles$.next(null)),
    );
  }

  @tuiPure
  stringify(items: readonly Common[]): TuiStringHandler<TuiContextWithImplicit<number>> {
    const map = new Map(items.map(({id, title}) => [id, title] as [number, string]));
    return ({$implicit}: TuiContextWithImplicit<number>) => map.get($implicit) || ``;
  }

}
