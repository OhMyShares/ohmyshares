import { DatePipe } from '@angular/common';
import { Component, HostBinding, OnInit } from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import { Portfolio } from '../../../core/interfaces/portfolios.interfaces';
import { AuthService } from '../../../core/services/auth.service';
import { CommonService } from '../../../core/services/common.service';
import {UntypedFormControl} from '@angular/forms';
import {debounceTime} from 'rxjs/operators';
import {Ticker} from '../../../core/interfaces/tickers.interfaces';
import {TickersService} from '../../../core/services/tickers.service';

@Component({
  selector: 'app-company-list',
  templateUrl: './company-list.component.html',
  styleUrls: ['./company-list.component.scss'],
  providers: [DatePipe]
})
export class CompanyListComponent implements OnInit {
  // @HostBinding('class') classes = '';

  public companies: Ticker[];
  public displayedColumns = [
    'ticker', 'name',
  ];
  public skeletonVisible = true;
  public searchField: UntypedFormControl;

  constructor(
    public commonService: CommonService,
    public authService: AuthService,
    public tickersService: TickersService,
    private route: ActivatedRoute,
    private router: Router,
  ) {
    this.searchField = new UntypedFormControl();
    this.searchField.setValue(this.route.snapshot.queryParamMap.get('search'));
    this.searchField.valueChanges.pipe(debounceTime(800))
      .subscribe(value => {
        this.router.navigate(['/companies'], {queryParams: {search: value}});
        // this.skeletonVisible = false;
        this.search();
      });
  }

  ngOnInit() {
    this.search();
  }

  search() {
    const search = this.searchField.value;
    const params = search ? {q: search} : null;
    this.tickersService.getList(params).subscribe((data) => {
      this.companies = data;
      this.skeletonVisible = false;
    });
  }
}
