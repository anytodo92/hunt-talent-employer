<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                $route_name = Route::currentRouteName();
                $redirect_routes = [
                    'login' => 'company.home',
                    'recruit.login' => 'recruit.home',
                    'outsource.login' => 'outsource.home',
                    'admin.login' => 'admin.home'
                ];

                if(Arr::has($redirect_routes, $route_name)) {

                    $redirect_route = $redirect_routes[$route_name];
                    return redirect()->route($redirect_route);

                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
