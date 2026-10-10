<?php

namespace App\Enums;

enum Currency: string
{
    public const UNIVERSAL_SIGN = '¤';

    case AED = 'AED';
    case AFN = 'AFN';
    case ALL = 'ALL';
    case AMD = 'AMD';
    case ANG = 'ANG';
    case AOA = 'AOA';
    case ARS = 'ARS';
    case AUD = 'AUD';
    case AWG = 'AWG';
    case AZN = 'AZN';
    case BAM = 'BAM';
    case BBD = 'BBD';
    case BDT = 'BDT';
    case BGN = 'BGN';
    case BHD = 'BHD';
    case BIF = 'BIF';
    case BMD = 'BMD';
    case BND = 'BND';
    case BOB = 'BOB';
    case BRL = 'BRL';
    case BSD = 'BSD';
    case BTN = 'BTN';
    case BWP = 'BWP';
    case BYN = 'BYN';
    case BZD = 'BZD';
    case CAD = 'CAD';
    case CDF = 'CDF';
    case CHF = 'CHF';
    case CLP = 'CLP';
    case CNY = 'CNY';
    case COP = 'COP';
    case CRC = 'CRC';
    case CUP = 'CUP';
    case CVE = 'CVE';
    case CZK = 'CZK';
    case DJF = 'DJF';
    case DKK = 'DKK';
    case DOP = 'DOP';
    case DZD = 'DZD';
    case EGP = 'EGP';
    case ERN = 'ERN';
    case ETB = 'ETB';
    case EUR = 'EUR';
    case FJD = 'FJD';
    case FKP = 'FKP';
    case GBP = 'GBP';
    case GEL = 'GEL';
    case GHS = 'GHS';
    case GIP = 'GIP';
    case GMD = 'GMD';
    case GNF = 'GNF';
    case GTQ = 'GTQ';
    case GYD = 'GYD';
    case HKD = 'HKD';
    case HNL = 'HNL';
    case HTG = 'HTG';
    case HUF = 'HUF';
    case IDR = 'IDR';
    case ILS = 'ILS';
    case INR = 'INR';
    case IQD = 'IQD';
    case IRR = 'IRR';
    case ISK = 'ISK';
    case JMD = 'JMD';
    case JOD = 'JOD';
    case JPY = 'JPY';
    case KES = 'KES';
    case KGS = 'KGS';
    case KHR = 'KHR';
    case KMF = 'KMF';
    case KPW = 'KPW';
    case KRW = 'KRW';
    case KWD = 'KWD';
    case KYD = 'KYD';
    case KZT = 'KZT';
    case LAK = 'LAK';
    case LBP = 'LBP';
    case LKR = 'LKR';
    case LRD = 'LRD';
    case LSL = 'LSL';
    case LYD = 'LYD';
    case MAD = 'MAD';
    case MDL = 'MDL';
    case MGA = 'MGA';
    case MKD = 'MKD';
    case MMK = 'MMK';
    case MNT = 'MNT';
    case MOP = 'MOP';
    case MRU = 'MRU';
    case MUR = 'MUR';
    case MVR = 'MVR';
    case MWK = 'MWK';
    case MXN = 'MXN';
    case MYR = 'MYR';
    case MZN = 'MZN';
    case NAD = 'NAD';
    case NGN = 'NGN';
    case NIO = 'NIO';
    case NOK = 'NOK';
    case NPR = 'NPR';
    case NZD = 'NZD';
    case OMR = 'OMR';
    case PAB = 'PAB';
    case PEN = 'PEN';
    case PGK = 'PGK';
    case PHP = 'PHP';
    case PKR = 'PKR';
    case PLN = 'PLN';
    case PYG = 'PYG';
    case QAR = 'QAR';
    case RON = 'RON';
    case RSD = 'RSD';
    case RUB = 'RUB';
    case RWF = 'RWF';
    case SAR = 'SAR';
    case SBD = 'SBD';
    case SCR = 'SCR';
    case SDG = 'SDG';
    case SEK = 'SEK';
    case SGD = 'SGD';
    case SHP = 'SHP';
    case SLE = 'SLE';
    case SOS = 'SOS';
    case SRD = 'SRD';
    case SSP = 'SSP';
    case STN = 'STN';
    case SVC = 'SVC';
    case SYP = 'SYP';
    case SZL = 'SZL';
    case THB = 'THB';
    case TJS = 'TJS';
    case TMT = 'TMT';
    case TND = 'TND';
    case TOP = 'TOP';
    case TRY = 'TRY';
    case TTD = 'TTD';
    case TWD = 'TWD';
    case TZS = 'TZS';
    case UAH = 'UAH';
    case UGX = 'UGX';
    case USD = 'USD';
    case UYU = 'UYU';
    case UZS = 'UZS';
    case VES = 'VES';
    case VND = 'VND';
    case VUV = 'VUV';
    case WST = 'WST';
    case XAF = 'XAF';
    case XCD = 'XCD';
    case XOF = 'XOF';
    case XPF = 'XPF';
    case YER = 'YER';
    case ZAR = 'ZAR';
    case ZMW = 'ZMW';
    case ZWL = 'ZWL';

    private const TITLES = [
        'AED' => 'UAE Dirham',
        'AFN' => 'Afghan Afghani',
        'ALL' => 'Albanian Lek',
        'AMD' => 'Armenian Dram',
        'ANG' => 'Netherlands Antillean Guilder',
        'AOA' => 'Angolan Kwanza',
        'ARS' => 'Argentine Peso',
        'AUD' => 'Australian Dollar',
        'AWG' => 'Aruban Florin',
        'AZN' => 'Azerbaijani Manat',
        'BAM' => 'Bosnia-Herzegovina Convertible Mark',
        'BBD' => 'Barbadian Dollar',
        'BDT' => 'Bangladeshi Taka',
        'BGN' => 'Bulgarian Lev',
        'BHD' => 'Bahraini Dinar',
        'BIF' => 'Burundian Franc',
        'BMD' => 'Bermudian Dollar',
        'BND' => 'Brunei Dollar',
        'BOB' => 'Bolivian Boliviano',
        'BRL' => 'Brazilian Real',
        'BSD' => 'Bahamian Dollar',
        'BTN' => 'Bhutanese Ngultrum',
        'BWP' => 'Botswana Pula',
        'BYN' => 'Belarusian Ruble',
        'BZD' => 'Belize Dollar',
        'CAD' => 'Canadian Dollar',
        'CDF' => 'Congolese Franc',
        'CHF' => 'Swiss Franc',
        'CLP' => 'Chilean Peso',
        'CNY' => 'Chinese Yuan',
        'COP' => 'Colombian Peso',
        'CRC' => 'Costa Rican Colón',
        'CUP' => 'Cuban Peso',
        'CVE' => 'Cape Verdean Escudo',
        'CZK' => 'Czech Koruna',
        'DJF' => 'Djiboutian Franc',
        'DKK' => 'Danish Krone',
        'DOP' => 'Dominican Peso',
        'DZD' => 'Algerian Dinar',
        'EGP' => 'Egyptian Pound',
        'ERN' => 'Eritrean Nakfa',
        'ETB' => 'Ethiopian Birr',
        'EUR' => 'Euro',
        'FJD' => 'Fijian Dollar',
        'FKP' => 'Falkland Islands Pound',
        'GBP' => 'Pound Sterling',
        'GEL' => 'Georgian Lari',
        'GHS' => 'Ghanaian Cedi',
        'GIP' => 'Gibraltar Pound',
        'GMD' => 'Gambian Dalasi',
        'GNF' => 'Guinean Franc',
        'GTQ' => 'Guatemalan Quetzal',
        'GYD' => 'Guyanese Dollar',
        'HKD' => 'Hong Kong Dollar',
        'HNL' => 'Honduran Lempira',
        'HTG' => 'Haitian Gourde',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'Israeli New Shekel',
        'INR' => 'Indian Rupee',
        'IQD' => 'Iraqi Dinar',
        'IRR' => 'Iranian Rial',
        'ISK' => 'Icelandic Króna',
        'JMD' => 'Jamaican Dollar',
        'JOD' => 'Jordanian Dinar',
        'JPY' => 'Japanese Yen',
        'KES' => 'Kenyan Shilling',
        'KGS' => 'Kyrgyzstani Som',
        'KHR' => 'Cambodian Riel',
        'KMF' => 'Comorian Franc',
        'KPW' => 'North Korean Won',
        'KRW' => 'South Korean Won',
        'KWD' => 'Kuwaiti Dinar',
        'KYD' => 'Cayman Islands Dollar',
        'KZT' => 'Kazakhstani Tenge',
        'LAK' => 'Lao Kip',
        'LBP' => 'Lebanese Pound',
        'LKR' => 'Sri Lankan Rupee',
        'LRD' => 'Liberian Dollar',
        'LSL' => 'Lesotho Loti',
        'LYD' => 'Libyan Dinar',
        'MAD' => 'Moroccan Dirham',
        'MDL' => 'Moldovan Leu',
        'MGA' => 'Malagasy Ariary',
        'MKD' => 'Macedonian Denar',
        'MMK' => 'Myanmar Kyat',
        'MNT' => 'Mongolian Tögrög',
        'MOP' => 'Macanese Pataca',
        'MRU' => 'Mauritanian Ouguiya',
        'MUR' => 'Mauritian Rupee',
        'MVR' => 'Maldivian Rufiyaa',
        'MWK' => 'Malawian Kwacha',
        'MXN' => 'Mexican Peso',
        'MYR' => 'Malaysian Ringgit',
        'MZN' => 'Mozambican Metical',
        'NAD' => 'Namibian Dollar',
        'NGN' => 'Nigerian Naira',
        'NIO' => 'Nicaraguan Córdoba',
        'NOK' => 'Norwegian Krone',
        'NPR' => 'Nepalese Rupee',
        'NZD' => 'New Zealand Dollar',
        'OMR' => 'Omani Rial',
        'PAB' => 'Panamanian Balboa',
        'PEN' => 'Peruvian Sol',
        'PGK' => 'Papua New Guinean Kina',
        'PHP' => 'Philippine Peso',
        'PKR' => 'Pakistani Rupee',
        'PLN' => 'Polish Złoty',
        'PYG' => 'Paraguayan Guaraní',
        'QAR' => 'Qatari Riyal',
        'RON' => 'Romanian Leu',
        'RSD' => 'Serbian Dinar',
        'RUB' => 'Russian Ruble',
        'RWF' => 'Rwandan Franc',
        'SAR' => 'Saudi Riyal',
        'SBD' => 'Solomon Islands Dollar',
        'SCR' => 'Seychellois Rupee',
        'SDG' => 'Sudanese Pound',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'SHP' => 'Saint Helena Pound',
        'SLE' => 'Sierra Leonean Leone',
        'SOS' => 'Somali Shilling',
        'SRD' => 'Surinamese Dollar',
        'SSP' => 'South Sudanese Pound',
        'STN' => 'São Tomé and Príncipe Dobra',
        'SVC' => 'Salvadoran Colón',
        'SYP' => 'Syrian Pound',
        'SZL' => 'Swazi Lilangeni',
        'THB' => 'Thai Baht',
        'TJS' => 'Tajikistani Somoni',
        'TMT' => 'Turkmenistani Manat',
        'TND' => 'Tunisian Dinar',
        'TOP' => 'Tongan Paʻanga',
        'TRY' => 'Turkish Lira',
        'TTD' => 'Trinidad and Tobago Dollar',
        'TWD' => 'New Taiwan Dollar',
        'TZS' => 'Tanzanian Shilling',
        'UAH' => 'Ukrainian Hryvnia',
        'UGX' => 'Ugandan Shilling',
        'USD' => 'US Dollar',
        'UYU' => 'Uruguayan Peso',
        'UZS' => 'Uzbekistani Soʻm',
        'VES' => 'Venezuelan Bolívar',
        'VND' => 'Vietnamese Đồng',
        'VUV' => 'Vanuatu Vatu',
        'WST' => 'Samoan Tālā',
        'XAF' => 'CFA Franc BEAC',
        'XCD' => 'East Caribbean Dollar',
        'XOF' => 'CFA Franc BCEAO',
        'XPF' => 'CFP Franc',
        'YER' => 'Yemeni Rial',
        'ZAR' => 'South African Rand',
        'ZMW' => 'Zambian Kwacha',
        'ZWL' => 'Zimbabwean Dollar',
    ];

    private const SIGNS = [
        'AED' => 'د.إ',
        'AFN' => '؋',
        'ALL' => 'L',
        'AMD' => '֏',
        'ANG' => 'ƒ',
        'AOA' => 'Kz',
        'ARS' => '$',
        'AUD' => 'A$',
        'AWG' => 'ƒ',
        'AZN' => '₼',
        'BAM' => 'KM',
        'BBD' => '$',
        'BDT' => '৳',
        'BGN' => 'лв',
        'BMD' => '$',
        'BND' => 'B$',
        'BOB' => 'Bs.',
        'BRL' => 'R$',
        'BSD' => '$',
        'BTN' => 'Nu.',
        'BWP' => 'P',
        'BYN' => 'Br',
        'BZD' => 'BZ$',
        'CAD' => 'C$',
        'CHF' => '₣',
        'CLP' => '$',
        'CNY' => '¥',
        'COP' => '$',
        'CRC' => '₡',
        'CUP' => '$',
        'CVE' => '$',
        'CZK' => 'Kč',
        'DKK' => 'kr',
        'DOP' => '$',
        'EGP' => '£',
        'ETB' => 'Br',
        'EUR' => '€',
        'FJD' => '$',
        'FKP' => '£',
        'GBP' => '£',
        'GEL' => '₾',
        'GHS' => '₵',
        'GIP' => '£',
        'GTQ' => 'Q',
        'GYD' => '$',
        'HKD' => 'HK$',
        'HNL' => 'L',
        'HUF' => 'Ft',
        'IDR' => 'Rp',
        'ILS' => '₪',
        'INR' => '₹',
        'IRR' => '﷼',
        'ISK' => 'kr',
        'JMD' => 'J$',
        'JPY' => '¥',
        'KES' => 'KSh',
        'KHR' => '៛',
        'KPW' => '₩',
        'KRW' => '₩',
        'KYD' => '$',
        'KZT' => '₸',
        'LAK' => '₭',
        'LBP' => '£',
        'LKR' => '₨',
        'LRD' => '$',
        'LSL' => 'L',
        'MDL' => 'L',
        'MGA' => 'Ar',
        'MKD' => 'ден',
        'MMK' => 'K',
        'MNT' => '₮',
        'MOP' => 'MOP$',
        'MRU' => 'UM',
        'MUR' => '₨',
        'MVR' => 'Rf',
        'MWK' => 'MK',
        'MXN' => '$',
        'MYR' => 'RM',
        'MZN' => 'MT',
        'NAD' => '$',
        'NGN' => '₦',
        'NIO' => 'C$',
        'NOK' => 'kr',
        'NPR' => '₨',
        'NZD' => 'NZ$',
        'OMR' => '﷼',
        'PAB' => 'B/.',
        'PEN' => 'S/',
        'PHP' => '₱',
        'PKR' => '₨',
        'PLN' => 'zł',
        'PYG' => '₲',
        'QAR' => '﷼',
        'RON' => 'lei',
        'RSD' => 'дин.',
        'RUB' => '₽',
        'SAR' => '﷼',
        'SBD' => '$',
        'SCR' => '₨',
        'SDG' => '£',
        'SEK' => 'kr',
        'SGD' => 'S$',
        'SHP' => '£',
        'SRD' => '$',
        'SSP' => '£',
        'SVC' => '₡',
        'SYP' => '£',
        'SZL' => 'L',
        'THB' => '฿',
        'TMT' => 'T',
        'TOP' => 'T$',
        'TRY' => '₺',
        'TTD' => 'TT$',
        'TWD' => 'NT$',
        'TZS' => 'TSh',
        'UAH' => '₴',
        'UGX' => 'USh',
        'USD' => '$',
        'UYU' => '$',
        'VES' => 'Bs.',
        'VND' => '₫',
        'WST' => 'T',
        'XAF' => 'FCFA',
        'XCD' => 'EC$',
        'XOF' => 'CFA',
        'YER' => '﷼',
        'ZAR' => 'R',
        'ZMW' => 'K',
        'ZWL' => '$',
    ];

    public function title(): string
    {
        return self::TITLES[$this->value];
    }

    public function sign(): string
    {
        return self::SIGNS[$this->value] ?? self::UNIVERSAL_SIGN;
    }

    public static function codes(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function titles(): array
    {
        return self::TITLES;
    }

    public static function signs(): array
    {
        $signs = [];
        foreach (self::cases() as $currency) {
            $signs[$currency->value] = $currency->sign();
        }
        return $signs;
    }

    public static function list(): array
    {
        return array_map(
            fn (self $currency): array => [
                'code' => $currency->value,
                'name' => $currency->sign(),
                'title' => $currency->title(),
            ],
            self::cases()
        );
    }

    public static function signOf(?string $code): ?string
    {
        return self::tryFrom((string)$code)?->sign();
    }

    public static function findCode(string $code): ?string
    {
        $code = trim($code);
        if (self::tryFrom(mb_strtoupper($code))) {
            return mb_strtoupper($code);
        }
        if (in_array(mb_strtolower($code), ['рубль', 'руб', 'руб.'])) {
            return self::RUB->value;
        }
        if (in_array(mb_strtolower($code), ['доллар сша', 'доллар', '$'])) {
            return self::USD->value;
        }
        return null;
    }
}
