<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PortfolioIndexCacheTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndexWorksWithNonTaggableCacheStore()
    {
        config(['cache.default' => 'file']);
        $user = User::factory()->create();
        $count = $user->portfolios()->count();

        $this->actingAs($user, 'api')->getJson('/api/portfolios')
            ->assertStatus(200)
            ->assertJsonCount($count);

        $this->actingAs($user, 'api')->postJson('/api/portfolios', [
            'name' => 'Cache Test',
            'currency_code' => Currency::codes()[0],
            'account_type' => Portfolio::TYPE_BROKER,
            'opened_at' => time(),
        ])->assertSuccessful();

        $this->actingAs($user, 'api')->getJson('/api/portfolios')
            ->assertStatus(200)
            ->assertJsonCount($count + 1);

        Cache::forget('portfolios_index_'.$user->id);
    }
}
