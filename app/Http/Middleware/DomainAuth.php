<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Facades\Domain;

class DomainAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $domain = null)
    {
        if (Auth::check() && Auth::user()->domain->domain == $domain) {
            return $next($request);
        } else {
            Auth::logout();
            throw new \Illuminate\Auth\AuthenticationException;
        }
    }
}
