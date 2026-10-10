<?php

namespace App\Http\Middleware;

use Closure;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $is_enabled = config('app.basic_auth_enabled');
        $username = config('app.basic_auth_username');
        $password = config('app.basic_auth_password');

        if ($is_enabled && $username && $password) {
            header('Cache-Control: no-cache, must-revalidate, max-age=0');
            $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
            $is_not_authenticated = (
                !$has_supplied_credentials ||
                $_SERVER['PHP_AUTH_USER'] != $username ||
                $_SERVER['PHP_AUTH_PW'] != $password
            );
            if ($is_not_authenticated) {
                header('HTTP/1.1 401 Authorization Required');
                header('WWW-Authenticate: Basic realm="Access denied"');
                exit;
            }
        }
        return $next($request);
    }
}
