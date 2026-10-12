import {DatePipe} from '@angular/common';
import {Component, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {CustomAsset, CustomAssetPrice} from '../../../core/interfaces/custom_assets.interfaces';
import {CustomAssetsService} from '../../../core/services/custom_assets.service';

@Component({
    selector: 'app-custom-asset-view',
    templateUrl: './custom-asset-view.component.html',
    styleUrls: ['./custom-asset-view.component.scss'],
    providers: [DatePipe],
    standalone: false
})
export class CustomAssetViewComponent implements OnInit {

  readonly stringify = String;
  public customAsset: CustomAsset;
  public chartData = [];
  public chartXMax = 1;
  public chartYMax = 1;
  public customAssetsPrices: CustomAssetPrice[];

  constructor(
    private customAssetsService: CustomAssetsService,
    private route: ActivatedRoute,
  ) {
  }

  ngOnInit() {
    const {data} = this.route.snapshot.data;
    const [customAsset, customAssetsPrices] = data;
    this.customAsset = customAsset;
    this.customAssetsPrices = customAssetsPrices;
    this.processChartData(customAssetsPrices);
    console.log(this.chartXMax, this.chartYMax);
  }

  processChartData(customAssetsPrices) {
    this.chartData = customAssetsPrices
      .reverse()
      .map((item, index) => {
        if (index > this.chartXMax) {
          this.chartXMax = index;
        }
        const price = +item.price;
        if (price > this.chartYMax) {
          this.chartYMax = price;
        }
        return [index, price];
      });
  }

}
