<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {

//            if (!in_array($request->url(), [\URL::route('user_logout'), \URL::route('admin_logout')])) {
//                \Session::set('redirect_url', $request->url());
//            } else {
//                \Session::remove('redirect_url');
//            }

            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } elseif ($guard == 'admin') {
                return redirect()->guest('/admin/login');
            } else {
                return redirect()->guest('/advertiser/login');
            }
        }

        return $next($request);
    }
}
