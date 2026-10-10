import {Component, OnInit} from '@angular/core';
import {Portfolio, PortfolioAnalytics, PortfolioAnalyticsData, Position} from '../../../core/interfaces/portfolios.interfaces';
import { CommonService } from '../../../core/services/common.service';
import {CURRENT_PORTFOLIO_KEY, PORTFOLIOS_LIST_KEY} from '../../../core/constants';
import {ActivatedRoute} from '@angular/router';
import {Common, REFERENCE, ReferencesService} from '../../../core/services/references.service';
import {PortfoliosService} from '../../../core/services/portfolios.service';
import {inflate} from 'zlib';
import {tuiFormatNumber} from '@taiga-ui/core';
import {TuiContextWithImplicit} from '@taiga-ui/cdk';
import {catchError} from 'rxjs/operators';
import {of} from 'rxjs';

@Component({
  selector: 'app-portfolio-analytics',
  templateUrl: './portfolio-analytics.component.html',
  styleUrls: ['./portfolio-analytics.component.scss']
})
export class PortfolioAnalyticsComponent implements OnInit {
  // @HostBinding('class') classes = 'row justify-content-center align-items-center';

  public analytics: PortfolioAnalyticsData;
  public portfolio: Portfolio;
  public portfolios: Portfolio[];
  public assetTypes: Common[];
  public content: Position[];

  public chartGrowth: any;
  public chartAssetTypes: any;
  public chartAssetTypesIndex = NaN;
  public chartSectors: any;
  public chartSectorsIndex = NaN;
  public chartEarnings: any;
  public chartEmitents: any;

  public skeletonVisible = true;

  public legendOptions = {
    position: 'bottom',
    horizontalAlign: 'center',
  };
  public plotOptions = {
    pie: {
      donut: {
        size: '60%',
      }
    }
  };

  public chartResponsiveSettings = [
    {
      breakpoint: 480,
      options: {
        legend: {
          position: 'bottom'
        },
        plotOptions: {
          pie: {
            donut: {
              size: '35%',
            }
          }
        }
      },
    }
  ];

  public earningsSum = 0;

  constructor(
    public commonService: CommonService,
    private portfoliosService: PortfoliosService,
    private referencesService: ReferencesService,
    private route: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.portfoliosService.getAnalyticsByUid(this.route.snapshot.params.uid)
      .pipe(catchError(() => {
        // this.router.navigate(['/portfolios']);
        return of({} as PortfolioAnalytics);
      }))
      .subscribe((data ) => {
        this.portfolio = data.portfolio;
        this.content = data.content;
        this.analytics = data.analytics;
        localStorage.setItem(CURRENT_PORTFOLIO_KEY, `${ this.portfolio.uid }`);
        this.portfolios = JSON.parse(localStorage.getItem(PORTFOLIOS_LIST_KEY));
        this.chartGrowth = this.processGrowthChart(this.analytics.growth);
        this.chartAssetTypes = this.processAssetTypesChart(this.analytics.asset_types);
        this.chartSectors = this.processSectorsChart(this.analytics.sectors);
        this.chartEarnings = this.processEarningsChart(this.analytics.earnings);
        this.chartEmitents = this.processEmitentsChart(this.content);
        this.skeletonVisible = false;
      });
    this.referencesService.getReference<Common[]>(REFERENCE.ASSET).subscribe((assetTypes) => {
      this.assetTypes = assetTypes;
    });
  }

  changeCurrency(currencyCode) {
    this.portfolio.currency_code = currencyCode;
    this.portfoliosService.update(this.portfolio)
      .subscribe(() => {
        // this.alertService.message$.next({
        //   message: $localize`:@@alert.portfolio.update.message:The portfolio has been successfully updated`,
        //   type: AlertTypes.SUCCESS
        // });
        this.commonService.reloadForce();
      });
  }

  processGrowthChart(growthData: any[]) {
    const labelsX = growthData.map((item) => (item.month));
    const currencySign = this.portfolio.currency_sign;
    const valuesGrowth = [];
    const valuesChange = [];
    let max = 0;
    growthData.map((item) => {
      valuesGrowth.push(+item.cumulative.toFixed());
      valuesChange.push(+item.total.toFixed());
      if (+item.cumulative.toFixed() > max) {
        max = +item.cumulative.toFixed();
      }
      if (+item.total.toFixed() > max) {
        max = +item.total.toFixed();
      }
    });
    const labelsY = [0, currencySign + ' ' + max.toLocaleString()];
    const hint = ({$implicit: e}) => {
      return currencySign + ' ' + valuesGrowth[e].toLocaleString() + '\n'
        + currencySign + ' ' + valuesChange[e].toLocaleString();
    };
    return {
      labelsX,
      labelsY,
      currencySign,
      values: [
        valuesGrowth,
        valuesChange
      ],
      hint,
      max
    };
  }

  processAssetTypesChart(assetTypes: any[]) {
    const values = assetTypes.map((item) => (item.part_percent));
    const labels = assetTypes.map((item) => (item.label));
    return {values, labels};
  }

  get assetTypesChartLabel(): string {
    return isNaN(this.chartAssetTypesIndex) ? `Total` : this.chartAssetTypes.labels[this.chartAssetTypesIndex];
  }

  get assetTypesChartValue(): number {
    return isNaN(this.chartAssetTypesIndex) ? `Total` : this.chartAssetTypes.values[this.chartAssetTypesIndex];
  }

  processSectorsChart(sectors: any[]) {
    const values = sectors.map((item) => (item.part_percent));
    const labels = sectors.map((item) => (item.label));
    return {values, labels};
  }

  get sectorsChartLabel(): string {
    return isNaN(this.chartSectorsIndex) ? `Total` : this.chartSectors.labels[this.chartSectorsIndex];
  }

  get sectorsChartValue(): number {
    return isNaN(this.chartSectorsIndex) ? `Total` : this.chartSectors.values[this.chartSectorsIndex];
  }

  processEarningsChart(earnings: any[]) {
    earnings.map((item) => {
      this.earningsSum = this.earningsSum + item.earnings;
    });
    const values = earnings.map((item) => (item.earnings));
    const captions = earnings.map((item) => (item.label));
    return {
      chart: {
        type: 'donut',
        height: '400px',
      },
      series: values,
      labels: captions,
    };
  }

  processEmitentsChart(content: Position[]) {
    const values = [];
    const captions = [];
    content.sort((a, b) => b.current_cost - a.current_cost);
    content.forEach((item) => {
      if (item.ticker !== '$$' && item.part_percent > 0) {
        values.push(item.current_cost);
        captions.push(item.title ?? item.ticker);
      }
    });
    return {
      chart: {
        type: 'donut',
        height: '400px',
      },
      series: values,
      labels: captions,
    };
  }

}
