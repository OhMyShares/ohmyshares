import {Component, HostBinding, OnInit} from '@angular/core';
import {UntypedFormBuilder, UntypedFormControl, UntypedFormGroup, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {CustomAsset} from '../../../core/interfaces/custom_assets.interfaces';
import {CommonService} from '../../../core/services/common.service';
import {CustomAssetsService} from '../../../core/services/custom_assets.service';
import {CurrencyReference, ReferencesService} from '../../../core/services/references.service';
import {TuiAlertService, TuiNotification} from '@taiga-ui/core';

@Component({
  selector: 'app-custom-asset-card',
  templateUrl: './custom-asset-card.component.html',
  styleUrls: [ './custom-asset-card.component.scss' ]
})
export class CustomAssetCardComponent implements OnInit {
  // @HostBinding('class') classes = '';

  public editForm = new UntypedFormGroup({
    ticker: new UntypedFormControl('', [
      Validators.required
    ]),
    name: new UntypedFormControl('', [
      Validators.required
    ]),
    currency_code: new UntypedFormControl('', [
      Validators.required
    ]),
    info: new UntypedFormControl(''),
  });

  public customAsset: CustomAsset;
  public currencies: CurrencyReference[];
  public currencyCodes: string[];
  public showLoader = false;

  constructor(
    public referencesService: ReferencesService,
    private customAssetsService: CustomAssetsService,
    private route: ActivatedRoute,
    private router: Router,
    private alertService: TuiAlertService,
    public commonService: CommonService,
    private formBuilder: UntypedFormBuilder
  ) {
  }

  ngOnInit() {
    const { data } = this.route.snapshot.data;
    const [ customAsset, currencies ] = data;
    this.customAsset = customAsset;
    this.currencies = currencies;
    this.currencyCodes = currencies.map((item) => item.code);
    if (this.customAsset.id) {
      this.editForm = this.formBuilder.group(this.customAsset);
    } else {
      // default values
      this.editForm.patchValue({});
    }
  }

  save() {
    if (this.editForm.invalid) {
      return;
    }
    const data = Object.assign({}, this.customAsset, this.editForm.value);
    if (data.id) {
      this.customAssetsService.update(data)
        .subscribe(() => {
          this.alertService.open($localize`:@@alert.customAsset.update.message:Custom asset has been successfully updated`, {
            status: TuiNotification.Success
          }).subscribe();
          history.back();
        });
    } else {
      this.customAssetsService.create(data)
        .subscribe(() => {
          this.alertService.open($localize`:@@alert.customAsset.create.message:Custom asset has been successfully created`, {
            status: TuiNotification.Success
          }).subscribe();
          history.back();
        });
    }
  }

  delete() {
    this.customAssetsService.delete(this.customAsset.id)
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.customAsset.delete.message:Custom asset has been successfully deleted`, {
          status: TuiNotification.Success
        }).subscribe();
        this.router.navigate(['/custom-assets']);
      });
  }

}
