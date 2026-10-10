<?php
namespace App\Models\Interfaces;

interface TickerInfoInterface
{
    public static function findModel($ticker, ?string $exchange = null);

    /**
     * @return string name of asset
     */
    public function getNameShortAttribute(): string;

    /**
     * @return int asset type code
     */
    public function getAssetAttribute(): int;

    public function getHistoryQuery();
    public function history($start = null, $end = null);
    public function lastHistory();
    public function isOutdated(): bool;
    public function isLastRequestExpired(): bool;
    public function lastPrice();

    public function updateHistory();
}
