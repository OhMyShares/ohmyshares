<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Unauthorized requests
Route::group(['middleware' => ['response.json'], 'namespace' => 'API'], function() {
    Route::post('auth', ['uses' => 'AuthController@authenticate', 'as' => 'auth']);
    Route::post('signup', ['uses' => 'AuthController@signup', 'as' => 'signup']);
    Route::post('password-forgot', ['uses' => 'AuthController@passwordForgot', 'as' => 'password-forgot']);
    Route::post('password-reset', ['uses' => 'AuthController@passwordReset', 'as' => 'password-reset']);
    Route::post('logout', ['uses' => 'AuthController@logout', 'as' => 'logout']);
});


// Authorized requests
Route::group(['middleware' => ['response.json', 'auth:api'], 'namespace' => 'API'], function() {
    Route::get('me', ['uses' => 'AuthController@me', 'as' => 'me']);
    Route::patch('me', ['uses' => 'AuthController@meUpdate', 'as' => 'me.update']);
    Route::post('password-change', ['uses' => 'AuthController@passwordChange']);
    Route::get('trades/tickers', ['uses' => 'TradeController@tickers', 'as' => 'trades.tickers']);
    Route::get('trades/currencies', ['uses' => 'TradeController@currencies', 'as' => 'trades.currencies']);
    Route::apiResource('trades', 'TradeController');
    Route::apiResource('custom-assets', 'CustomAssetController');
    Route::apiResource('custom-assets-prices', 'CustomAssetPriceController');

    // Portfolios
    Route::apiResource('portfolios', 'PortfolioController');
    Route::get('portfolios/{uid}/content', ['uses' => 'PortfolioController@content', 'as' => 'portfolios.content']);
    Route::get('portfolios/{uid}/analytics', ['uses' => 'PortfolioController@analytics', 'as' => 'portfolios.analytics']);
    Route::post('portfolios/{uid}/import/{parser}', ['uses' => 'PortfolioController@import', 'as' => 'portfolios.import']);
    Route::post('portfolios/{uid}/clone', ['uses' => 'PortfolioController@clone', 'as' => 'portfolios.clone']);

    // Portfolio Items
    Route::delete('portfolio-items/{itemUid}', ['uses' => 'PortfolioController@itemDelete', 'as' => 'portfolios.itemDelete']);
    Route::patch('portfolio-items/{itemUid}/ticker', ['uses' => 'PortfolioController@itemChangeTicker', 'as' => 'portfolios.itemChangeTicker']);

    // Tickers
    Route::get('tickers/list', ['uses' => 'TickerController@index', 'as' => 'tickers.list']);
    Route::get('tickers/{ticker}/info', ['uses' => 'TickerController@info', 'as' => 'tickers.info']);
    Route::get('tickers/{ticker}/history', ['uses' => 'TickerController@history', 'as' => 'tickers.history']);
    Route::get('tickers/{ticker}/dividends', ['uses' => 'TickerController@dividends', 'as' => 'tickers.dividends']);

    // References
    Route::group(['prefix' => 'reference'], function() {
        Route::get('/currency', ['uses' => 'ReferenceController@currency', 'as' => 'reference.currency']);
        Route::get('/currency-rates', ['uses' => 'ReferenceController@currencyRates', 'as' => 'reference.currencyRates']);
        Route::get('/account-type', ['uses' => 'ReferenceController@accountType', 'as' => 'reference.accountType']);
        Route::get('/trade-type', ['uses' => 'ReferenceController@tradeType', 'as' => 'reference.tradeType']);
        Route::get('/trade-asset', ['uses' => 'ReferenceController@tradeAsset', 'as' => 'reference.tradeAsset']);
        Route::get('/ticker', ['uses' => 'ReferenceController@ticker', 'as' => 'reference.ticker']);
        Route::get('/quote', ['uses' => 'ReferenceController@quote', 'as' => 'reference.quote']);
        Route::get('/parsers', ['uses' => 'ReferenceController@parsers', 'as' => 'reference.parsers']);
    });

    // User management routes
    Route::apiResource('users', 'UserController')->middleware('role.admin');

    // Plugins (admin only)
    Route::group(['prefix' => 'plugins', 'middleware' => ['role.admin']], function() {
        Route::get('/', ['uses' => 'PluginController@index', 'as' => 'plugins.index']);
        Route::get('/catalog', ['uses' => 'PluginController@catalog', 'as' => 'plugins.catalog']);
        Route::get('/installations/{installation}', ['uses' => 'PluginController@installation', 'as' => 'plugins.installation']);
        Route::post('/', ['uses' => 'PluginController@store', 'as' => 'plugins.store']);
        Route::get('/{plugin}', ['uses' => 'PluginController@show', 'as' => 'plugins.show']);
        Route::post('/{plugin}/upgrade', ['uses' => 'PluginController@upgrade', 'as' => 'plugins.upgrade']);
        Route::patch('/{plugin}/enable', ['uses' => 'PluginController@enable', 'as' => 'plugins.enable']);
        Route::put('/{plugin}/config', ['uses' => 'PluginController@storeConfig', 'as' => 'plugins.config']);
        Route::delete('/{plugin}', ['uses' => 'PluginController@destroy', 'as' => 'plugins.destroy']);
    });

    // Currency rates (admin only)
    Route::group(['prefix' => 'currency-rates', 'middleware' => ['role.admin']], function() {
        Route::get('/', ['uses' => 'CurrencyRateController@index', 'as' => 'currencyRates.index']);
        Route::post('/update', ['uses' => 'CurrencyRateController@update', 'as' => 'currencyRates.update']);
    });
});
