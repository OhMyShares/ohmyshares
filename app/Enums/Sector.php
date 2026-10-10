<?php

namespace App\Enums;

enum Sector: string
{
    case BasicMaterials = 'Basic Materials';
    case CommunicationServices = 'Communication Services';
    case ConsumerCyclical = 'Consumer Cyclical';
    case ConsumerDefensive = 'Consumer Defensive';
    case Energy = 'Energy';
    case FinancialServices = 'Financial Services';
    case Healthcare = 'Healthcare';
    case Industrials = 'Industrials';
    case RealEstate = 'Real Estate';
    case Technology = 'Technology';
    case Utilities = 'Utilities';

    public static function titles(): array
    {
        return array_map(fn (self $sector) => $sector->value, self::cases());
    }
}
