import { Component, Inject } from '@angular/core';
// import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { CommonService } from '../../../../core/services/common.service';
import { TradeListComponent } from '../trade-list.component';
import {TuiDialogContext, TuiDialogService} from '@taiga-ui/core';
import {POLYMORPHEUS_CONTEXT} from '@tinkoff/ng-polymorpheus';
import {Common} from '../../../../core/services/references.service';


export interface FilterDialogData {
  opener: TradeListComponent;
}


@Component({
  selector: 'app-filter-dialog',
  templateUrl: 'filter-dialog.component.html',
  styleUrls: [ 'filter-dialog.component.scss' ]
})
export class FilterDialogComponent {

  public data: FilterDialogData;
  public tradeTypesFiltered: Common[];

  constructor(
    public commonService: CommonService,
    @Inject(TuiDialogService) private readonly dialogService: TuiDialogService,
    @Inject(POLYMORPHEUS_CONTEXT) private readonly context: TuiDialogContext,
  ) {
    this.data = context.data;
    this.tradeTypesFiltered = this.data.opener.tradeTypes;
  }

  assetTypeFilterChanged(assetTypeSelected: Common) {
    this.data.opener.search({asset: assetTypeSelected.id.toString()});
    this.tradeTypesFiltered = this.data.opener.tradeTypes.filter(tradeType => tradeType.linked_asset.includes(assetTypeSelected.id));
  }

  tradeTypeFilterChanged(tradeTypeSelected: Common) {
    this.data.opener.search({type: tradeTypeSelected.id.toString()});
  }

  onCloseClick(): void {
    // this.dialogRef.close();
  }

}
