<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    public function testApi()
    {
        $email = 'unit_test@ohmyshares.com';
        config(['app.signup_enabled' => true, 'services.hcaptcha.secret' => null]);
        Mail::fake();

        // signup
        $uri = '/api/signup?'.http_build_query([
            'name' => 'Test Me',
            'email' => $email,
            'captcha' => 'test',
        ]);

        $response = $this->post($uri);
        //$response->dump();
        $response
            ->assertStatus(200)
            ->assertJson([]);


        // auth
        $response = $this->post('/api/auth?', [
            'email' => $email,
            'password' => 'test_password',
        ]);
        //$token = $response->decodeResponseJson('api_token');
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['api_token']);
    }


    // public function testAuth()
    // {
    //     $user = factory(User::class)->create();
    //     $response = $this->actingAs($user, 'api')
    //         ->post('/api/auth?', [
    //             'email' => $user->email,
    //             'password' => 'password',
    //         ]);
    //     $response->dump();
    //     $response
    //         ->assertStatus(200)
    //         ->assertJsonStructure(['api_token']);
    // }


    public function testMe()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->get('/api/me');
        //$response->dump();
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'name', 'email'
            ]);
    }


    public function testLogout()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->post('/api/logout');
        //$response->dump();
        $response
            ->assertStatus(200)
            ->assertJson([]);
    }

    public function testPortfolioContents()
    {
        $user = User::factory()->create();
        $user->portfolios->first()->delete();

        $portfolio = Portfolio::factory()->make();
        $portfolio->user_id = $user->id;
        $portfolio->save();

        // add money
        $trade = Trade::factory()->make();
        $trade->portfolio_id = $portfolio->id;
        $trade->user_id = $user->id;
        $trade->sum = 500;
        $trade->type = Trade::TYPE_CREDIT;
        $trade->save();

        // add share
        $trade = Trade::factory()->make();
        $trade->portfolio_id = $portfolio->id;
        $trade->user_id = $user->id;
        $trade->type = Trade::TYPE_BUY;
        $trade->ticker = 'MSFT';
        $trade->save();

        $user->refresh();

        $response = $this->actingAs($user, 'api')
            ->get('/api/portfolios/'.$user->portfolios->first()->uid.'/content');
        //$response->dump();
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'content',
                'summary',
                'portfolio' => ['uid']
            ])
            ->assertJsonPath('content.1.ticker', 'MSFT');
    }
}
