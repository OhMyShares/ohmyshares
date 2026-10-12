import { TuiNotificationService } from "@taiga-ui/core";
import { Component, HostBinding, OnInit } from '@angular/core';
import {UntypedFormBuilder, UntypedFormControl, UntypedFormGroup, Validators} from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import * as moment from 'moment';
import { FormField } from '../../../core/interfaces/common.interfaces';
import { CustomAsset, CustomAssetPrice } from '../../../core/interfaces/custom_assets.interfaces';
import { CommonService } from '../../../core/services/common.service';
import { CustomAssetsService } from '../../../core/services/custom_assets.service';
import {TuiDay} from '@taiga-ui/cdk';

@Component({
    selector: 'app-custom-asset-price-card',
    templateUrl: './custom-asset-price-card.component.html',
    styleUrls: ['./custom-asset-price-card.component.scss'],
    standalone: false
})
export class CustomAssetPriceCardComponent implements OnInit {
  // @HostBinding('class') classes = '';

  public editForm = new UntypedFormGroup({
    created_at: new UntypedFormControl('', [
      Validators.required
    ]),
    price: new UntypedFormControl('', [
      Validators.required
    ]),
  });

  public customAssetPrice: CustomAssetPrice;
  public customAssets: CustomAsset[];
  public showLoader = false;

  constructor(
    private customAssetsService: CustomAssetsService,
    private route: ActivatedRoute,
    private router: Router,
    private alertService: TuiNotificationService,
    public commonService: CommonService,
    private formBuilder: UntypedFormBuilder
  ) {
  }

  ngOnInit() {
    const { data } = this.route.snapshot.data;
    const [ customAssetPrice, customAssets ] = data;
    this.customAssetPrice = customAssetPrice;
    this.customAssets = customAssets;
    if (this.customAssetPrice.id) {
      this.editForm = this.formBuilder.group(this.customAssetPrice);
    } else {
      // default values
      this.customAssetPrice.custom_asset_id = +this.route.snapshot.paramMap.get('id');
      this.editForm.patchValue({});
    }
    const createdAt = moment.unix(customAssetPrice.created_at || moment().unix());
    this.editForm.patchValue({
      created_at: new TuiDay(createdAt.year(), createdAt.month(), createdAt.date())
    });
  }

  save() {
    if (this.editForm.invalid) {
      return;
    }
    const data = Object.assign({}, this.customAssetPrice, this.editForm.value);
    data.created_at = this.editForm.value.created_at.toUtcNativeDate().getTime() / 1000;
    if (data.id) {
      this.customAssetsService.updatePrice(data)
        .subscribe(() => {
          this.alertService.open($localize`:@@alert.customAsset.update.message:Custom asset has been successfully updated`, {
            appearance: 'positive'
          }).subscribe();
          history.back();
        });
    } else {
      this.customAssetsService.createPrice(data)
        .subscribe(() => {
          this.alertService.open($localize`:@@alert.customAsset.create.message:Price has been successfully added`, {
            appearance: 'positive'
          }).subscribe();
          history.back();
        });
    }
  }

  delete() {
    this.customAssetsService.deletePrice(this.customAssetPrice.id)
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.customAsset.delete.message:Price has been successfully deleted`, {
          appearance: 'positive'
        }).subscribe();
        this.router.navigate(['/custom-asset-price', this.customAssetPrice.id]);
      });
  }

  getControl(field: string): UntypedFormControl {
    return this.editForm.controls[field] as UntypedFormControl;
  }

}
