// import { MatPaginatorIntl } from '@angular/material/paginator';

const localeId = window?.navigator?.languages?.[0] || 'en';
const [locale] = localeId.split('-');
const localeString = localStorage.getItem('LOCALE_ID');
if (!localeString) {
  localStorage.setItem('LOCALE_ID', locale);
}

export const LOCALES = ['en', 'zn', 'hi', 'es', 'fr', 'ar', 'ru', 'pt', 'id', 'de', 'jp', 'ko'];
export const LOCALE_STRING = localeString || locale;

export const PORTFOLIOS_LIST_KEY = 'PORTFOLIOS_LIST';
export const CURRENT_PORTFOLIO_KEY = 'CURRENT_PORTFOLIO';
export const ANALYTICS_CURRENCY_KEY = 'ANALYTICS_CURRENCY';

export const DATERANGEPICKER_CONFIG = ['ru'].includes(localeString)
  ? {
    format: 'DD.MM.YYYY',
    firstDay: 1,
    daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    monthNames: ['Янв.', 'Фев.', 'Март', 'Апр.', 'Май', 'Июн.', 'Июл.', 'Авг.', 'Сент.', 'Окт.', 'Нояб.', 'Дек.']
  }
  : null;

export const PAGINATOR_INTL = () => {
  // const paginatorIntl = new MatPaginatorIntl();
  // return Object.assign(paginatorIntl, {
  //   itemsPerPageLabel: $localize`:@@table.general.paginator.itemsPerPage:Items per page\:`,
  //   nextPageLabel: $localize`:@@table.general.paginator.nextPage:Next page`,
  //   previousPageLabel: $localize`:@@table.general.paginator.previousPage:Previous page`,
  //   getRangeLabel: (page, pageSize, length) => `${page + 1} ` + $localize`:@@table.general.paginator.of:of` + ` ${Math.ceil(length / pageSize)}`
  // });
};

export const COLORS = {
  success: '#28a745',
  danger: '#dc3545',
  buy: '#28a745',
  sell: '#dc3545',
};

export const enum TRADE_TYPE {
  buy = 1,
  sell = 2,
  dividend = 3,
  fee = 4,
  coupon = 5,
  taxes = 6,
}

export const enum ASSET_TYPE {
  stock = 1,
  bonds = 2,
  money = 10,
  crypto = 11,
  custom = 20,
}
