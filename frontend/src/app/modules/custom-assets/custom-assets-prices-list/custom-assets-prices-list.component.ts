import { tuiScrollbarOptionsProvider } from '@taiga-ui/core';
import { DatePipe } from '@angular/common';
import { Component, HostBinding, Input, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { CustomAssetPrice } from '../../../core/interfaces/custom_assets.interfaces';
import { CommonService } from '../../../core/services/common.service';


@Component({
    selector: 'app-custom-assets-prices-list',
    templateUrl: './custom-assets-prices-list.component.html',
    styleUrls: ['./custom-assets-prices-list.component.scss'],
    providers: [tuiScrollbarOptionsProvider({mode: 'hidden'})],
    standalone: false
})
export class CustomAssetsPricesListComponent implements OnInit {
  // @HostBinding('class') classes = '';
  @Input() customAssetsPrices: CustomAssetPrice[] = [];

  public columns = ['created_at', 'price'];

  constructor(
    // private customAssetsService: CustomAssetsService,
    public commonService: CommonService,
    public route: ActivatedRoute,
  ) {
  }

  ngOnInit(): void {
  }

}
