<?php

namespace App\QueryFilters;

use App\Components\Uid;

/**
 * Filter records based on query parameters.
 *
 */
class TradeFilters extends BaseFilters
{
    /**
     * Filter records based on the query parameter "uid"
     *
     * @param $uid
     */
    public function portfolioUid($uid): void
    {
        $this->query->where('portfolio_id', Uid::decode($uid));
    }

    /**
     * Filter records based on the query parameter "type"
     *
     * @param $value
     */
    public function type($value): void
    {
        $this->query->where('type', $value);
    }

    /**
     * Filter records based on the query parameter "asset"
     *
     * @param $value
     */
    public function asset($value): void
    {
        $this->query->where('asset', $value);
    }

    /**
     * Filter records based on the query parameter "currency_code"
     *
     * @param $value
     */
    public function currencyCode($value): void
    {
        $this->query->where('currency_code', $value);
    }

    /**
     * Filter records based on the query parameter "ticker"
     *
     * @param $value
     */
    public function ticker($value): void
    {
        $this->query->where('ticker', $value);
    }

    /**
     * Filter records based on the query parameter "created_at_begin"
     *
     * @param $timestamp
     */
    public function createdAtBegin($timestamp): void
    {
        $this->query->where('created_at', '>=', $timestamp);
    }

    /**
     * Filter records based on the query parameter "created_at_end"
     *
     * @param $timestamp
     */
    public function createdAtEnd($timestamp): void
    {
        $this->query->where('created_at', '<=', $timestamp);
    }
}
