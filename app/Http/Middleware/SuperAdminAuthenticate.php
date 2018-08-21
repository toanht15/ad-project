<?php

namespace App\Http\Middleware;

use Closure;

class SuperAdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! \Auth::guard('admin')->user()->isSuperAdmin()) {
            abort(404);
        }

        return $next($request);
    }
}
