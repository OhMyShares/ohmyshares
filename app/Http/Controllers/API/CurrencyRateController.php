<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CurrencyRate;
use App\Models\Plugin;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CurrencyRateController extends Controller
{
    const DEFAULT_LIMIT = 50;

    public function __construct(private readonly CurrencyService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'currency' => 'nullable|string|max:10',
            'date' => 'nullable|date_format:Y-m-d',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);
        $query = CurrencyRate::query()
            ->when($request->get('currency'), fn(Builder $q, string $code): Builder => $q
                ->where(fn(Builder $q): Builder => $q
                    ->where('base', mb_strtoupper($code))
                    ->orWhere('currency', mb_strtoupper($code))))
            ->when($request->get('date'), fn(Builder $q, string $date): Builder => $q->where('date', $date));

        return response()->json([
            'data' => (clone $query)
                ->orderByDesc('date')
                ->orderBy('base')
                ->orderBy('currency')
                ->limit((int)$request->get('limit', self::DEFAULT_LIMIT))
                ->offset((int)$request->get('offset', 0))
                ->get(),
            'total' => $query->count(),
            'providers' => $this->providers(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
        ]);
        $date = $request->get('date');
        return response()->json([
            'count' => $this->service->update($date ? Carbon::createFromFormat('!Y-m-d', $date) : null),
            'providers' => $this->providers(),
        ]);
    }

    /**
     * @return array<int, array{package: string, title: string}>
     */
    private function providers(): array
    {
        return $this->service->providers()
            ->map(fn(Plugin $plugin): array => ['package' => $plugin->package, 'title' => $plugin->title])
            ->values()
            ->all();
    }
}
