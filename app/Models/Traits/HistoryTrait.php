<?php

namespace App\Models\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait HistoryTrait
{
    public function history($start = null, $end = null)
    {
        if (!$start) {
            $start = date_create('-1 month')->format('Y-m-d');
        }
        if (!$end) {
            $end = date('Y-m-d');
        }
        return $this->getHistoryQuery()
            ->whereNotNull('close')
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();
    }

    public function lastHistory()
    {
        if (!$this->_last_history) {
            $this->_last_history = $this->getHistoryQuery()
                ->orderBy('date', 'desc')
                ->first();
        }
        return $this->_last_history;
    }

    public function isOutdated(): bool
    {
        $row = $this->lastHistory();
        if ($row) {
            $date = Carbon::createFromFormat('Y-m-d', $row->date);
            $today = Carbon::today();
            $days = (int)$date->diffInDays($today, true);
            // if difference 1 day and more
            if ($days) {
                if ($today->isWeekend() && $days <= 2) {
                    return false; // we have actual history, just today is weekend
                }
                return true;
            }
        }
        return false;
    }

    public function isLastRequestExpired(): bool
    {
        $today = Carbon::today();
        $last_request = Cache::get('history_' . $this->ticker . '_' . ($this->exchange ?? '') . '_' . $this->asset, false);
        if (!$last_request) {
            return true;
        }
        return false;
    }

    public function setLastRequest(): void
    {
        Cache::put('history_' . $this->ticker . '_' . ($this->exchange ?? '') . '_' . $this->asset, true, 60 * 60);
    }

    public function lastPrice(): ?float
    {
        $row = $this->lastHistory();
        if ($row) {
            return $row->close;
        }
        return 0;
    }
}
