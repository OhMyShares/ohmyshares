import { Component, HostBinding, Input, OnInit } from '@angular/core';
import { Position, Portfolio } from '../../../core/interfaces/portfolios.interfaces';
import { CommonService } from '../../../core/services/common.service';

@Component({
  selector: 'app-item-summary',
  templateUrl: './item-summary.component.html',
  styleUrls: ['./item-summary.component.scss']
})
export class ItemSummaryComponent implements OnInit {
  // @HostBinding('class') classes = '';
  @Input() item: Position;

  constructor(
    public commonService: CommonService,
  ) { }

  ngOnInit(): void {
  }

}
