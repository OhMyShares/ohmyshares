<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class Localization
{
    public function __construct(private readonly UrlGenerator $url)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locales = config('app.locales_available');
        $browser_locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
        $request_locale = $request->server('HTTP_OMS_LOCALE');

        // if logged
        if (!Auth::guest()) {
            App::setLocale(Auth::user()->locale());
            $this->url->defaults([
                'locale' => App::getLocale() ?: $browser_locale,
            ]);
            return $next($request);
        }

        // not logged
        $locale_param = $request->route('locale');
        if ($locale_param) {
            if (in_array($locale_param, $locales)) {
                Session::put('locale', $locale_param);
            } else {
                // redirect urls like /page to /locale/page if route exists and locale not provided
                if (Route::has('landing.'.$locale_param)) {
                    return redirect($browser_locale.'/'.$locale_param);
                }
                abort(404);
            }
        }

        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            if ($request_locale) {
                $locale = $request_locale;
            } else {
                $locale = $browser_locale;
            }
            if (in_array($locale, $locales)) {
                App::setLocale($locale);
            }
        }

        $this->url->defaults([
            'locale' => App::getLocale() ?: $browser_locale,
        ]);

        return $next($request);
    }
}
