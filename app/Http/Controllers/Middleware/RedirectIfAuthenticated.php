<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {

            if (Auth::guard($guard)->check()) {

                if ($guard === 'penjual') {
                    return redirect()->route('penjual.homepenjual');
                }

                if ($guard === 'customer') {
                    return redirect()->route('customer.homecustomer');
                }

                if ($guard === 'superadmin') {
                    return redirect()->route('superadmin.homesuperadmin');
                }
            }
        }

        return $next($request);
    }
}