import { TuiNotificationService } from "@taiga-ui/core";
import { Component, HostBinding, OnInit } from '@angular/core';
import {UntypedFormBuilder, UntypedFormControl, UntypedFormGroup, Validators} from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import * as moment from 'moment';
import { FormField } from '../../../core/interfaces/common.interfaces';
import { Portfolio } from '../../../core/interfaces/portfolios.interfaces';
import { CommonService } from '../../../core/services/common.service';
import { PortfoliosService } from '../../../core/services/portfolios.service';
import {Common, CurrencyReference, ReferencesService} from '../../../core/services/references.service';
import { CURRENT_PORTFOLIO_KEY } from '../../../core/constants';
import {TuiDay} from '@taiga-ui/cdk';

@Component({
    selector: 'app-portfolio-card',
    templateUrl: './portfolio-card.component.html',
    styleUrls: ['./portfolio-card.component.scss'],
    standalone: false
})
export class PortfolioCardComponent implements OnInit {
  // @HostBinding('class') classes = 'tui-space_bottom-5';

  public editForm = new UntypedFormGroup({
    name: new UntypedFormControl('', [
      Validators.required
    ]),
    currency_code: new UntypedFormControl('', [
      Validators.required
    ]),
    account_type: new UntypedFormControl('', [
      Validators.required
    ]),
    opened_at: new UntypedFormControl(''),
    fee_percent: new UntypedFormControl('', [
      Validators.pattern('^[0-9]+((\\.|\\,)[0-9]{1,5})?$'),
    ]),
    goal: new UntypedFormControl('', [
      Validators.pattern('^[0-9]+((\\.|\\,)[0-9]{1,5})?$'),
    ]),
    notes: new UntypedFormControl(''),
  });

  public portfolio: Portfolio;
  public currencies: CurrencyReference[];
  public currencyCodes: string[];
  public accountTypes: Common[];
  public showLoader = false;

  constructor(
    public referencesService: ReferencesService,
    private portfoliosService: PortfoliosService,
    private route: ActivatedRoute,
    private router: Router,
    private alertService: TuiNotificationService,
    public commonService: CommonService,
    private formBuilder: UntypedFormBuilder
  ) {
  }

  ngOnInit() {
    this.route.data
      .subscribe(({ data }) => {
        const [ portfolio, currencies, accountTypes ] = data;
        this.portfolio = portfolio;
        this.currencies = currencies;
        this.currencyCodes = currencies.map((item) => item.code);
        this.accountTypes = accountTypes;
        if (this.portfolio.uid) {
          this.editForm = this.formBuilder.group(portfolio);
        } else {
          this.editForm.patchValue({
            account_type: accountTypes[0].id,
            goal: '0',
          });
        }
        const openedAt = moment.unix(portfolio.opened_at || moment().unix());
        this.editForm.patchValue({
          opened_at: new TuiDay(openedAt.year(), openedAt.month(), openedAt.date())
        });
      });
  }

  save() {
    if (this.editForm.invalid) {
      return;
    }
    const data = Object.assign({}, this.portfolio, this.editForm.value);
    data.opened_at = this.editForm.value.opened_at.toUtcNativeDate().getTime() / 1000;
    if (data.uid) {
      this.showLoader = true;
      this.portfoliosService.update(data)
        .subscribe(() => {
          this.showLoader = false;
          this.alertService.open($localize`:@@alert.portfolio.update.message:The portfolio has been successfully updated`, {
            appearance: 'positive'
          }).subscribe();
          history.back();
        });
    } else {
      this.showLoader = true;
      this.portfoliosService.create(data, this)
        .subscribe(() => {
          this.showLoader = false;
          this.alertService.open($localize`:@@alert.portfolio.create.message:The portfolio has been successfully created`, {
            appearance: 'positive'
          }).subscribe();
          history.back();
        });
    }
  }

  delete() {
    this.portfoliosService.delete(this.portfolio.uid)
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.portfolio.delete.message:The portfolio has been successfully deleted`, {
          appearance: 'positive'
        }).subscribe();
        localStorage.setItem(CURRENT_PORTFOLIO_KEY, null);
        this.router.navigate([ '/portfolios' ]);
      });
  }

}
