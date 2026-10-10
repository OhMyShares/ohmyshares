import { Component, HostBinding, OnInit } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { TableColumn } from '../../../core/interfaces/common.interfaces';
import { CustomAsset } from '../../../core/interfaces/custom_assets.interfaces';
import { CommonService } from '../../../core/services/common.service';
import { CustomAssetsService } from '../../../core/services/custom_assets.service';


@Component({
  selector: 'app-custom-assets-list',
  templateUrl: './custom-assets-list.component.html',
  styleUrls: ['./custom-assets-list.component.scss']
})
export class CustomAssetsListComponent implements OnInit {
  // @HostBinding('class') classes = '';

  public customAssets: CustomAsset[];
  public columns = ['ticker', 'name', 'price'];
  public skeletonVisible = true;
  public customAssets$: BehaviorSubject<CustomAsset[]> = new BehaviorSubject([]);

  constructor(
    private customAssetsService: CustomAssetsService,
    public commonService: CommonService
  ) {
  }

  ngOnInit(): void {
    this.skeletonVisible = true;
    this.customAssetsService.getList()
      .subscribe((customAssets) => {
        this.customAssets$.next(customAssets);
        this.customAssets = customAssets;
        this.skeletonVisible = false;
      });
  }

}
